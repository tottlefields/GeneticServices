SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `animal`;
DROP TABLE IF EXISTS `audit_trail`;
DROP TABLE IF EXISTS `breed_list`;
DROP TABLE IF EXISTS `breed_test_lookup`;
DROP TABLE IF EXISTS `client`;
DROP TABLE IF EXISTS `order_test_notes`;
DROP TABLE IF EXISTS `order_tests`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `test_code_webshop_lookup`;
DROP TABLE IF EXISTS `test_codes`;
DROP TABLE IF EXISTS `test_swab_results`;
DROP TABLE IF EXISTS `test_swabs`;
DROP TABLE IF EXISTS `vet`;

CREATE TABLE `animal` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `AnimalID` int(4) unsigned NOT NULL,
  `ClientID` int(4) unsigned NOT NULL,
  `Species` enum('Canine','Feline','Bovine','Equine') DEFAULT NULL,
  `Breed` varchar(255) DEFAULT NULL,
  `RegisteredName` varchar(255) DEFAULT NULL,
  `RegistrationNo` varchar(50) DEFAULT NULL,
  `Sex` enum('m','f','') DEFAULT '',
  `BirthDate` varchar(20) DEFAULT NULL,
  `TattooOrChip` varchar(50) DEFAULT NULL,
  `PetName` varchar(255) DEFAULT NULL,
  `Colour` varchar(100) DEFAULT NULL,
  `breed_id` int(4) unsigned DEFAULT NULL,
  `client_id` int(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_idx` (`ClientID`),
  KEY `animal_idx` (`AnimalID`)
);

CREATE TABLE `audit_trail` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `process` varchar(100) NOT NULL,
  `table` varchar(50) DEFAULT NULL,
  `row_id` int(4) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
);

CREATE TABLE `breed_list` (
  `ID` int(4) unsigned DEFAULT NULL,
  `breed` varchar(100) NOT NULL,
  `is_primary` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `breed_idx` (`breed`)
);

CREATE TABLE `breed_test_lookup` (
  `breed_id` int(4) NOT NULL,
  `test_code` varchar(10) NOT NULL,
  PRIMARY KEY (`breed_id`,`test_code`)
);

CREATE TABLE `client` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `ClientID` int(4) unsigned NOT NULL,
  `Tel` varchar(100) DEFAULT NULL,
  `Fax` varchar(100) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(100) DEFAULT NULL,
  `Address2` varchar(100) DEFAULT NULL,
  `Address3` varchar(100) DEFAULT NULL,
  `Town` varchar(100) DEFAULT NULL,
  `County` varchar(100) DEFAULT NULL,
  `Postcode` varchar(20) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_idx` (`ClientID`)
);

CREATE TABLE `order_test_notes` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `test_id` int(4) unsigned DEFAULT NULL,
  `note_by` varchar(50) DEFAULT NULL,
  `note_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `note_text` text,
  PRIMARY KEY (`id`),
  KEY `text_idx` (`test_id`)
);

CREATE TABLE `order_tests` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `OrderID` int(4) unsigned DEFAULT NULL,
  `PortalID` varchar(10) DEFAULT NULL,
  `AnimalID` int(4) unsigned DEFAULT NULL,
  `TestCode` varchar(20) NOT NULL,
  `Quantity` int(4) unsigned DEFAULT '1',
  `SampleType` enum('Swab','Blood') DEFAULT 'Swab',
  `VetID` int(4) unsigned DEFAULT NULL,
  `kit_sent` datetime DEFAULT NULL,
  `sent_by` varchar(50) DEFAULT NULL,
  `returned_date` datetime DEFAULT NULL,
  `received_by` varchar(50) DEFAULT NULL,
  `order_id` int(4) unsigned NOT NULL DEFAULT '0',
  `animal_id` int(4) unsigned NOT NULL DEFAULT '0',
  `test_code` varchar(10) DEFAULT NULL,
  `bundle` varchar(50) DEFAULT NULL,
  `cancelled_date` datetime DEFAULT NULL,
  `cancelled_by` varchar(50) DEFAULT NULL,
  `repeat_swab` int(4) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `AnimalID` (`AnimalID`),
  KEY `OrderID` (`OrderID`),
  KEY `VetID` (`VetID`),
  KEY `animal_idx` (`animal_id`),
  KEY `order_idx` (`order_id`),
  KEY `portal_idx` (`PortalID`),
  KEY `OrderAnimalTest` (`OrderID`,`AnimalID`,`TestCode`)
);

CREATE TABLE `orders` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(4) unsigned NOT NULL DEFAULT '0',
  `OrderID` int(4) unsigned NOT NULL,
  `ClientID` int(4) unsigned NOT NULL,
  `OrderDate` date DEFAULT NULL,
  `ReportFormat` enum('EMAIL','POST','FAX') DEFAULT NULL,
  `VetReportFormat` enum('EMAIL','POST','FAX') DEFAULT NULL,
  `Paid` int(1) DEFAULT '0',
  `AgreeResearch` int(1) NOT NULL DEFAULT '0',
  `content` varchar(100) DEFAULT '',
  `ShippingName` varchar(100) DEFAULT NULL,
  `ShippingCompany` varchar(100) DEFAULT NULL,
  `ShippingAddress` varchar(100) DEFAULT NULL,
  `ShippingAddress2` varchar(100) DEFAULT NULL,
  `ShippingAddress3` varchar(100) DEFAULT NULL,
  `ShippingTown` varchar(100) DEFAULT NULL,
  `ShippingCounty` varchar(100) DEFAULT NULL,
  `ShippingPostcode` varchar(50) DEFAULT NULL,
  `ShippingCountry` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ClientID` (`ClientID`),
  KEY `client_idx` (`client_id`),
  KEY `order_idx` (`OrderID`)
);

CREATE TABLE `test_code_webshop_lookup` (
  `test_code` varchar(10) NOT NULL,
  `TestCode` varchar(50) NOT NULL,
  UNIQUE KEY `TestCode` (`test_code`,`TestCode`)
);

CREATE TABLE `test_codes` (
  `test_code` varchar(10) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `no_swabs` int(4) unsigned DEFAULT '1',
  `no_results` int(4) unsigned DEFAULT '1',
  `is_current` int(1) NOT NULL DEFAULT '1',
  `sub_tests` varchar(100) DEFAULT NULL,
  UNIQUE KEY `test_idx` (`test_code`)
);

CREATE TABLE `test_swab_results` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `test_id` int(4) unsigned NOT NULL,
  `swab_id` int(4) unsigned NOT NULL,
  `test_code` varchar(10) NOT NULL,
  `test_plate` varchar(10) DEFAULT NULL,
  `test_plate_well` varchar(4) DEFAULT NULL,
  `test_result` enum('AFFECTED','CARRIER','NORMAL') DEFAULT NULL,
  `result_entered_by` varchar(50) DEFAULT NULL,
  `result_entered_date` datetime DEFAULT NULL,
  `result_authorised_by` varchar(50) DEFAULT NULL,
  `result_authorised_date` datetime DEFAULT NULL,
  `cert_code` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `test_idx` (`swab_id`,`test_code`),
  UNIQUE KEY `cert_idx` (`cert_code`)
);

CREATE TABLE `test_swabs` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `test_id` int(4) unsigned NOT NULL,
  `swab` enum('A','B') NOT NULL DEFAULT 'A',
  `extraction_plate` varchar(10) DEFAULT NULL,
  `extraction_well` varchar(4) DEFAULT NULL,
  `swab_failed` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `swab_idx` (`test_id`,`swab`)
);

CREATE TABLE `vet` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `VetID` int(4) unsigned NOT NULL,
  `Tel` varchar(100) DEFAULT NULL,
  `Fax` varchar(100) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Address` varchar(100) DEFAULT NULL,
  `Address2` varchar(100) DEFAULT NULL,
  `Address3` varchar(100) DEFAULT NULL,
  `Town` varchar(100) DEFAULT NULL,
  `County` varchar(100) DEFAULT NULL,
  `Postcode` varchar(20) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vet_idx` (`VetID`)
);