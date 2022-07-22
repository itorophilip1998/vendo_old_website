CREATE TABLE IF NOT EXISTS `vendo`.`TemporaryEntryCodes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `date` DATETIME NULL,
  `code` VARCHAR(60) NULL,
  `sponsor_user_id` INT NULL,
  `user_id` INT NULL,
  PRIMARY KEY (`id`)
  );

CREATE TABLE IF NOT EXISTS `vendo`.`User` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `upline_user_id` INT NULL,
  `sex` TINYINT NULL,
  `given_name` TEXT NULL,
  `sur_name` TEXT NULL,
  `date_of_birth` DATE NULL,
  `street` TEXT NULL,
  `postcode` TEXT NULL,
  `city` TEXT NULL,
  `country` TEXT NULL,
  `mobile_number` TEXT NULL,
  `email` TEXT NULL,
  `password` TEXT NULL,
  `md5_hash` CHAR 32,
  `own_money` TINYINT NULL,
  `awareness_total_loss` TINYINT NULL,
  `assets_volume` INT NULL,
  `existence_threat` INT NULL,
  `reg_complete` TINYINT DEFAULT 0,
  PRIMARY KEY (`id`)
);

ALTER TABLE `vendo`.`User` ADD `trading_account` INT NULL DEFAULT -1;

ALTER TABLE `vendo`.`User` ADD `language` TEXT NULL;

ALTER TABLE `vendo`.`User` ADD `payment_method` INT NULL DEFAULT -1;

CREATE TABLE IF NOT EXISTS `OpenBuyingTransactions` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `product` varchar(1024) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `api_amount` double NOT NULL,
  `paid_amount` double NOT NULL,
  `address` varchar(100) NOT NULL,
  `dest_tag` text NOT NULL,
  `transaction_id` varchar(1000) NOT NULL,
  `confirms_needed` int(11) NOT NULL,
  `timeout` double NOT NULL,
  `checkout_url` text NOT NULL,
  `status_url` text NOT NULL,
  `qrcode_url` text NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `vendo`.`TemporaryEntryCodes` ADD `date_checked` DATETIME NULL;

ALTER TABLE `User` ADD `automation` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Shows whether automation is turned on, turned off, or pending';
ALTER TABLE `User` ADD `AccountNumber` VARCHAR(256) NULL DEFAULT NULL COMMENT 'Accountnumber from Broker - getInfo() with id';

ALTER TABLE `User` ADD `housenumber` VARCHAR(128) NOT NULL DEFAULT '' AFTER `street`;

CREATE TABLE IF NOT EXISTS `Emails` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`from` TEXT DEFAULT NULL,
	`to` TEXT NOT NULL,
	`subject` TEXT NOT NULL,
	`message` TEXT NOT NULL,
	`send_on` DATETIME NULL DEFAULT NULL,
	`sent_on` DATETIME NULL DEFAULT NULL,
  `attachments` TEXT NULL COMMENT 'JSON array of attachment paths',
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `User` ADD `profile_picture_name` VARCHAR(256) NULL DEFAULT NULL COMMENT 'Name of the profile-picture';

ALTER TABLE `User` ADD `balance` DOUBLE NOT NULL DEFAULT 0;
ALTER TABLE `User` ADD `equity` DOUBLE NOT NULL DEFAULT 0;
ALTER TABLE `User` ADD `last_trading_history_end_date` DATE NOT NULL DEFAULT '2010-01-01';

CREATE TABLE IF NOT EXISTS `OpenOrders` (
	`userId` INT NOT NULL,
  `order` INT NOT NULL,
	`cmd` VARCHAR(1000),
	`symbol` VARCHAR(1000) NULL,
	`volume` DOUBLE NOT NULL DEFAULT 0,
	`open_price` DOUBLE NOT NULL DEFAULT 0,
	`sl` VARCHAR(1000) NULL,
	`tp` VARCHAR(1000) NULL,
  `close_price` DOUBLE NOT NULL DEFAULT 0,
  `open_time` DATETIME NULL,
	`commission` DOUBLE NOT NULL DEFAULT 0,
	`profit` DOUBLE NOT NULL DEFAULT 0,
	`storage` VARCHAR(1000) NULL,
  `comment` TEXT NULL,
	PRIMARY KEY (`order`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PendingOrders` (
	`userId` INT NOT NULL,
  `order` INT NOT NULL,
	`cmd` VARCHAR(1000),
	`symbol` VARCHAR(1000) NULL,
	`volume` DOUBLE NOT NULL DEFAULT 0,
	`open_price` DOUBLE NOT NULL DEFAULT 0,
	`sl` VARCHAR(1000) NULL,
	`tp` VARCHAR(1000) NULL,
  `close_price` DOUBLE NOT NULL DEFAULT 0,
  `open_time` DATETIME NULL,
	`commission` DOUBLE NOT NULL DEFAULT 0,
	`profit` DOUBLE NOT NULL DEFAULT 0,
	`storage` VARCHAR(1000) NULL,
  `comment` TEXT NULL,
	PRIMARY KEY (`order`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `OrderHistory` (
	`userId` INT NOT NULL,
  `order` INT NOT NULL,
	`cmd` VARCHAR(1000),
	`symbol` VARCHAR(1000) NULL,
	`volume` DOUBLE NOT NULL DEFAULT 0,
	`open_price` DOUBLE NOT NULL DEFAULT 0,
	`sl` VARCHAR(1000) NULL,
	`tp` VARCHAR(1000) NULL,
  `close_price` DOUBLE NOT NULL DEFAULT 0,
  `open_time` DATETIME NULL,
	`commission` DOUBLE NOT NULL DEFAULT 0,
	`profit` DOUBLE NOT NULL DEFAULT 0,
	`storage` VARCHAR(1000) NULL,
  `comment` TEXT NULL,
	PRIMARY KEY (`order`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `User` ADD `downline_direct_count` INT NOT NULL DEFAULT '0' , ADD `downline_total_count` INT NOT NULL DEFAULT '0' , ADD `access_downline_total` DOUBLE NOT NULL DEFAULT '0' ;

ALTER TABLE `OpenBuyingTransactions` ADD `paid_amount_usd` DOUBLE NOT NULL DEFAULT '0' AFTER `paid_amount`;

ALTER TABLE `User` ADD `broker_registration_complete` TINYINT NOT NULL DEFAULT '0' AFTER `reg_complete`;

ALTER TABLE `User` ADD `access_volume_paid` DOUBLE NOT NULL DEFAULT '0' ;

ALTER TABLE `User` ADD `downline_level` INT NOT NULL DEFAULT '0' ;

ALTER TABLE `OpenBuyingTransactions` ADD `started_on` DATETIME NOT NULL DEFAULT '2020-05-01' ;

-- ^^^^ INSTALLED ON PRODUCTION SERVER ^^^^ --

-- ^^^^ INSTALLED ON TEST SERVER ^^^^ --

