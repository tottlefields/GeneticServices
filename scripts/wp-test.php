<?php
global $wpdb;

$_REQUEST['start'] = '2017-03-01';
$_REQUEST['from']  = '2017-03-31';
$data = getOrderDataForCsv();
$header = array_shift($data);

$tests = array();
foreach ($data as $order){
	if(isset($tests[$order[5]])){
		$tests[$order[5]] += intval($order[3]);
	}
	else{
		$tests[$order[5]] = intval($order[3]);
	}
}

arsort($tests);

foreach ($tests as $testCode => $testCount){
	echo $testCode." - ".$testCount."\n";
}


?>
