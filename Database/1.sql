-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema m2
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema m2
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `m2` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `m2` ;

-- -----------------------------------------------------
-- Table `m2`.`md1_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `m2`.`md1_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `message` VARCHAR(5000) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `m2`.`md2_tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `m2`.`md2_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tagname` VARCHAR(200) NOT NULL,
  `description` VARCHAR(500) NOT NULL,
  `color` VARCHAR(10) NOT NULL,
  `priority` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `tagname_UNIQUE` (`tagname` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `m2`.`md1000`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `m2`.`md1000` (
  `id_lognote` INT UNSIGNED NOT NULL,
  `id_tag` INT UNSIGNED NOT NULL,
  INDEX `fk_pivot_log_tags_MD3_log_idx` (`id_lognote` ASC),
  INDEX `fk_pivot_log_tags_MD4_tags1_idx` (`id_tag` ASC),
  CONSTRAINT `fk_pivot_log_tags_MD3_log`
    FOREIGN KEY (`id_lognote`)
    REFERENCES `m2`.`md1_log` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pivot_log_tags_MD4_tags1`
    FOREIGN KEY (`id_tag`)
    REFERENCES `m2`.`md2_tags` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
