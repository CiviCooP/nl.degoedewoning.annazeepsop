CREATE TABLE IF NOT EXISTS dgw_bsn (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `contact_id_1` int(11) DEFAULT NULL, 
  `contact_id_2` int(11) DEFAULT NULL,
  `bsn_1` varchar(15) DEFAULT NULL, 
  `bsn_2` varchar(15) DEFAULT NULL,
  `sort_name_1` varchar(45) DEFAULT NULL, 
  `sort_name_2` varchar(45) DEFAULT NULL,
  `display_name_1` varchar(45) DEFAULT NULL, 
  `display_name_2` varchar(45) DEFAULT NULL,
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
