SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `File` (
  `F_id` INT NOT NULL AUTO_INCREMENT,
  `F_displayName` VARCHAR(255) NULL,
  `F_address` CHAR(55) NOT NULL,
  `F_timeStamp` BIGINT NULL DEFAULT 0,
  `F_fileSize` INT NULL DEFAULT 0,
  `F_hash` CHAR(40) NULL,
  `F_comment` VARCHAR(255) NULL,
  `F_mimeType` VARCHAR(255) NULL,
  PRIMARY KEY (`F_id`),
  UNIQUE INDEX `F_id_UNIQUE` (`F_id` ASC),
  UNIQUE INDEX `F_hash_UNIQUE` (`F_hash` ASC),
  UNIQUE INDEX `F_address_UNIQUE` (`F_address` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

ALTER TABLE `File` ADD `F_mimeType` VARCHAR(255);
ALTER TABLE `File` CHANGE `F_timeStamp` `F_timeStamp` INT UNSIGNED NULL DEFAULT '0';

<?php if (is_dir($sqlPath.'/procedures')) array_map(function ($inp,$sqlPath){if ($inp!='.' && $inp!='..'){include($sqlPath.'/procedures/'.$inp);}},scandir($sqlPath.'/procedures'),array_pad(array(),count(scandir($sqlPath.'/procedures')),$sqlPath));?>
<?php if (is_dir($sqlPath.'/migrations')) array_map(function ($inp,$sqlPath){if ($inp!='.' && $inp!='..'){include($sqlPath.'/migrations/'.$inp);}},scandir($sqlPath.'/migrations'),array_pad(array(),count(scandir($sqlPath.'/migrations')),$sqlPath));?>