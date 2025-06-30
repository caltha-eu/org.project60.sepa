<?php

use CRM_Sepa_ExtensionUtil as E;

abstract class CRM_Sepa_Logic_Import {

  public static $errors = array();
  private static $error_message = '';

  /** @var array List of fields with required status */
  private static $required = array(
    'first_name' => TRUE,
    'last_name' => TRUE,
    'birth_date' => FALSE,
    'street_address' => TRUE,
    'city' => TRUE,
    'postal_code' => TRUE,
    'country_id' => TRUE,
    'phone' => FALSE,
    'email' => FALSE,
    'amount' => TRUE,
    'reference' => TRUE,
    'iban' => TRUE,
    'source' => FALSE,
    'note' => FALSE,
    'contact_custom_field' => FALSE,
  );

  /** @var array  */
  public static $column = array(
    'first_name' => 0,
    'last_name' => 1,
    'birth_date' => 2,
    'street_address' => 3,
    'city' => 4,
    'postal_code' => 5,
    'country_id' => 6,
    'phone' => 7,
    'email' => 8,
    'amount' => 9,
    'reference' => 10,
    'iban' => 11,
    'source' => 12,
    'note' => 13,
    'contact_custom_field' => 14,
  );

  private static $re = array(
    'iban' => '/[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}/',
    'email' => '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
  );

  private static $decimalDelimiter = ',';

  private static $thousandsDelimiter = ' ';

  private static $dateFormat = 'Y-m-d';


  /**
   * Get import settings
   *
   * @return array
   */
  public static function getSettings() {
    $keys = array(
      'batching_default_creditor' => null,
      'default_mandate_type' => null,
      'import_financial_type_id' => null,
      'import_campaign_id' => null,
      'import_collection_day' => null,
      'import_interval' => null,
      'import_date_format' => null,
      'import_thousands_delimiter' => null,
      'import_decimal_delimiter' => null,
      'import_contact_custom_field' => null,
      'import_batch_size' => 50,
      'recipient_tax_number' => null,
      'recipient_iban_number' => null,
      'recipient_info' => null,
    );
    foreach ($keys as $key => $val) {
      $value = CRM_Sepa_Logic_Settings::getSetting($key);
      $keys[$key] = $value;
    }
    return $keys;
  }


  /**
   * Validate whole content of file. Not valid rows recorded in self::$errors array.
   *
   * @param array $content
   * @param array $settings
   *
   * @return bool
   */
  public static function validateImportFile($content, $settings) {
    self::$decimalDelimiter = $settings['import_decimal_delimiter'];
    self::$thousandsDelimiter = $settings['import_thousands_delimiter'];
    self::$dateFormat = $settings['import_date_format'];

    self::$errors = array();
    $i = 0;
    foreach ($content as $row) {
      if (!self::validateRow($row)) {
        self::$errors[] = array('line' => ++$i, 'message' => self::$error_message);
      }
    }
    if (!self::validateUniqueReference($content)) {
      self::$errors[] = array('line' => 0, 'message' => E::ts('Mandate references are not unique in file', array('domain' => 'org.project60.sepa')));
    }
    return !count(self::$errors);
  }


  /**
   * Validate one row of file.
   *
   * @param array $row
   *
   * @return bool
   */
  private static function validateRow($row) {
    foreach (self::$required as $key => $requ) {
      if ($requ && !$row[self::$column[$key]]) {
        self::$error_message = E::ts('Required field %1 is not set', array('domain' => 'org.project60.sepa', 1 => $key));
        return false;
      }
      if (in_array($key, array_keys(self::$re)) && $row[self::$column[$key]]) {
        if (!preg_match(self::$re[$key], $row[self::$column[$key]])) {
          self::$error_message = E::ts('Field %1 has wrong format', array('domain' => 'org.project60.sepa', 1 => $key));
          return false;
        }
      }
      if ($key == 'amount') {
        if (!self::validateAmount($row[self::$column[$key]])) {
          self::$error_message = E::ts('Amount "%1" has wrong format', array('domain' => 'org.project60.sepa', 1 => $row[self::$column[$key]]));
          return false;
        }
      }
      if ($key == 'reference' && $row[self::$column[$key]]) {
        if (!self::validateReference($row[self::$column[$key]])) {
          self::$error_message = E::ts('Mandate reference "%1" already exists', array('domain' => 'org.project60.sepa', 1 => $row[self::$column[$key]]));
          return false;
        }
      }
    }
    return true;
  }


  /**
   * Validate format of amount.
   *
   * @param string $amount
   *
   * @return bool
   */
  private static function validateAmount($amount) {
    $new_amount = self::castAmount($amount);
    $new_amount = number_format($new_amount, 2, self::$decimalDelimiter, self::$thousandsDelimiter);
    return ($amount === $new_amount);
  }


  /**
   * Check if mandate reference is valid (doesn't exist in db already).
   *
   * @param String $reference
   *
   * @return bool True - ok, this reference can be imported
   */
  private static function validateReference($reference) {
    $query = "SELECT count(il.id)
              FROM civicrm_sdd_import_log il JOIN civicrm_sdd_mandate m ON il.reference = m.reference
              WHERE il.reference = %1 AND il.status = %2";
    $params = array(
      1 => array($reference, 'String'),
      2 => array(CRM_Sepa_Logic_Import_Log::STATUS_OK, 'Integer'),
    );
    $count = (int)CRM_Core_DAO::singleValueQuery($query, $params);
    return !$count;
  }


  /**
   * Check if mandate references are unique in whole file.
   *
   * @param array $content
   *
   * @return bool
   */
  private static function validateUniqueReference($content) {
    $references = array();
    foreach ($content as $row) {
      $references[$row[CRM_Sepa_Logic_Import::$column['reference']]] = $row[CRM_Sepa_Logic_Import::$column['reference']];
    }
    return (count($content) == count($references));
  }


  /**
   * Cast string amount from file into valid float type.
   *
   * @param String $amount
   *
   * @return float
   */
  public static function castAmount($amount) {
    $new_amount = preg_replace('/[^0-9.]+/', '', $amount);
    return $new_amount/100;
  }
}
