<?php

use CRM_Sepa_ExtensionUtil as E;

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_Import_Runner extends CRM_Core_Page {

  function run() {
    $queue = CRM_Sepa_Logic_Import_Queue::singleton()->getQueue();
    $runner = new CRM_Queue_Runner(array(
      'title' => E::ts('SEPA Import'),
      'queue' => $queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
      'onEnd' => array('CRM_Sepa_Page_Import_Runner', 'onEnd'),
      'onEndUrl' => CRM_Utils_System::url('civicrm/sepa/import-log-view'),
    ));
    $runner->runAllViaWeb();
  }

  /**
   * Handle the final step of the queue
   */
  static function onEnd(CRM_Queue_TaskContext $ctx) {
    CRM_Core_Session::setStatus('All tasks in queue are executes', 'Queue', 'success');
  }
}
