CREATE TABLE IF NOT EXISTS  `language` (
  `slug` VARCHAR(3) NOT NULL,
  `name` VARCHAR(3) NOT NULL,
  `trash` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`slug`),
  UNIQUE INDEX `getBySlug` (`slug` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS  `i18n` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `i18n_translate` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `datas` LONGTEXT NOT NULL,
  `i18n_id` INT(11) NOT NULL,
  `language_slug` VARCHAR(3) NOT NULL,
  `checked` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`, `i18n_id`, `language_slug`),
  INDEX `getTranslationFromId` (`i18n_id` ASC) VISIBLE,
  FULLTEXT INDEX `getTranslateIdFromText_search` (`datas`) VISIBLE,
  CONSTRAINT `fk_i18n_translate_i18n1`
    FOREIGN KEY (`i18n_id`)
    REFERENCES `i18n` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_i18n_translate_language1`
    FOREIGN KEY (`language_slug`)
    REFERENCES `language` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;
