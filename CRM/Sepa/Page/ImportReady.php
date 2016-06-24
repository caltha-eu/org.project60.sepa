<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_ImportReady extends CRM_Core_Page {

  // todo change to 50 after development
  // todo later : move param to import settings (default 50)
  private $batchSize = 3;

  function run() {
    $session = new CRM_Core_Session();
    $data = $session->get('data', 'sepa-import');
    $params = $session->get('params', 'sepa-import');
    $this->assign('rows', count($data));

    $queue = CRM_Sepa_Logic_ImportQueue::singleton()->getQueue();
    if (!$queue->numberOfItems()) {
      $settings = CRM_Sepa_Logic_Import::getSettings();
      $result = civicrm_api3('SepaCreditor', 'get', array('sequential' => 1, 'id' => $params['creditor_id'], 'return' => 'currency'));
      $settings['currency'] = $result['values'][0]['currency'];
      $session->set('params', array_merge($params, $settings) , 'sepa-import');

      $import_hash = CRM_Sepa_Logic_ImportLog::newHash();
      $session->set('import_hash', $import_hash, 'sepa-import');
      $session->set('country_ids', $this->determineCountryIds($data), 'sepa-import');

      $this->addTaskStarting($queue);

      $batches = ceil(count($data) / $this->batchSize);
      for ($i = 1; $i <= $batches; $i++) {
        $batch = array_slice($data, $i * $this->batchSize - $this->batchSize, $this->batchSize, TRUE);
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


  /**
   * Prepare array of country_id based on data from import file.
   *
   * @param array $data
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  private function determineCountryIds($data) {
    $country = array();
    $country_ids = array();
    foreach ($data as $row) {
      $country[$row[CRM_Sepa_Logic_Import::$column['country_id']]] = $row[CRM_Sepa_Logic_Import::$column['country_id']];
    }
    $result = civicrm_api3('Country', 'get', array(
      'sequential' => 1,
      'return' => "id,iso_code",
      'iso_code' => array('IN' => array_keys($country)),
    ));
    foreach ($result['values'] as $value) {
      $country_ids[$value['iso_code']] = $value['id'];
    }
    return $country_ids;
  }
}
