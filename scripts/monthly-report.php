<?php
global $wpdb;

$yesterday = date('Y-m-d', time()-60*60*24);
$last_month = date('Y-m', time()-60*60*24);
$_REQUEST['start'] = $last_month.'-01';
$_REQUEST['from']  = $yesterday;

//$_REQUEST['start'] = '2017-03-01';
//$_REQUEST['from']  = '2017-03-31';

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
$message = '
Please find below the total number of each test sold on the DNA Testing webshop for '.date('F Y', time()-60*60*24).'<br />
<br />
<table>';
foreach ($tests as $testCode => $testCount){
	$message .= '<tr><td>'.$testCode."</td><td>".$testCount.'</td></tr>';
}
$message .= '</table>';

$TO = 'ellen.schofield@aht.org.uk, samantha.gunn@aht.org.uk';
//$TO = 'ellen.schofield@aht.org.uk';
$TITLE = '[DNA Testing] Tests Sold During '.date('F Y', time()-60*60*24);
//$headers = array('FROM:dnatesting@aht.org.uk');
//$headers = array('FROM:dnatesting@aht.org.uk', 'Content-Type: text/html; charset=UTF-8');

add_filter( 'wp_mail_content_type', 'set_html_content_type' );
$sent = wp_mail( $TO, $TITLE, $message );
remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

echo $sent."\n";


function set_html_content_type() { return 'text/html'; }

?>
