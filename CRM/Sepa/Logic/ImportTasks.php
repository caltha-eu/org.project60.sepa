<?php
/*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2013-2014 SYSTOPIA                       |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


/**
 * Class CRM_Sepa_Logic_ImportTasks
 */
class CRM_Sepa_Logic_ImportTasks {


  /**
   * Task just for starting
   * 
   * @param \CRM_Queue_TaskContext $ctx
   *
   * @return bool
   */
  public static function starting(CRM_Queue_TaskContext $ctx) {
    return TRUE;
  }


  /**
   * Task for creating new mandates based on batch of data from import file
   *
   * @param \CRM_Queue_TaskContext $ctx
   * @param array $batch
   *
   * @return bool
   */
  public static function createMandates(CRM_Queue_TaskContext $ctx, $batch) {
    $session = new CRM_Core_Session();
    $params = $session->get('params', 'sepa-import');
    $import_hash = $session->get('import_hash', 'sepa-import');

    foreach ($batch as $id => $row) {
      try {
        // todo add transaction and rollback
        $contactId = self::createContact($row);
        $result = self::createMandate($row, $params, $contactId);

        $log = array(
          'import_hash' => $import_hash,
          'status' => CRM_Sepa_Logic_ImportLog::STATUS_OK,
          'reference' => $row[CRM_Sepa_Logic_Import::$column['reference']],
          'mandate_id' => $result['id'],
          'filename' => '',
          'row' => $id+1,
          'data' => serialize($row),
        );
        CRM_Sepa_Logic_ImportLog::add($log);

      } catch (Exception $ex) {
        CRM_Core_Error::debug_var('$ex', $ex->getMessage());
        CRM_Core_Error::debug_var('$ex->getTraceAsString()', $ex->getTraceAsString());
        $log = array(
          'import_hash' => $import_hash,
          'status' => CRM_Sepa_Logic_ImportLog::STATUS_FAILED,
          'reference' => $row[CRM_Sepa_Logic_Import::$column['reference']],
          'mandate_id' => 0,
          'filename' => '',
          'row' => $id+1,
          'data' => serialize($row),
          'api_error' => $ex->getMessage()."\n".$ex->getTraceAsString(),
        );
        CRM_Sepa_Logic_ImportLog::add($log);
      }
    }
    return TRUE;
  }


  /**
   * Get or create if needed contact.
   *
   * @param array $row One row from import file
   *
   * @return int Contact Id
   */
  private static function createContact($row) {
    return 56247;
  }


  /**
   * Create one mandate for contact.
   *
   * @param array $row
   * @param array $params Params and settings merged into single array
   * @param int $contactId
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  private static function createMandate($row, $params, $contactId) {
    // todo Add support for custom field
    // todo Bic calculate by iban
    $bic = '';
    $amount = $row[CRM_Sepa_Logic_Import::$column['amount']];
    $amount = CRM_Sepa_Logic_Import::castAmount($amount);
    $params_mandate = array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'creditor_id' => $params['creditor_id'],
      'type' => $params['default_mandate_type'],
      'reference' => $row[CRM_Sepa_Logic_Import::$column['reference']],
      'iban' => $row[CRM_Sepa_Logic_Import::$column['iban']],
      'bic' => $bic,
      'source' => $row[CRM_Sepa_Logic_Import::$column['source']],
      'financial_type_id' => $params['financial_type_id'],
      'frequency_interval' => $params['import_interval'],
      'amount' => $amount,
      'start_date' => $params['start_date'],
      'create_date' => date('Y-m-d'),
      'cycle_day' => $params['collection_day'],
      'campaign_id' => $params['campaign_id'],
      'currency' => $params['currency'],
    );
    $result = civicrm_api3('SepaMandate', 'createfull', $params_mandate);
    return $result;
  }
}
