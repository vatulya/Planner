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
  birthdate DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',

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
(2, 'test1@gmail.com', SHA1('test1'), 20, NOW(), 'Test User 1'),
(3, 'test2@gmail.com', SHA1('test2'), 20, NOW(), 'Test User 2'),
(4, 'test3@gmail.com', SHA1('test3'), 20, NOW(), 'Test User 3'),
(5, 'test4@gmail.com', SHA1('test4'), 20, NOW(), 'Test User 4'),
(6, 'test5@gmail.com', SHA1('test5'), 50, NOW(), 'Test User 5')
;

CREATE TABLE IF NOT EXISTS groups (
  id INT NOT NULL,
  group_name VARCHAR(255) NOT NULL,
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
