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
    CRM_Core_Error::debug_var('STARTING', 111);
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
    CRM_Core_Error::debug_var('$batch', $batch);
    CRM_Core_Error::debug_var('$params in task createMandates', $params);
    sleep(5);
    return TRUE;
  }
}
