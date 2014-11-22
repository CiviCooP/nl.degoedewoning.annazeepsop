CREATE TABLE IF NOT EXISTS dgw_bsn (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `contact_id_1` int(11) DEFAULT NULL, 
  `contact_id_2` int(11) DEFAULT NULL,
  `bsn_1` varchar(15) DEFAULT NULL, 
  `bsn_2` varchar(15) DEFAULT NULL,
  `sort_name_1` varchar(128) DEFAULT NULL, 
  `sort_name_2` varchar(128) DEFAULT NULL,
  `display_name_1` varchar(128) DEFAULT NULL, 
  `display_name_2` varchar(128) DEFAULT NULL,
  `birth_date_1` date DEFAULT NULL, 
  `birth_date_2` date DEFAULT NULL,
  `street_address_1` varchar(75) DEFAULT NULL, 
  `street_address_2` varchar(75) DEFAULT NULL,
  `city_1` varchar(45) DEFAULT NULL, 
  `city_2` varchar(45) DEFAULT NULL,
  `postal_code_1` varchar(45) DEFAULT NULL, 
  `postal_code_2` varchar(45) DEFAULT NULL,
  `phone_1` varchar(45) DEFAULT NULL, 
  `phone_2` varchar(45) DEFAULT NULL,
  `email_1` varchar(75) DEFAULT NULL, 
  `email_2` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS dgw_suspects1 (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `contact_id_1` int(11) DEFAULT NULL, 
  `contact_id_2` int(11) DEFAULT NULL,
  `bsn_1` varchar(15) DEFAULT NULL, 
  `bsn_2` varchar(15) DEFAULT NULL,
  `display_name_1` varchar(128) DEFAULT NULL, 
  `display_name_2` varchar(128) DEFAULT NULL,
  `sort_name_1` varchar(128) DEFAULT NULL,
  `sort_name_2` varchar(128) DEFAULT NULL,
  `birth_date_1` date DEFAULT NULL, 
  `birth_date_2` date DEFAULT NULL,
  `street_address_1` varchar(75) DEFAULT NULL, 
  `street_address_2` varchar(75) DEFAULT NULL,
  `city_1` varchar(45) DEFAULT NULL, 
  `city_2` varchar(45) DEFAULT NULL,
  `postal_code_1` varchar(45) DEFAULT NULL, 
  `postal_code_2` varchar(45) DEFAULT NULL,
  `phone_1` varchar(45) DEFAULT NULL, 
  `phone_2` varchar(45) DEFAULT NULL,
  `phone_type_id_1` int(11) DEFAULT NULL,
  `phone_type_id_2` int(11) DEFAULT NULL,
  `email_1` varchar(75) DEFAULT NULL, 
  `email_2` varchar(75) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS dgw_suspects2 (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `contact_id_1` int(11) DEFAULT NULL, 
  `contact_id_2` int(11) DEFAULT NULL,
  `bsn_1` varchar(15) DEFAULT NULL, 
  `bsn_2` varchar(15) DEFAULT NULL,
  `display_name_1` varchar(128) DEFAULT NULL, 
  `display_name_2` varchar(128) DEFAULT NULL,
  `sort_name_1` varchar(128) DEFAULT NULL,
  `sort_name_2` varchar(128) DEFAULT NULL,
  `birth_date_1` date DEFAULT NULL, 
  `birth_date_2` date DEFAULT NULL,
  `street_address_1` varchar(75) DEFAULT NULL, 
  `street_address_2` varchar(75) DEFAULT NULL,
  `city_1` varchar(45) DEFAULT NULL, 
  `city_2` varchar(45) DEFAULT NULL,
  `postal_code_1` varchar(45) DEFAULT NULL, 
  `postal_code_2` varchar(45) DEFAULT NULL,
  `phone_1` varchar(45) DEFAULT NULL, 
  `phone_2` varchar(45) DEFAULT NULL,
  `phone_type_id_1` int(11) DEFAULT NULL,
  `phone_type_id_2` int(11) DEFAULT NULL,
  `email_1` varchar(75) DEFAULT NULL, 
  `email_2` varchar(75) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS dgw_suspects3 (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `contact_id_1` int(11) DEFAULT NULL, 
  `contact_id_2` int(11) DEFAULT NULL,
  `bsn_1` varchar(15) DEFAULT NULL, 
  `bsn_2` varchar(15) DEFAULT NULL,
  `display_name_1` varchar(128) DEFAULT NULL, 
  `display_name_2` varchar(128) DEFAULT NULL,
  `sort_name_1` varchar(128) DEFAULT NULL,
  `sort_name_2` varchar(128) DEFAULT NULL,
  `birth_date_1` date DEFAULT NULL, 
  `birth_date_2` date DEFAULT NULL,
  `street_address_1` varchar(75) DEFAULT NULL, 
  `street_address_2` varchar(75) DEFAULT NULL,
  `city_1` varchar(45) DEFAULT NULL, 
  `city_2` varchar(45) DEFAULT NULL,
  `postal_code_1` varchar(45) DEFAULT NULL, 
  `postal_code_2` varchar(45) DEFAULT NULL,
  `phone_1` varchar(45) DEFAULT NULL, 
  `phone_2` varchar(45) DEFAULT NULL,
  `phone_type_id_1` int(11) DEFAULT NULL,
  `phone_type_id_2` int(11) DEFAULT NULL,
  `email_1` varchar(75) DEFAULT NULL, 
  `email_2` varchar(75) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dgw_first_deleted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id_first` int(11) DEFAULT NULL,
  `display_name_first` varchar(128) DEFAULT NULL,
  `gender_first` varchar(15) DEFAULT NULL,
  `birth_date_first` date DEFAULT NULL,
  `renter_first` tinyint(4) DEFAULT NULL,
  `main_renter_first` tinyint(4) DEFAULT NULL,
  `start_date_first` date DEFAULT NULL,
  `end_date_first` date DEFAULT NULL,
  `reason_first` varchar(45) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `active_hov` tinyint(4) DEFAULT 0,
  `active_act` tinyint(4) DEFAULT 0,
  `active_case` tinyint(4) DEFAULT 0,
  `active_group` tinyint(4) DEFAULT 0,
  `active_relation` tinyint(4) DEFAULT 0,
  `reason_civicrm` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `contact_id_first_INDEX` (`contact_id_first`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
