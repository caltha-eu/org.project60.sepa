<?php

class CRM_Sepa_Logic_Format_mbankpl extends CRM_Sepa_Logic_Format {

  /** @var string Charset used in output files. */
  public static $out_charset = 'ISO-8859-2';

  /** @var string Only accepted (3), active (5) or auto accepted (8) mandates should be processed */
  public static $generatexml_sql_where = ' AND mandate.bank_status IN (3, 5, 8)';

  /**
   * gives the option of setting extra variables to the template
   */
  public function assignExtraVariables($template) {
    $template->assign('settings', array(
      'nip' => '6762472999',
      'zleceniodawca_nazwa' => 'Fundacja Kupuj Odpowiedzialnie',
      'zleceniodawca_adres1' => 'ul. Sławkowska 12',
      'zleceniodawca_adres2' => '31-014 Kraków',
    ));
  }

  public function getDDFilePrefix() {
    return 'BRE-';
  }

  public function getFilename($variable_string) {
    return $variable_string . '.txt';
  }

}
