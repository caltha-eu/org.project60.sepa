<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_ImportReady extends CRM_Core_Page {

  private $batchSize = 3;

  function run() {
    $session = new CRM_Core_Session();
    $data = $session->get('data', 'sepa-import');
    $this->assign('rows', count($data) - 1);

    $queue = CRM_Sepa_Logic_ImportQueue::singleton()->getQueue();
    if (!$queue->numberOfItems()) {
      $this->addTaskStarting($queue);

      $batches = ceil((count($data) - 1) / $this->batchSize);
      for ($i = 1; $i <= $batches; $i++) {
        $batch = array_slice($data, $i * $this->batchSize - $this->batchSize, $this->batchSize);
        $this->addTaskCreateMandate($queue, $batch, $i, $batches);
      }
    }

    return parent::run();
  }


  /**
   * @param \CRM_Queue_Queue $queue
   */
  private function addTaskStarting(CRM_Queue_Queue &$queue) {
    $task = new CRM_Queue_Task(
      array('CRM_Sepa_Logic_ImportTasks', 'starting'),
      array(),
      'Starting import mandates'
    );
    $queue->createItem($task);
  }


  /**
   * @param \CRM_Queue_Queue $queue
   * @param array $batch
   * @param int $counter
   * @param int $n
   */
  private function addTaskCreateMandate(CRM_Queue_Queue &$queue, $batch, $counter, $n) {
    $task = new CRM_Queue_Task(
      array('CRM_Sepa_Logic_ImportTasks', 'createMandates'),
      array($batch),
      'Created mandates in batch '.$counter."/".$n
    );
    $queue->createItem($task);
  }
}
