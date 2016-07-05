<?php
/*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2013-2014 SYSTOPIA                       |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Sepa_ExtensionUtil as E;

/**
 * Class CRM_Sepa_Logic_ImportTasks
 */
class CRM_Sepa_Logic_Import_Tasks {

  private static $country_ids = array();

  private static $location_type_id = 1;

  private static $bic_installed = false;

  /**
   * Task just for starting
   * 
   * @param \CRM_Queue_TaskContext $ctx
   *
   * @return bool
   */
  public static function starting(CRM_Queue_TaskContext $ctx) {
    return TRUE;
  }


  /**
   * Task for creating new mandates based on batch of data from import file
   *
   * @param \CRM_Queue_TaskContext $ctx
   * @param array $batch
   *
   * @return bool
   */
  public static function createMandates(CRM_Queue_TaskContext $ctx, $batch) {
    $session = new CRM_Core_Session();
    $params = $session->get('params', 'sepa-import');
    $import_hash = $session->get('import_hash', 'sepa-import');
    self::$country_ids = $session->get('country_ids', 'sepa-import');
    $result = civicrm_api3('Extension', 'get', array(
      'sequential' => 1,
      'is_active' => 1,
      'key' => "org.project60.bic",
    ));
    self::$bic_installed = !!$result['count'];

    $logs = array();
    foreach ($batch as $id => $row) {
      $tx = new CRM_Core_Transaction();
      try {
        $contactId = self::createContact($row, $params);
        $result = self::createMandate($row, $params, $contactId);
        CRM_Sepamandatebatch_Logic_Bankstatus::createActivity($result['id']);
        $log = array(
          'import_hash' => $import_hash,
          'status' => CRM_Sepa_Logic_Import_Log::STATUS_OK,
          'reference' => $row[CRM_Sepa_Logic_Import::$column['reference']],
          'mandate_id' => $result['id'],
          'filename' => '',
          'row' => $id+1,
          'data' => serialize($row),
        );
        $logs[] = $log;
      } catch (Exception $ex) {
        $tx->rollback();
        $log = array(
          'import_hash' => $import_hash,
          'status' => CRM_Sepa_Logic_Import_Log::STATUS_FAILED,
          'reference' => $row[CRM_Sepa_Logic_Import::$column['reference']],
          'mandate_id' => 0,
          'filename' => '',
          'row' => $id+1,
          'data' => serialize($row),
          'api_error' => $ex->getMessage()."\n".$ex->getTraceAsString(),
        );
        $logs[] = $log;
      }
      $tx = null;
    }

    foreach ($logs as $log) {
      CRM_Sepa_Logic_Import_Log::add($log);
    }

    return TRUE;
  }


  /**
   * Get or create if needed contact.
   *
   * @param array $row One row from import file
   * @param array $importParams
   *
   * @return int Contact Id
   */
  private static function createContact($row, $importParams) {
    if ($row[CRM_Sepa_Logic_Import::$column['email']]) {
      $params = array(
        'sequential' => 1,
        'contact_type' => 'Individual',
        'email' => $row[CRM_Sepa_Logic_Import::$column['email']],
        'first_name' => $row[CRM_Sepa_Logic_Import::$column['first_name']],
        'last_name' => $row[CRM_Sepa_Logic_Import::$column['last_name']],
        'api.Address.get' => array(
          'id' => '$value.address_id',
          'contact_id' => '$value.id',
          'location_type_id' => self::$location_type_id,
          'is_primary' => 1,
          'country_id' => self::$country_ids[$row[CRM_Sepa_Logic_Import::$column['country_id']]],
          'postal_code' => $row[CRM_Sepa_Logic_Import::$column['postal_code']],
          'city' => $row[CRM_Sepa_Logic_Import::$column['city']],
          'street_address' => $row[CRM_Sepa_Logic_Import::$column['street_address']],
        ),
      );
      $result = civicrm_api3('Contact', 'get', $params);
      if ($result['count'] == 1) {
        $contactId = $result['id'];
        if ($result['values'][0]['api.Address.get']['count'] == 0) {
          self::newAddress($row, $contactId);
        }
        return $contactId;
      } else {
        return self::newContact($row, $importParams);
      }
    } else {
      return self::newContact($row, $importParams);
    }
  }


  /**
   * Create whole new contact.
   *
   * @param array $row
   * @param array $importParams
   *
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  private static function newContact($row, $importParams) {
    $params = array(
      'sequential' => 1,
      'contact_type' => 'Individual',
      'email' => $row[CRM_Sepa_Logic_Import::$column['email']],
      'first_name' => $row[CRM_Sepa_Logic_Import::$column['first_name']],
      'last_name' => $row[CRM_Sepa_Logic_Import::$column['last_name']],
      'birth_date' => $row[CRM_Sepa_Logic_Import::$column['birth_date']],
      'source' => E::ts('Campaign: %1', array('domain' => 'org.project60.sepa', 1 => $importParams['campaign_title'])),
      'api.Address.create' => array(
        'contact_id' => '$value.id',
        'location_type_id' => self::$location_type_id,
        'country_id' => self::$country_ids[$row[CRM_Sepa_Logic_Import::$column['country_id']]],
        'postal_code' => $row[CRM_Sepa_Logic_Import::$column['postal_code']],
        'city' => $row[CRM_Sepa_Logic_Import::$column['city']],
        'street_address' => $row[CRM_Sepa_Logic_Import::$column['street_address']],
      ),
    );
    if ($row[CRM_Sepa_Logic_Import::$column['phone']]) {
      $params['api.Phone.create'] = array(
        'contact_id' => '$value.id',
        'phone' => $row[CRM_Sepa_Logic_Import::$column['phone']],
        'location_type_id' => 4, // Other
        'phone_type_id' => 1, // Phone
      );
    }
    $result = civicrm_api3('Contact', 'create', $params);
    return (int)$result['id'];
  }


  /**
   * Create new primary address for given contact.
   *
   * @param array $row
   * @param int $contactId
   *
   * @throws \CiviCRM_API3_Exception
   */
  private static function newAddress($row, $contactId) {
    $params = array(
      'contact_id' => $contactId,
      'location_type_id' => self::$location_type_id,
      'is_primary' => 1,
      'country_id' => self::$country_ids[$row[CRM_Sepa_Logic_Import::$column['country_id']]],
      'postal_code' => $row[CRM_Sepa_Logic_Import::$column['postal_code']],
      'city' => $row[CRM_Sepa_Logic_Import::$column['city']],
      'street_address' => $row[CRM_Sepa_Logic_Import::$column['street_address']],
    );
    civicrm_api3('Address', 'create', $params);
  }

  /**
   * Create one mandate for contact.
   *
   * @param array $row
   * @param array $params Params and settings merged into single array
   * @param int $contactId
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  private static function createMandate($row, $params, $contactId) {
    // todo Add support for custom field
    $bic = '';
    if (self::$bic_installed) {
      $result = civicrm_api3('Bic', 'findbyiban', array(
        'sequential' => 1,
        'iban' => $row[CRM_Sepa_Logic_Import::$column['iban']],
      ));
      $bic = $result['bic'];
    }
    $amount = $row[CRM_Sepa_Logic_Import::$column['amount']];
    $amount = CRM_Sepa_Logic_Import::castAmount($amount);
    $params_mandate = array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'creditor_id' => $params['creditor_id'],
      'type' => $params['default_mandate_type'],
      'reference' => $row[CRM_Sepa_Logic_Import::$column['reference']],
      'iban' => $row[CRM_Sepa_Logic_Import::$column['iban']],
      'bic' => $bic,
      'source' => $row[CRM_Sepa_Logic_Import::$column['source']],
      'financial_type_id' => $params['financial_type_id'],
      'frequency_interval' => $params['import_interval'],
      'amount' => $amount,
      'start_date' => $params['start_date'],
      'create_date' => date('Y-m-d'),
      'cycle_day' => $params['collection_day'],
      'campaign_id' => $params['campaign_id'],
      'currency' => $params['currency'],
    );

    $contact_custom_field = $row[CRM_Sepa_Logic_Import::$column['contact_custom_field']] ?? null;
    $field = CRM_Sepa_Logic_Settings::getSetting('contact_custom_field');

    if( $field || $contact_custom_field )
    {
      if( !$contact_custom_field )
      {
        throw new CiviCRM_API3_Exception("Optional custom field for contact was not set in CSV file but defined in settings.");
      }

      if( !$field )
      {
        throw new CiviCRM_API3_Exception("Optional custom field contact was set in CSV file but not defined in settings.");
      }

      $params_mandate[$field] = $contact_custom_field;
    }

    $result = civicrm_api3('SepaMandate', 'createfull', $params_mandate);
    return $result;
  }
}
