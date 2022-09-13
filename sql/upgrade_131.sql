-- /*******************************************************
-- *
-- * Add new feature - Import mandates from csv file.
-- *
-- *******************************************************/

CREATE TABLE IF NOT EXISTS `civicrm_sdd_import_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `import_hash` VARCHAR(45) NOT NULL,
  `log_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` INT(10) NOT NULL COMMENT 'Import status: 0 ok, -1 failed, 1 skipped',
  `reference` VARCHAR(35) NOT NULL COMMENT 'Mandate reference',
  `mandate_id` INT(10) UNSIGNED NULL,
  `filename` VARCHAR(255) NULL,
  `row` INT(10) NULL COMMENT 'Row number in file',
  `data` TEXT NULL COMMENT 'Serialized data used for import this mandate',
  `api_error` TEXT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;
