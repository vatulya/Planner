-- This is base SQL script for create core database structure.
-- This script don't drop tables.

CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role TINYINT(3) NOT NULL DEFAULT 0,

  full_name VARCHAR(255) NOT NULL,
  address TEXT NOT NULL DEFAULT '',
  phone VARCHAR(75) NOT NULL DEFAULT '',
  emergency_phone VARCHAR(75) NOT NULL DEFAULT '',
  emergency_full_name VARCHAR(255) NOT NULL DEFAULT '',
  birthday DATE NOT NULL DEFAULT '0000-00-00',
  owner VARCHAR(255) NOT NULL DEFAULT 'Eigen',

  created DATETIME NOT NULL,
  updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE(email),
  INDEX(email, password),
  INDEX(role),
  INDEX(full_name)
)
;
INSERT IGNORE INTO users SET
  id = 1,
  email = 'harmen@futurumshop.com',
  password = SHA1('superadmin'),
  role = 100,
  full_name = 'Harmen van der Meulen',
  created = NOW()
;

-- THIS IS TEMPORARY DEV DATA
INSERT IGNORE INTO users (id, email, password, role, created, full_name) VALUES
(2, 'test_1@gmail.com', SHA1('test_1'), 20, NOW(), 'Test User 1'),
(3, 'test_2@gmail.com', SHA1('test_2'), 20, NOW(), 'Test User 2'),
(4, 'test_3@gmail.com', SHA1('test_3'), 20, NOW(), 'Test User 3'),
(5, 'test_4@gmail.com', SHA1('test_4'), 20, NOW(), 'Test User 4'),
(6, 'test_5@gmail.com', SHA1('test_5'), 50, NOW(), 'Test User 5')
;

CREATE TABLE IF NOT EXISTS groups (
  id INT NOT NULL AUTO_INCREMENT,
  group_name VARCHAR(255) NOT NULL,
  color VARCHAR (10) NOT NULL DEFAULT 'FFF',
  PRIMARY KEY (id),
  INDEX(group_name)
)
;

-- THIS IS TEMPORARY DEV DATA
INSERT IGNORE INTO groups (id, group_name) VALUES
(1, 'Test Group')
;

CREATE TABLE IF NOT EXISTS user_groups (
  user_id INT NOT NULL,
  group_id INT NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, group_id)
)
;

-- THIS IS TEMPORARY DEV DATA
INSERT IGNORE INTO user_groups (user_id, group_id, is_admin) VALUES
(2, 1, 0),
(3, 1, 0),
(4, 1, 0),
(5, 1, 0),
(6, 1, 1)
;

CREATE TABLE IF NOT EXISTS user_checks (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  check_date DATE DEFAULT NULL,
  check_in TIME DEFAULT NULL,
  check_out TIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX(user_id),
  INDEX(check_date),
  INDEX(check_in),
  INDEX(check_out)
)
;

CREATE TABLE user_day_work_plan (
  id int(10) NOT NULL AUTO_INCREMENT,
  user_id int(4) NOT NULL,
  date date NOT NULL,
  status1 int(2) NOT NULL  DEFAULT 0,
  status2 int(2) DEFAULT NULL,
  time_start time NOT NULL,
  time_end time NOT NULL,
  time_exclude time DEFAULT NULL,
  group_id int(4) DEFAULT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (user_id, group_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
;

CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `color_hex` varchar(10) NOT NULL,
  `editable` int(1) DEFAULT NULL,
  `edit_type` int(2) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
;

INSERT INTO `status`(`id`,`description`,`color`,`color_hex`,`editable`,`edit_type`) VALUES ( '0','White','White','FFFFFF',NULL,NULL);
INSERT INTO `status`(`id`,`description`,`color`,`color_hex`,`editable`,`edit_type`) VALUES ( '2','Werk','Green','32CD32','0',NULL);
INSERT INTO `status`(`id`,`description`,`color`,`color_hex`,`editable`,`edit_type`) VALUES ( '3','Vrij','Yellow','FFFF00','0',NULL);
INSERT INTO `status`(`id`,`description`,`color`,`color_hex`,`editable`,`edit_type`) VALUES ( '4','Ziekte','Red','E9967A','1','1');
INSERT INTO `status`(`id`,`description`,`color`,`color_hex`,`editable`,`edit_type`) VALUES ( '5','Dokter/overige',NULL,'00FFFF','1','0');
INSERT INTO `status`(`id`,`description`,`color`,`color_hex`,`editable`,`edit_type`) VALUES ( '6','Buitengewoon verlof','blue','0000FF','1','0');

CREATE TABLE `group_plannings` (
  id INT NOT NULL AUTO_INCREMENT,
  group_id INT NOT NULL,
  user_id INT NOT NULL DEFAULT 0,
  week_type ENUM('odd', 'even') NOT NULL,
  day_number INT NOT NULL,
  time_start TIME NOT NULL DEFAULT '00:00:00',
  time_end TIME NOT NULL DEFAULT '00:00:00',
  pause_start TIME NOT NULL DEFAULT '00:00:00',
  pause_end TIME NOT NULL DEFAULT '00:00:00',
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE (group_id, user_id, week_type, day_number),
  INDEX (user_id),
  INDEX (group_id)
)
;

CREATE TABLE `group_settings` (
  group_id INT NOT NULL,
  pause_start TIME DEFAULT NULL,
  pause_end TIME DEFAULT NULL,
  max_free_people INT NOT NULL DEFAULT 0,
  alert_over_limit_free_people INT NOT NULL DEFAULT 0,
  PRIMARY KEY (group_id)
)
;

CREATE TABLE `group_exceptions` (
  id INT NOT NULL AUTO_INCREMENT,
  group_id INT NOT NULL,
  exception_date DATE,
  max_free_people INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE (group_id, exception_date),
  INDEX (group_id),
  INDEX (exception_date)
)
;

CREATE TABLE `group_holidays` (
  id INT NOT NULL AUTO_INCREMENT,
  group_id INT NOT NULL,
  holiday_date DATE,
  holiday_name VARCHAR(255),
  PRIMARY KEY (id),
  UNIQUE (group_id, holiday_date),
  INDEX (group_id),
  INDEX (holiday_date)
)
;

CREATE TABLE `user_parameters` (
  user_id INT NOT NULL,
  total_free_time INT NOT NULL DEFAULT 0,
  allowed_free_time INT NOT NULL DEFAULT 0,
  regular_work_hours INT NOT NULL DEFAULT 40,
  PRIMARY KEY (user_id)
)
;

CREATE TABLE `user_requests` (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  request_id INT NOT NULL,
  request_date DATE NOT NULL,
  status ENUM('open', 'approved', 'rejected') NOT NULL DEFAULT 'open',
  comment TEXT NOT NULL,
  admin_id INT NOT NULL DEFAULT 0,
  created DATETIME NOT NULL,
  updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE (user_id, request_date),
  INDEX(user_id),
  INDEX(request_id),
  INDEX(request_date),
  INDEX(status)
)
;

CREATE TABLE `user_overtime` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(4) NOT NULL,
  `date` date NOT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `group_id` int(4) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`group_id`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8
;

CREATE TABLE `parameters` (
  id INT NOT NULL DEFAULT 1, -- this field just reserved and not used
  default_total_free_hours INT NOT NULL DEFAULT 216,
  PRIMARY KEY (id)
)
;
CREATE TABLE `user_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `group_id` INT(11) NOT NULL,
  `week` INT(11) NOT NULL,
  `work_hours` TIME DEFAULT NULL,
  `overtime_hours` TIME DEFAULT NULL,
  `ill_hours` TIME DEFAULT NULL,
  `vacation_hours` TIME DEFAULT NULL,
  `free_hours` TIME DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8


ALTER TABLE `user_parameters`
ADD COLUMN `year` YEAR NULL AFTER `regular_work_hours`,
ADD COLUMN `additional_free_time` INT(11) DEFAULT '0' NOT NULL AFTER `year`;
DELETE  FROM user_parameters;
ALTER TABLE `user_parameters` DROP PRIMARY KEY,     ADD PRIMARY KEY(`user_id`, `year`);
ALTER TABLE `user_parameters`  CHANGE `allowed_free_time` `used_free_time` INT(11) DEFAULT '0' NOT NULL;
ALTER TABLE `users`     ADD COLUMN `regular_work_hours` INT(3) DEFAULT '40' NOT NULL AFTER `updated`;
ALTER TABLE `user_parameters` DROP COLUMN `regular_work_hours`;

ALTER TABLE `status`     ADD COLUMN `is_holiday` BINARY(1) DEFAULT '0' NOT NULL AFTER `edit_type`;
UPDATE `status` SET `is_holiday`='1' WHERE `id`='5' OR `id`='3';
ALTER TABLE `status` ADD COLUMN `long_description` VARCHAR(512) NULL AFTER `is_holiday`;

ALTER TABLE `user_missing`     CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,    ADD PRIMARY KEY(`id`);
ALTER TABLE `user_history`     CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT,    ADD PRIMARY KEY(`id`);


-- Alert page alters

CREATE TABLE `user_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `type` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `notice_mailed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alertKey` (`date`,`type`,`user_id`,`group_id`),
  KEY `FK_user_alerts` (`type`),
  CONSTRAINT `FK_user_alerts` FOREIGN KEY (`type`) REFERENCES `status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;

ALTER TABLE `user_history`     ADD COLUMN `num_incident` INT(11) DEFAULT '0' NOT NULL AFTER `year`;
ALTER TABLE `status` ADD COLUMN  `alert_description` varchar(1024) NULL AFTER `long_description`;
ALTER TABLE `user_mail` ADD COLUMN `type` enum('overview','alerts') DEFAULT 'overview' NOT NULL after `email`;

UPDATE `status` SET `alert_description`='Fogot to check in' WHERE `id`='2';
UPDATE `status` SET `alert_description`='Ill' WHERE `id`='4';
UPDATE `status` SET `alert_description`='Holiday free' WHERE `id`='3';
UPDATE `status` SET `alert_description`='Doctor Visit' WHERE `id`='5';
UPDATE `status` SET `alert_description`='special paid free ' WHERE `id`='6';
UPDATE `status` SET `alert_description`='Overtime' WHERE `id`='7';



ALTER TABLE `user_requests`
CHANGE `status` `status` ENUM('open','approved','rejected','refunded') DEFAULT 'open' NOT NULL;

-- New wishes

CREATE TABLE `intervals_work` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `color_hex` varchar(10) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `intervals_pause` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `time_interval_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_time_intervals` (`time_interval_id`),
  CONSTRAINT `FK_time_intervals` FOREIGN KEY (`time_interval_id`) REFERENCES `intervals_pause` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `group_plannings` ADD COLUMN `interval_id` INT(11) DEFAULT '0' NOT NULL;
ALTER TABLE `group_plannings` DROP COLUMN `time_start`;
ALTER TABLE `group_plannings` DROP COLUMN `time_end`;
ALTER TABLE `group_plannings` DROP COLUMN `pause_start`;
ALTER TABLE `group_plannings` DROP COLUMN `pause_end`;
DELETE FROM `group_plannings`;
ALTER TABLE `group_plannings` ADD KEY `FK_interval_id` (`interval_id`);
ALTER TABLE `group_plannings` ADD CONSTRAINT `FK_interval_id` FOREIGN KEY (`interval_id`)
REFERENCES `intervals_work` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;