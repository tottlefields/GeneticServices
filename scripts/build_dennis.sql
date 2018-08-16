SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS client;
DROP TABLE IF EXISTS animal;
DROP TABLE IF EXISTS vet;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS order_tests;
DROP TABLE IF EXISTS audit_trail;

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
  `ShippingName` varchar(100) DEFAULT NULL,
  `ShippingCompany` varchar(100) DEFAULT NULL,
  `ShippingAddress` varchar(100) DEFAULT NULL,
  `ShippingAddress2` varchar(100) DEFAULT NULL,
  `ShippingAddress3` varchar(100) DEFAULT NULL,
  `ShippingTown` varchar(100) DEFAULT NULL,
  `ShippingCounty` varchar(100) DEFAULT NULL,
  `ShippingPostcode` varchar(20) DEFAULT NULL,
  `ShippingCountry` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_idx` (`ClientID`)
);

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
  `PetName` varchar(50) DEFAULT NULL,
  `Colour` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `animal_idx` (`AnimalID`),
  KEY `client_idx` (`ClientID`)
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

CREATE TABLE `orders` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `OrderID` int(4) unsigned NOT NULL,
  `ClientID` int(4) unsigned NOT NULL,
  `OrderDate` date DEFAULT NULL,
  `ReportFormat` enum('EMAIL','POST','FAX') DEFAULT NULL,
  `VetReportFormat` enum('EMAIL','POST','FAX') DEFAULT NULL,
  `Paid` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_idx` (`OrderID`),
  KEY `ClientID` (`ClientID`)
);

CREATE TABLE `order_tests` (
  `SwabID` int(4) NOT NULL AUTO_INCREMENT,
  `OrderID` int(4) unsigned NOT NULL,
  `AnimalID` int(4) unsigned NOT NULL,
  `TestCode` varchar(20) NOT NULL,
  `Quantity` int(4) unsigned DEFAULT '1',
  `SampleType` enum('Swab','Blood') DEFAULT 'Swab',
  `VetID` int(4) unsigned DEFAULT NULL,
  `kit_sent` date DEFAULT NULL,
  `returned_date` date DEFAULT NULL,
  `received_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`SwabID`),
  UNIQUE KEY `OrderAnimalTest` (`OrderID`,`AnimalID`,`TestCode`),
  KEY `AnimalID` (`AnimalID`),
  KEY `OrderID` (`OrderID`),
  KEY `VetID` (`VetID`)
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

SET FOREIGN_KEY_CHECKS=1;
