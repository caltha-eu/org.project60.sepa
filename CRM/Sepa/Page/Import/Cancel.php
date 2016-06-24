<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_Import_Cancel extends CRM_Core_Page {

  function run() {
    $session = new CRM_Core_Session();
    $session->set('data', null, 'sepa-import');
    $session->set('params', null, 'sepa-import');
    $queue = CRM_Sepa_Logic_Import_Queue::singleton()->getQueue();
    $queue->deleteQueue();
    return parent::run();
  }
}
