<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_ImportReady extends CRM_Core_Page {

  function run() {
    $session = new CRM_Core_Session();
    $data = $session->get('data', 'sepa-import');
    $params = $session->get('params', 'sepa-import');
    $this->assign('count', count($data));
    $this->assign('params', $params);

    $queue = CRM_Sepa_Logic_ImportQueue::singleton()->getQueue();

    // todo divide data into batches per 50 rows
    $this->addTaskStarting($queue);
    $this->addTaskCreateMandate($queue, $data);

    return parent::run();
  }


  /**
   * @param \CRM_Queue_Queue $queue
   */
  private function addTaskStarting(CRM_Queue_Queue &$queue) {
    $task = new CRM_Queue_Task(
      array('CRM_Sepa_Logic_ImportTasks', 'starting'),
      array()
    );
    $queue->createItem($task);
  }


  /**
   * @param \CRM_Queue_Queue $queue
   * @param $data
   */
  private function addTaskCreateMandate(CRM_Queue_Queue &$queue, $data) {
    $task = new CRM_Queue_Task(
      array('CRM_Sepa_Logic_ImportTasks', 'createMandates'),
      array($data)
    );
    $queue->createItem($task);
  }
}
