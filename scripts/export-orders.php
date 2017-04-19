<?php
global $wpdb;

date_default_timezone_set("Europe/London");

#$NOW = strtotime('2017-04-13 11:00:00');
$NOW = strtotime('-1 hour');

$_REQUEST['from'] = date('Y-m-d H:i:s', $NOW);
$_REQUEST['start']  = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($_REQUEST['from'])));

$data = getOrderDataForCsv();
#$header = array_shift($data);

if (count($data) > 1){
	$file = outputCSV($data);
	echo $_REQUEST['start']."\t".$_REQUEST['from']."\t".$file."\n";
}
else{
	echo $_REQUEST['start']."\t".$_REQUEST['from']."\tNo orders to export\n";
}

?>
