<?php

require_once 'CRM/Core/Page.php';

class CRM_Sepa_Page_ImportLogView extends CRM_Core_Page {

  function run() {
    $session = new CRM_Core_Session();
    $session->set('data', null, 'sepa-import');
    $session->set('params', null, 'sepa-import');
    $import_hash = $session->get('import_hash', 'sepa-import');
    $stats = CRM_Sepa_Logic_ImportLog::getStats($import_hash);
    $failed = CRM_Sepa_Logic_ImportLog::getFailedByHash($import_hash);
    $this->assign('stats', $stats);
    $this->assign('failed', $failed);
    return parent::run();
  }
}
