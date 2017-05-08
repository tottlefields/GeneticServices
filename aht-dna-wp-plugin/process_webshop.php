<?php

$MY_CNF = parse_ini_file($_SERVER['HOME']."/.my.cnf", true);
$DEBUG = 0;
$SPECIES = array('Canine Tests' => 'Canine');



$mysqli = mysqli_connect("localhost", $MY_CNF['client']['user'], $MY_CNF['client']['password'], "es_webshop_import");
if (mysqli_connect_errno($mysqli)) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error()."\n";
	exit(0);
}

//CLIENTS (people)
$SQL = "SELECT GROUP_CONCAT(distinct ClientID) as client_id, FullName, Organisation, Email, Tel, Fax, Address, Address2, Address3, Town, County, Postcode,
		Country, ShippingName, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingAddress3, ShippingTown, ShippingCounty, ShippingPostcode, ShippingCountry
		FROM webshop_import WHERE imported=0 GROUP BY FullName, Email";
if ($DEBUG) { echo str_replace("\t", "", $SQL)."\n"; }

$RESULT = mysqli_query($mysqli, $SQL);
while ($row = mysqli_fetch_assoc($RESULT)){
	
	$SQL2 = 'SELECT * FROM client WHERE 
			(FullName = "'.$row['FullName'].'") + 
			(Email = "'.$row['Email'].'") + 
			(ClientID = '.$row['client_id'].') >= 2';
	if ($DEBUG) { echo str_replace("\t", "", $SQL2)."\n"; }
	$RESULT2 = mysqli_query($mysqli, $SQL2) or printf("ERROR: %s\n", mysqli_error($mysqli));
	
	if (mysqli_num_rows($RESULT2) > 0){
		echo "Client already found in the database - record may need updating\n";
	}
	else{
		echo "Creating a new CLIENT record in the database\n";
		$SQL3 = 'INSERT INTO client(ClientID, FullName, Organisation, Email, Tel, Fax, Address, Address2, Address3, Town, County, Postcode,
				Country, ShippingName, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingAddress3, ShippingTown, ShippingCounty, ShippingPostcode, ShippingCountry)
				VALUES ("'.$row['client_id'].'", "'.$row['FullName'].'", "'.$row['Organisation'].'", "'.$row['Email'].'", "'.$row['Tel'].'", "'.$row['Fax'].'",
						"'.$row['Address'].'", "'.$row['Address2'].'", "'.$row['Address3'].'", "'.$row['Town'].'",	"'.$row['County'].'", "'.$row['Postcode'].'",
						"'.$row['Country'].'", "'.$row['ShippingName'].'", "'.$row['ShippingCompany'].'", "'.$row['ShippingAddress'].'", "'.$row['ShippingAddress2'].'",
						"'.$row['ShippingAddress3'].'", "'.$row['ShippingTown'].'", "'.$row['ShippingCounty'].'", "'.$row['ShippingPostcode'].'", "'.$row['ShippingCountry'].'")';
		if (!mysqli_query($mysqli, $SQL3)) { printf("ERROR: %s\n", mysqli_error($mysqli)); }
		if ($DEBUG) { echo str_replace("\t", "", $SQL3)."\n"; }
	}
}


//ANIMALS
$SQL = "SELECT GROUP_CONCAT(AnimalID) as animal_ids, min(AnimalID) as AnimalID, ClientID, Species, Breed, 
		RegisteredName, Registration, Sex, date_BirthDate, TattooOrChip, Colour, PetName 
		FROM webshop_import WHERE imported=0 GROUP BY RegisteredName, Registration, PetName, date_BirthDate, TattooOrChip";
if ($DEBUG) { echo str_replace("\t", "", $SQL)."\n"; }

$RESULT = mysqli_query($mysqli, $SQL);
while ($row = mysqli_fetch_assoc($RESULT)){
	$row['Species'] = $SPECIES[$row['Species']];
	
	$SQL2 = 'SELECT * FROM animal WHERE 
			(RegisteredName = "'.$row['RegisteredName'].'") + 
			(PetName = "'.$row['PetName'].'") + 
			(Registration = "'.$row['Registration'].'") + 
			(BirthDate = "'.$row['date_BirthDate'].'") + 
			(TattooOrChip = "'.$row['TattooOrChip'].'") > 3';
	if ($DEBUG) { echo str_replace("\t", "", $SQL2)."\n"; }
	$RESULT2 = mysqli_query($mysqli, $SQL2) or printf("ERROR: %s\n", mysqli_error($mysqli));
	
	if (mysqli_num_rows($RESULT2) > 0){
		$animal = mysqli_fetch_assoc($RESULT2);
		$animal['date_BirthDate'] = $animal['BirthDate'];
		$diffs = array_diff_assoc($row, $animal);
//		unset($diffs['id']);
		unset($diffs['animal_ids']);
		unset($diffs['BirthDate']);
		if (count($diffs) > 0){ 
			echo "Animal already found in the database - record needs updating\n";
			#print_r($diffs);
			$updates = array();
			foreach ($diffs as $col => $val){
				array_push($updates, $col.' = "'.$val.'"');
			}			
			$A_SQL = "UPDATE animal SET ".implode(', ', $updates)." WHERE id=".$animal['id'];
			if ($DEBUG) { echo str_replace("\t", "", $A_SQL)."\n"; }
			mysqli_query($mysqli, $A_SQL);
		}
	}
	else{
		echo "Creating a new ANIMAL record in the database\n";
		$SQL3 = 'INSERT INTO animal(webshop_animal_ids, AnimalID, ClientID, Species, Breed, RegisteredName, Registration, Sex, TattooOrChip, BirthDate, PetName, Colour)
				VALUES ("'.$row['animal_ids'].'", '.$row['AnimalID'].', '.$row['ClientID'].', "'.$row['Species'].'", "'.$row['Breed'].'", "'.$row['RegisteredName'].'",
						"'.$row['Registration'].'", "'.$row['Sex'].'",  "'.$row['TattooOrChip'].'", "'.$row['date_BirthDate'].'", "'.$row['PetName'].'", "'.$row['Colour'].'")';
		if (!mysqli_query($mysqli, $SQL3)) { printf("ERROR: %s\n", mysqli_error($mysqli)); }
		if ($DEBUG) { echo str_replace("\t", "", $SQL3)."\n"; }
	}
}



#$filename = $_SERVER['HOME'].'/projects/WebShop/wp_animals_updates.tsv';
$filename = $_SERVER['HOME'].'/projects/WebShop/tottlefields_updates.tsv';
if (file_exists($filename) && filesize($filename) > 50) {
	$fp = fopen($filename, 'r');	
	while ( !feof($fp) ){
		# 0=>AnimalID	1=>ClientID	2=>Breed	3=>PetName	4=>RegisteredName	5=>BirthDate	6=>sex	7=>colour	8=>TattooChip
		$mapping_array = array(0=>'AnimalID', 1=>'ClientID', 2=>'Breed', 3=>'PetName', 4=>'RegisteredName', 5=>'BirthDate', 6=>'sex', 7=>'colour', 8=>'TattooOrChip');
		$line = fgets($fp, 2048);	
		$data = str_getcsv($line, "\t");
		if (count($data) > 1){
			$data[6] = substr($data[6], 0, 1);
			$SQL4 = "SELECT AnimalID,ClientID,Breed,PetName,RegisteredName,BirthDate,Sex,Colour,TattooOrChip,id
					FROM animal WHERE AnimalID=".$data[0]." OR webshop_animal_ids LIKE '%".$data[0]."%'";
			$RESULT4 = mysqli_query($mysqli, $SQL4) or printf("ERROR: %s\n", mysqli_error($mysqli));
			if (mysqli_num_rows($RESULT4) > 0){
				$animal = mysqli_fetch_row($RESULT4);
				$diffs = array_diff_assoc($data, $animal);
				unset($diffs[9]);
				if (count($diffs) > 0){ 
					$updates = array();
					foreach ($diffs as $num => $val){
						array_push($updates, $mapping_array[$num].' = "'.$val.'"');
					}			
					$A_SQL = "UPDATE animal SET ".implode(', ', $updates)." WHERE id=".$animal[9];
					if ($DEBUG) { echo str_replace("\t", "", $A_SQL)."\n"; }
					mysqli_query($mysqli, $A_SQL);
				}
			}
			else{
				echo "ERROR - Dog does not exist in the database\n";
			}
		}
	}
	fclose($fp);
}




?>
