<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_ImportLogView extends CRM_Core_Page {

  function run() {
    $session = new CRM_Core_Session();
    $session->set('data', null, 'sepa-import');
    $session->set('params', null, 'sepa-import');
    $import_hash = $session->get('import_hash', 'sepa-import');
    $stats = CRM_Sepa_Logic_ImportLog::getStats($import_hash);
    $errors = CRM_Sepa_Logic_ImportLog::getFailedByHash($import_hash);
    $this->assign('import_hash', $import_hash);
    $this->assign('ok', (int)$stats[CRM_Sepa_Logic_ImportLog::STATUS_OK]['n']);
    $this->assign('failed', (int)$stats[CRM_Sepa_Logic_ImportLog::STATUS_FAILED]['n']);
    $this->assign('errors', $errors);
    return parent::run();
  }
}
