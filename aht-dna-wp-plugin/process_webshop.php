<?php

$DEBUG = 1;
$SPECIES = array('Canine Tests' => 'Canine');

$mysqli = mysqli_connect("localhost", "root", "", "es_webshop_import");
if (mysqli_connect_errno($mysqli)) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//CLIENTS (people)
$SQL = "SELECT GROUP_CONCAT(distinct ClientID) as client_id, FullName, Organisation, Email, Tel, Fax, Address, Address2, Address3, Town, County, Postcode,
		Country, ShippingName, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingAddress3, ShippingTown, ShippingCounty, ShippingPostcode, ShippingCountry
		FROM webshop_import WHERE imported=0 GROUP BY FullName, Email";
if ($DEBUG) { echo str_replace("\t", "", $SQL)."\n"; }

$RESULT = mysqli_query($mysqli, $SQL);
while ($row = mysqli_fetch_assoc($RESULT)){
	
	$SQL2 = 'SELECT id, ClientID FROM client WHERE 
			(FullName = "'.$row['FullName'].'") + 
			(Email = "'.$row['Email'].'") + 
			(ClientID = '.$row['client_id'].') > 2';
	if ($DEBUG) { echo str_replace("\t", "", $SQL2)."\n"; }
	$RESULT2 = mysqli_query($mysqli, $SQL2);
	if (mysqli_num_rows($RESULT2) > 0){
		echo "Client already found in the database - record needs updating\n";
	}
	else{
		echo "Client needs a record creating in the database\n";
		$SQL3 = 'INSERT INTO client(FullName, Organisation, Email, Tel, Fax, Address, Address2, Address3, Town, County, Postcode,
				Country, ShippingName, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingAddress3, ShippingTown, ShippingCounty, ShippingPostcode, ShippingCountry)
				VALUES ("'.$row['client_id'].'", "'.$SPECIES[$row['Species']].'", "'.$row['Breed'].'", "'.$row['RegisteredName'].'", "'.$row['Registration'].'", "'.$row['Sex'].'", "'.$row['TatooOrChip'].'", "'.$row['BirthDate'].'", "'.$row['PetName'].'", "'.$row['Colour'].'")';
		if ($DEBUG) { echo str_replace("\t", "", $SQL3)."\n"; }
	}
}



exit(0);

//ANIMALS
$SQL = "SELECT GROUP_CONCAT(AnimalID) as animal_ids, Species, Breed, RegisteredName, Registration, Sex, date_BirthDate, TatooOrChip, Colour, PetName 
		FROM webshop_import WHERE imported=0 GROUP BY RegisteredName, Registration, PetName, date_BirthDate, TatooOrChip";
if ($DEBUG) { echo str_replace("\t", "", $SQL)."\n"; }

$RESULT = mysqli_query($mysqli, $SQL);
while ($row = mysqli_fetch_assoc($RESULT)){
	//echo implode("\t", array($row['RegisteredName'], $row['Registration'], $row['date_BirthDate'], $row['TatooOrChip']))."\n";
	
	$SQL2 = 'SELECT id, AnimalID FROM animal WHERE 
			(RegisteredName = "'.$row['RegisteredName'].'") + 
			(PetName = "'.$row['PetName'].'") + 
			(Registration = "'.$row['Registration'].'") + 
			(BirthDate = "'.$row['date_BirthDate'].'") + 
			(TatooOrChip = "'.$row['TatooOrChip'].'") > 3';
	if ($DEBUG) { echo str_replace("\t", "", $SQL2)."\n"; }
	$RESULT2 = mysqli_query($mysqli, $SQL2);
	if (mysqli_num_rows($RESULT2) > 0){
		echo "Animal already found in the database - record needs updating\n";
	}
	else{
		echo "Animal needs a record creating in the database\n";
		$SQL3 = 'INSERT INTO animal(webshop_animal_ids, Species, Breed, RegisteredName, Registration, Sex, TatooOrChip, BirthDate, PetName, Colour)
				VALUES ("'.$row['animal_ids'].'", "'.$SPECIES[$row['Species']].'", "'.$row['Breed'].'", "'.$row['RegisteredName'].'", "'.$row['Registration'].'", "'.$row['Sex'].'", "'.$row['TatooOrChip'].'", "'.$row['BirthDate'].'", "'.$row['PetName'].'", "'.$row['Colour'].'")';
		if ($DEBUG) { echo str_replace("\t", "", $SQL3)."\n"; }
	}
}

?>