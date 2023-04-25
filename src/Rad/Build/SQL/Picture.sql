CREATE TABLE IF NOT EXISTS `picture` (
  `id_picture` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `path_original` TEXT NULL DEFAULT NULL,
  `path_icon` TEXT NULL DEFAULT NULL,
  `path_small` TEXT NULL DEFAULT NULL,
  `path_normal` TEXT NULL DEFAULT NULL,
  `path_large` TEXT NULL DEFAULT NULL,
  `path_xlarge` TEXT NULL DEFAULT NULL,
  `path_xxlarge` TEXT NULL DEFAULT NULL,
  `title_i18n` INT(11) NOT NULL,
  `keyword_i18n` INT(11) NOT NULL,
  `description_i18n` INT(11) NOT NULL,
  `mime_type` VARCHAR(45) NULL DEFAULT NULL,
  `trash` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_picture`),
  UNIQUE INDEX `getBySlug` (`slug` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

