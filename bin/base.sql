-- This is base SQL script for create core database structure.
-- This script don't drop tables.

CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role TINYINT(3) NOT NULL DEFAULT 0,
  created DATETIME NOT NULL,
  updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE(email),
  INDEX(email, password),
  INDEX(role)
)
;
INSERT IGNORE INTO users SET
  email = 'harmen@futurumshop.com',
  password = SHA1('superadmin'),
  role = 100,
  created = NOW()
;