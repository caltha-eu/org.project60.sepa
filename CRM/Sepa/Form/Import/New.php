<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Sepa_Form_Import_New extends CRM_Core_Form {
  
  private $fields = array();

  // todo print values below the form
  private $readonlyFields = array(
    'interval' => array(),
    'mandate_type' => array(),
  );

  private $defaultsMap = array(
    'creditor_id' => 'batching_default_creditor',
    'financial_type_id' => 'import_financial_type_id',
    'campaign_id' => 'import_campaign_id',
    'collection_day' => 'import_collection_day',
  );

  private $creditors = array();
  private $financialTypes = array();
  private $campaigns = array();
  private $settings = array();

  function __construct($state, $action, $method, $name) {
    $this->fields = array(
      'creditor_id' => array(
        'type' => 'Select',
        'label' => ts("Creditor", array('domain' => 'org.project60.sepa')),
        'options' => array(),
        'required' => true,
      ),
      'financial_type_id' => array(
        'type' => 'Select',
        'label' => ts("Financial Type", array('domain' => 'org.project60.sepa')),
        'options' => array(),
        'required' => true,
      ),
      'campaign_id' => array(
        'type' => 'Select',
        'label' => ts("Campaign", array('domain' => 'org.project60.sepa')),
        'options' => array(),
        'required' => true, // fixme seems not to be required when CiviCampaign is not installed
      ),
      'collection_day' => array(
        'type' => 'text',
        'label' => ts("Collection day", array('domain' => 'org.project60.sepa')),
        'options' => array(),
        'required' => true,
      ),
      'start_date' => array(
        'type' => 'text',
        'label' => ts("Start date", array('domain' => 'org.project60.sepa')),
        'options' => array(),
        'required' => true,
      ),
      'importFile' => array(
        'type' => 'File',
        'label' => ts("Import file", array('domain' => 'org.project60.sepa')),
        'options' => array(),
        'required' => true,
      ),
    );
    parent::__construct($state, $action, $method, $name);
  }

  function preProcess() {
    $this->creditors = $this->getCreditors();
    $this->fields['creditor_id']['options'] = $this->creditors;
    $this->financialTypes = $this->getFinancialTypes();
    $this->fields['financial_type_id']['options'] = $this->financialTypes;
    $this->campaigns = $this->getCampaigns();
    $this->fields['campaign_id']['options'] = $this->campaigns;
    $this->settings = $this->getSettings();
    parent::preProcess();
  }

  function buildQuickForm() {
    foreach ($this->fields as $key => $field) {
      if ($key == 'importFile') {
        $this->addElement('file', $key, $field['label'], 'size=30 maxlength=60');
        $this->addUploadElement($key);
      } else {
        $this->add($field['type'], $key, $field['label'], array('' => '- select -') + $field['options'], $field['required']);
      }
    }

    $config = CRM_Core_Config::singleton();
    if (!empty($config->maxImportFileSize)) {
      $uploadFileSize = $config->maxImportFileSize;
    }
    else {
      $uploadFileSize = CRM_Utils_Number::formatUnitSize($config->maxFileSize . 'm', TRUE);
    }
    if ($uploadFileSize >= 8388608) {
      $uploadFileSize = 8388608;
    }
    $uploadSize = round(($uploadFileSize / (1024 * 1024)), 2);
    $this->addRule('importFile', ts('A valid file must be uploaded.'), 'uploadedfile');
    $this->addRule('importFile', ts('File size should be less than %1 MBytes (%2 bytes)', array(1 => $uploadSize, 2 => $uploadFileSize)), 'maxfilesize', $uploadFileSize);
    $this->setMaxFileSize($uploadFileSize);
    $this->addRule('importFile', ts('Input file must be in CSV format'), 'utf8File');

    $this->addButtons(array(array('type' => 'upload', 'name' => ts('Submit'), 'isDefault' => TRUE)));
    $this->assign('elementNames', $this->getRenderableElementNames());
  }

  function postProcess() {
    $importFile = $this->controller->exportValue($this->_name, 'importFile');
    CRM_Core_Error::debug_var('$importFile', $importFile);

    $values = $this->exportValues();
    CRM_Core_Error::debug_var('$values', $values);

    $importFile['name'];
    $fp = fopen($importFile['name'], 'r');
    $content = fread($fp, filesize($importFile['name']));
    CRM_Core_Error::debug_var('$content', $content);
  }


  function setDefaultValues() {
    $defaults = array();
    foreach ($this->defaultsMap as $key => $setting) {
      $defaults[$key] = $this->settings[$setting];
    }
    $defaults['start_date'] = date('Y-m-d');
    return $defaults;
  }


  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }


  private function getCreditors() {
    $result = civicrm_api3('SepaCreditor', 'get');
    $arr = array();
    if (array_key_exists('values', $result) && count($result['values']) > 0) {
      foreach ($result['values'] as $item) {
        $arr[$item['id']] = $item['name'];
      }
    }
    return $arr;
  }


  private function getFinancialTypes() {
    $result = civicrm_api3('FinancialType', 'get', array('is_active' => 1));
    $arr = array();
    if (array_key_exists('values', $result) && count($result['values']) > 0) {
      foreach ($result['values'] as $item) {
        $arr[$item['id']] = $item['name'];
      }
    }
    return $arr;
  }


  private function getCampaigns() {
    $result = civicrm_api3('Campaign', 'get', array('is_active' => 1));
    $arr = array();
    if (array_key_exists('values', $result) && count($result['values']) > 0) {
      foreach ($result['values'] as $item) {
        $arr[$item['id']] = $item['title'];
      }
    }
    return $arr;
  }


  private function getSettings() {
    $keys = array(
      'batching_default_creditor' => null,
      'default_mandate_type' => null,
      'import_financial_type_id' => null,
      'import_campaign_id' => null,
      'import_collection_day' => null,
      'import_interval' => null,
      'import_date_format' => null,
      'import_thousands_delimiter' => null,
      'import_decimal_delimiter' => null,
      'import_contact_custom_field' => null,
    );
    foreach ($keys as $key => $val) {
      $value = CRM_Core_BAO_Setting::getItem('SEPA Direct Debit Preferences', $key);
      $keys[$key] = $value;
    }
    return $keys;
  }
}
