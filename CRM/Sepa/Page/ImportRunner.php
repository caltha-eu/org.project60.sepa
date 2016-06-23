<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_ImportRunner extends CRM_Core_Page {

  function run() {
    $queue = CRM_Sepa_Logic_ImportQueue::singleton()->getQueue();
    $runner = new CRM_Queue_Runner(array(
      'title' => ts('SEPA Import'),
      'queue' => $queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
      'onEnd' => array('CRM_Sepa_Page_ImportRunner', 'onEnd'),
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
