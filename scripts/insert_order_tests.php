<?php

$HOME = getenv('HOME');
$settings = parse_ini_file($HOME.'/.my.cnf',true);

$DB = 'es_wp_dennis';
$HOST = 'localhost';
$USER = $settings['client']['user'];
$PASS = $settings['client']['password'];


// Create & Check connection
$mysqli = new mysqli($HOST, $USER, $PASS, $DB);
if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error); } 

$sql = "select OrderID,AnimalID,TestCode,Quantity,SampleType,VetID,test_code,no_swabs,sub_tests
	from es_webshop_data.order_tests inner join test_code_webshop_lookup using (TestCode)
	inner join test_codes using (test_code)
	where OrderID NOT IN (select OrderID from order_tests where OrderID>0)";
$res = $mysqli->query($sql);

while ($row = $res->fetch_assoc()) {
	if(empty($row['VetID'])){$row['VetID'] = 0; }
	if ($row['no_swabs']>1){
		$sub_tests = explode(':', $row['sub_tests']);
		foreach ($sub_tests as $test_code){
			$mysqli->query("replace into order_tests (OrderID,AnimalID,TestCode,Quantity,SampleType,VetID,test_code) values (".$row['OrderID'].",".$row['AnimalID'].",'".$row['TestCode']."',".$row['Quantity'].",'".$row['SampleType']."',".$row['VetID'].",'".$test_code."')");
		}
	}
	else{
		$result = $mysqli->query("replace into order_tests (OrderID,AnimalID,TestCode,Quantity,SampleType,VetID,test_code) values (".$row['OrderID'].",".$row['AnimalID'].",'".$row['TestCode']."',".$row['Quantity'].",'".$row['SampleType']."','".$row['VetID']."','".$row['test_code']."')");
		if(!$result){ echo $mysqli->error."\n"; }
	}
}

$mysqli->close();
?>
