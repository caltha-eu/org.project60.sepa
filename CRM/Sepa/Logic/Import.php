<?php

abstract class CRM_Sepa_Logic_Import {

  public static $errors = array();
  private static $error_message = '';

  /** @var array List of fields with required status */
  private static $required = array(
    'first_name' => TRUE,
    'last_name' => TRUE,
    'birth_date' => FALSE,
    'address_street' => TRUE,
    'city' => TRUE,
    'postal_code' => TRUE,
    'country_id' => TRUE,
    'phone' => FALSE,
    'email' => TRUE,
    'amount' => TRUE,
    'mandate_reference' => TRUE,
    'iban' => TRUE,
    'source' => FALSE,
    'note' => FALSE,
    'contact_custom_field' => FALSE,
  );

  /** @var array  */
  private static $column = array(
    'first_name' => 0,
    'last_name' => 1,
    'birth_date' => 2,
    'address_street' => 3,
    'city' => 4,
    'postal_code' => 5,
    'country_id' => 6,
    'phone' => 7,
    'email' => 8,
    'amount' => 9,
    'mandate_reference' => 10,
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
    $n = count($content);
    /* first row always contains header */
    for ($i = 1; $i < $n; $i++) {
      if (!self::validateRow($content[$i])) {
        self::$errors[] = array('line' => $i+1, 'message' => self::$error_message);
      }
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
        self::$error_message = ts('Required field %1 is not set', array('domain' => 'org.project60.sepa', 1 => $key));
        return false;
      }
      if (in_array($key, array_keys(self::$re))) {
        if (!preg_match(self::$re[$key], $row[self::$column[$key]])) {
          self::$error_message = ts('Field %1 has wrong format', array('domain' => 'org.project60.sepa', 1 => $key));
          return false;
        }
      }
      if ($key == 'amount') {
        if (!self::validateAmount($row[self::$column[$key]])) {
          self::$error_message = ts('Amount has wrong format', array('domain' => 'org.project60.sepa'));
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
    $new_amount = preg_replace('/[^0-9.]+/', '', $amount);
    $new_amount = number_format($new_amount/100, 2, self::$decimalDelimiter, self::$thousandsDelimiter);
    return ($amount === $new_amount);
  }
}
