<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_Import_NotValid extends CRM_Core_Page {

  function run() {
    $session = new CRM_Core_Session();
    $errors = $session->get('errors', 'sepa-import');
    $session->set('errors', null, 'sepa-import');
    $this->assign('errors', $errors);
    return parent::run();
  }
}
