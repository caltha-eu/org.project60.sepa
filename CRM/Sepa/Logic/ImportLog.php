<?php

abstract class CRM_Sepa_Logic_ImportLog {

  const STATUS_OK = 0;
  const STATUS_FAILED = -1;
  const STATUS_SKIPPED = 1;

  public static function newHash() {
    return md5(time());
  }
  
  public static function add($values) {
    $query = "INSERT INTO civicrm_sdd_import_log (import_hash, status, reference, mandate_id, filename, row, data, api_error) 
              VALUES (%1, %2, %3, %4, %5, %6, %7, %8)";
    $params = array(
      1 => array($values['import_hash'], 'String'),
      2 => array($values['status'], 'Integer'),
      3 => array($values['reference'], 'String'),
      4 => array(isset($values['mandate_id']) ? $values['mandate_id'] : 0, 'Integer'),
      5 => array(isset($values['filename']) ? $values['filename'] : '', 'String'),
      6 => array(isset($values['row']) ? $values['row'] : 0, 'Integer'),
      7 => array(isset($values['data']) ? $values['data'] : '', 'String'),
      8 => array(isset($values['api_error']) ? $values['api_error'] : '', 'String'),
    );
    CRM_Core_DAO::executeQuery($query, $params);
  }
  
  public static function getFailedByHash($import_hash) {
    $data = array();
    $query = "SELECT * FROM civicrm_sdd_import_log WHERE import_hash = %1 AND status < 0";
    $params = array(
      1 => array($import_hash, 'String'),
    );
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while ($dao->fetch()) {
      $data[] = array(
        'import_hash' => $dao->import_hash,
        'status' => $dao->status,
        'reference' => $dao->reference,
        'mandate_id' => $dao->mandate_id,
        'filename' => $dao->filename,
        'row' => $dao->row,
        'data' => $dao->data,
        'api_error' => $dao->api_error,
      );
    }
    return $data;
  }


  public static function getStats($import_hash) {
    $data = array();
    $query = "SELECT status, count(id) n FROM civicrm_sdd_import_log WHERE import_hash = %1 GROUP BY status";
    $params = array(
      1 => array($import_hash, 'String'),
    );
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while ($dao->fetch()) {
      $data[$dao->status] = array(
        'status' => $dao->status,
        'n' => $dao->n,
      );
    }
    return $data;
  }
}
