<?php
/*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2016-2018                                |
| Author: @scardinius                                    |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

class CRM_Sepa_Logic_Format_citibankpl extends CRM_Sepa_Logic_Format {

  /** @var string Only accepted (3), active (5) or auto_accepted (8) mandates should be processed */
  public static $generatexml_sql_where = ' AND mandate.bank_status IN (3, 5, 8)';

  /**
   * Apply string encoding
   *
   * @param string $content
   *
   * @return mixed
   */
  public function characterEncode($content) {
    return iconv('UTF-8', 'WINDOWS-1250', $content);
  }

  public function improveContent($content) {
    return preg_replace('~(*BSR_ANYCRLF)\R~', "\r\n", $content);
  }

  public function getDDFilePrefix() {
    return 'CITIBANK-';
  }

  public function getFilename($variable_string) {
    return $variable_string.'.txt';
  }

}
