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
 * This is a helper class for the import queue.
 * It is a singleton class because it will hold the queue object for our extension
 */
class CRM_Sepa_Logic_Import_Queue {

  const QUEUE_NAME = 'org.project60.sepa.import';

  private $queue;

  static $singleton;

  
  /**
   * CRM_Sepa_Logic_ImportQueue constructor sets CRM_Queue_Queue object
   */
  private function __construct() {
    $this->queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => self::QUEUE_NAME,
      'reset' => false, //do not flush queue upon creation
    ));
  }


  /**
   * @return CRM_Sepa_Logic_Import_Queue
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Sepa_Logic_Import_Queue();
    }
    return self::$singleton;
  }


  /**
   * @return CRM_Queue_Queue
   */
  public function getQueue() {
    return $this->queue;
  }
}
