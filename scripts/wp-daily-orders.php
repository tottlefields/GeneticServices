<?php
global $wpdb;

date_default_timezone_set("Europe/London");

#$NOW = strtotime('2017-05-08');
$NOW = strtotime('-1 day');

$_REQUEST['from'] = date('Y-m-d', $NOW);
$_REQUEST['start']  = $_REQUEST['from'];

$data = getOrderDataForCsv();
$header = array_shift($data);

if (count($data) > 1){
	foreach ($data as $order){
		echo $order[0]."\n";
	}	
	#echo $_REQUEST['start']."\t".$_REQUEST['from']."\t".$file."\n";
}
else{
	#echo $_REQUEST['start']."\t".$_REQUEST['from']."\tNo orders to export\n";
}

?>
