CREATE TABLE IF NOT EXISTS `credentials_group` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `credentials_id` char(60) DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `server_ticket` (
  `_id` char(100) NOT NULL,
  `fingerprint` int(11) DEFAULT NULL,
  `ip` varchar(255) DEFAULT '0',
  `port` int(6) DEFAULT '0',
  `os` char(10) DEFAULT '0',
  `connection_type` char(50) DEFAULT '0',
  `auth_method` char(50) DEFAULT '0',
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `server_ticket_credentials_group` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_ticket_hash` char(60) DEFAULT NULL,
  `credentials_group_id` int(11) DEFAULT NULL,
  UNIQUE KEY `_id` (`_id`),
  UNIQUE KEY `server_ticket_id_credentials_group_id` (`server_ticket_hash`,`credentials_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
