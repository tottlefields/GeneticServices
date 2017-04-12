<?php
global $wpdb;

//$NOW = strtotime('2017-04-10 12:05:00');
$NOW = time();

$_REQUEST['from'] = date('Y-m-d H:i:s', $NOW);
$_REQUEST['start']  = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($_REQUEST['from'])));

$data = getOrderDataForCsv();
$header = array_shift($data);

if (count($data) > 0){
	$file = outputCSV($data);
	echo $_REQUEST['from']."\t".$file."\n";
}
else{
	echo $_REQUEST['from']."\tNo orders to export\n";
}

?>
