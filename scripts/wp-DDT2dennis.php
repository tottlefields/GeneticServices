<?php
global $wpdb;

$ddtTestCodes = array();
$data = array();
$to_ignore = array();
$swabs_failed = array();

$results = $wpdb->get_results("select distinct ddt_sheet, test_code from test_codes WHERE ddt_sheet IS NOT NULL");
foreach ($results as $r){ foreach (explode(':', $r->ddt_sheet) as $tc){$ddtTestCodes[$tc] = $r->test_code; }}

if (($handle = fopen("/home/LANPARK/eschofield/projects/GeneticsServices/TO_IGNORE", "r")) !== FALSE) {
    while (($row = fgets($handle, 1000)) !== FALSE) { array_push($to_ignore, rtrim($row)); }
}

$line = 1;
$count = 0;

if (($handle = fopen($args[0], "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	if ($row[0] == 'Test'){ continue; }
    	$line++;
    	
    	if ($row[0] == ''){ continue; }
    	if (in_array($row[5], $to_ignore)){ reportError($line, $row, "Ignoring..."); continue; }

/*    	Array(
			[2] => Cert Sent
			[4] => Status
			[5] => Number
			[11] => SwabID
			[12] => Date of Test
			[13] => Repeated?
			[14] => Status Entered
			[15] => Authorized
			[16] => Date Authorized
			[17] => Cert Code
		)
		Array(
			[2] => 12/12/2018
			[4] => normal
			[5] => SPS2428
			[11] => 18394
			[12] => Q1871 TaqMan1592 11/12/18
			[13] => 
			[14] => ktye
			[15] => sgunn
			[16] => 12/12/2018
			[17] => AC7014229
		)*/
		
    	$swab_id = $row[11];
    	//echo $swab_id."\n";
    	if (!is_numeric($swab_id)){ reportError($line, $row, "No Swab ID"); continue; }
    	
    	$test_details = getTestDetails($swab_id);
    	if (count($test_details) != 1){ reportError($line, $row); continue; }    	
    	if (!isset($test_details->test_code)){ reportError($line, $row, "Swab ID doesn't exist in Dennis"); continue; }
    	
    	
    	//$wpdb->query("UPDATE IGNORE order_tests SET DDT_ID='$row[5]' WHERE id=$swab_id");    	
    	
    	$swabs = array();
    	
    	$q_plate_a = getQPlate($row[12]);
    	$q_plate_b = getQPlate($row[13]);    	
    	if($q_plate_a == "ERROR" || $q_plate_b == "ERROR"){ reportError($line, $row, "Multiple Q plates found"); continue; }
    	
    	$a_failed = checkFailures($row[12]);
    	$b_failed = checkFailures($row[13]);
    	if ($a_failed && $b_failed){ array_push($swabs_failed, $swab_id); }    	
    	if($q_plate_a != ''){ array_push($swabs, array('q_plate' => $q_plate_a, 'swab' => 'A', 'swab_failed' => $a_failed)); }
    	if($q_plate_b != ''){ array_push($swabs, array('q_plate' => $q_plate_b, 'swab' => 'B', 'swab_failed' => $b_failed)); }
    	
    	$g_plate_a = getGPlate($row[12]);
    	$g_plate_b = getGPlate($row[13]);
    	if($g_plate_a != ''){ $swabs[0]['test_plate'] = $g_plate_a; }
    	if($g_plate_b != ''){ $swabs[1]['test_plate'] = $g_plate_b; }
    	
    	$t_plate_a = getTPlate($row[12]);
    	$t_plate_b = getTPlate($row[13]);
    	if($t_plate_a != ''){ $swabs[0]['test_plate'] = $t_plate_a; }
    	if($t_plate_b != ''){ $swabs[1]['test_plate'] = $t_plate_b; }
    	    	
    	$results = array(
    		'test_result' => $row[4],
    		'result_entered_by' => $row[14],
    		'result_authorised_by' => $row[15],
    		'result_authorised_date' => DateToSQL($row[16]),
    		'cert_code' => $row[17],
    		'result_reported_date' => DateToSQL($row[2]),
    	);
    	
    	
    	if (isset($data[$swab_id]) && $test_details->no_swabs == 1 && $test_details->no_results >= 2){
    		//echo "Got here.... swab already seen, only 1 swab needed, but 2 sets of results required\n";
    		$data[$swab_id][$row[5]] = array();
    		foreach((explode(':', $test_details->sub_tests)) as $test_code){
				if ($test_code == $ddtTestCodes[$row[0]]){
					$data[$swab_id][$row[5]]['test_code'] = $test_code;
					$swabs = array();
					$count++;
					//echo "Row #$line OK\n";
					break;
				}
			}
			if (!isset($data[$swab_id][$row[5]]['test_code']) && isset($ddtTestCodes[$row[0]])){
				$data[$swab_id][$row[5]]['test_code'] = $ddtTestCodes[$row[0]]; 
				$count++;
				//echo "Row #$line OK\n";
			}
			elseif(!isset($data[$swab_id][$row[5]]['test_code'])) { echo $row[0]."\n"; exit; }
    	}
    	elseif(isset($data[$swab_id]) && $test_details->no_swabs == 1){
			if ($row[0] == $test_details->ddt_sheet){ 
				if (($test_details->test_code == 'CC' && $row[0] == 'CURLY COAT') || ($test_details->test_code == 'EF' && $row[0] == 'EPISODIC FALLING')){
					//echo $test_details->test_code."/".$row[0]." - single CC/EF test requested but got results for the other ($swab_id)\n";
					$data[$swab_id][$row[5]] = array();
					$data[$swab_id][$row[5]]['test_code'] = $ddtTestCodes[$row[0]];
					$count++;
					//echo "Row #$line OK\n";
				}
				elseif (($test_details->test_code == 'LHA' && $row[0] == 'L2HGA') || ($test_details->test_code == 'HC' && $row[0] == 'HC-SBT')){
					//echo $test_details->test_code."/".$row[0]." - single LHA/HC test requested but got results for the other ($swab_id) = SBT_ALL\n";
					$data[$swab_id][$row[5]] = array();
					$data[$swab_id][$row[5]]['test_code'] = $ddtTestCodes[$row[0]];
					$count++;
					//echo "Row #$line OK\n";
				}
				elseif(in_array($swab_id, $swabs_failed)){
					# C swab generated!
					if (count(array_keys($data[$swab_id])) > 1){
						echo "not sure what to do here.....\n";
						continue;
					}
					
					$ddt = (array_keys($data[$swab_id]))[0];
					if (count($data[$swab_id][$ddt]['swabs']) == 2){
						foreach ($swabs as $extras){
							$new_swab = chr(ord($extras['swab']) + count($data[$swab_id][$ddt]['swabs']));
							$extras['swab'] = $new_swab;
							$extras['other_ddt'] = $row[5];
							array_push($data[$swab_id][$ddt]['swabs'], $extras);
						}
						$swabs = array();
						$count++;
						//echo "Row #$line OK\n";
					}
					else{
						echo "not sure what to do here.....\n";
						continue;
					}
				}
				else {
					echo "Second version of swab seen but only 1 needed... what!?\n";
				}
			}
    	}
    	elseif(isset($data[$swab_id])){
    		echo "Row #$line... something weird?\n";
    	}
		elseif(!isset($data[$swab_id])){
			
			if ($test_details->no_results > 1){
				foreach((explode(':', $test_details->sub_tests)) as $test_code){
					if ($test_code == $row[0]){
						$data[$swab_id] = array();
						$data[$swab_id][$row[5]] = array();
						$data[$swab_id][$row[5]]['test_code'] = $test_code;
						$count++;
						//echo "Row #$line OK\n";
						break;
					}
				}
				if (!isset($data[$swab_id][$row[5]]['test_code']) && isset($ddtTestCodes[$row[0]])){
					$data[$swab_id] = array();
					$data[$swab_id][$row[5]] = array();
					$data[$swab_id][$row[5]]['test_code'] = $ddtTestCodes[$row[0]]; 
					$count++;
					//echo "Row #$line OK\n";
				}
				else { echo $row[0]."\n"; exit; }
			}
			else {				
				if ($ddtTestCodes[$row[0]] == $test_details->test_code){					
					$data[$swab_id] = array();
					$data[$swab_id][$row[5]] = array();
					$data[$swab_id][$row[5]]['test_code'] = $test_details->test_code;
					$count++;	
					//echo "Row #$line OK\n";				
				}
				elseif ($row[0] != $test_details->ddt_sheet){ 
					if (($test_details->test_code == 'EF' && $row[0] == 'CURLY COAT') || ($test_details->test_code == 'CC' && $row[0] == 'EPISODIC FALLING')){
						//echo $test_details->test_code."/".$row[0]." - single CC/EF test requested but got results for the other ($swab_id) = CKCS_ALL\n";
						$data[$swab_id] = array();
						$data[$swab_id][$row[5]] = array();
						$data[$swab_id][$row[5]]['test_code'] = $ddtTestCodes[$row[0]];
						$count++;
						//echo "Row #$line OK\n";
					}
					elseif (($test_details->test_code == 'HC' && $row[0] == 'L2HGA') || ($test_details->test_code == 'LHA' && $row[0] == 'HC-SBT')){
						//echo $test_details->test_code."/".$row[0]." - single LHA/HC test requested but got results for the other ($swab_id) = SBT_ALL\n";
						$data[$swab_id] = array();
						$data[$swab_id][$row[5]] = array();
						$data[$swab_id][$row[5]]['test_code'] = $ddtTestCodes[$row[0]];
						$count++;
						//echo "Row #$line OK\n";
					}
					else{
						reportError($line, $row, "Test codes don't match (".$row[0]."/".$test_details->ddt_sheet.")");
						continue;
					}
				}
			}
		}
		else{
    		echo "Row #$line... something weird?\n";
    	}
    	
    	if (count($swabs) > 0){ $data[$swab_id][$row[5]]['swabs'] = $swabs; }
    	$data[$swab_id][$row[5]]['results'] = $results;
    	
    }
    fclose($handle);
}

foreach ($data as $swab_id => $ddt_details){
	
	foreach ($ddt_details as $ddt => $swab_details){
		if (isset($swab_details['swabs'])){
			$updated_swabs = array();
			foreach($swab_details['swabs'] as $swab){
				$wpdb->query("INSERT INTO test_swabs (test_id, swab, extraction_plate, swab_failed) VALUES ($swab_id, '".$swab['swab']."', \"".$swab['test_plate']."\", ".$swab['swab_failed'].") ON DUPLICATE KEY UPDATE extraction_plate=\"".$swab['test_plate']."\", swab_failed=".$swab['swab_failed']);
				$swabs = $wpdb->get_results("select * from test_swabs where test_id=$swab_id AND swab='".$swab['swab']."'");
				$swab['dennis_id'] = $swabs[0]->id;
				array_push($updated_swabs, $swab);
				if (!$swab['swab_failed'] ){
					$sql = "INSERT IGNORE INTO test_swab_results (test_id, swab_id, DDT_ID, test_code) VALUES ($swab_id, ".$swab['dennis_id'].", \"".$ddt."\", '".$swab_details['test_code']."')";
					echo $sql.";\n";
				}
			}
			$data[$swab_id][$ddt]['swabs'] = $updated_swabs;
		}
	}
	
	break;	
}

//$test = array_slice($data, 0 ,4);
echo "\n".$count."\n";
//echo json_encode($test);
//echo json_encode($data);
echo "\n\n";



function reportError($line, $row, $error=''){
	$swab_id = $row[11];
	echo "ERROR with row #$line";
	if ($error !='') { echo " - $error"; }
	if ($swab_id != ''){ echo " (swab=$swab_id)"; }
	echo "\n"; 
}

function getQPlate($cell){
	$plate = '';
	if (preg_match('/Q\d+/', $cell)){
		preg_match_all('/Q\d+/', $cell, $matches);
		if (count($matches[0]) > 1 ){ return 'ERROR'; }
		$plate = $matches[0][0];
			//$wpdb->query( "UPDATE IGNORE test_swabs SET extraction_plate='".$matches[0][0]."' WHERE test_id=$swab_id AND swab='A'");
	}
	return $plate;
}

function getGPlate($cell){
	$plate = '';
	if (preg_match('/\d+G/', $cell)){
		preg_match_all('/\d+G\S*/', $cell, $plates);
		if (count($plates[0]) > 1 ){ return implode(':', $plates[0]); }
		$plate = $plates[0][0];
			//$wpdb->query( "UPDATE IGNORE test_swabs SET extraction_plate='".$matches[0][0]."' WHERE test_id=$swab_id AND swab='A'");
	}
	return $plate;
}

function getTPlate($cell){
	$plate = '';
	if (preg_match('/TaqMan\d+/', $cell)){
		preg_match_all('/TaqMan\d+/', $cell, $plates);
		if (count($plates[0]) > 1 ){ return implode(':', $plates[0]); }
		$plate = $plates[0][0];
			//$wpdb->query( "UPDATE IGNORE test_swabs SET extraction_plate='".$matches[0][0]."' WHERE test_id=$swab_id AND swab='A'");
	}
	return $plate;
}

function checkFailures($cell){
	$failed = 0;
	preg_match('/code (\d)/', $cell, $matches);
	if (count($matches) > 0){ /* $wpdb->query( "UPDATE IGNORE test_swabs SET swab_failed='".$matches[0]."' WHERE test_id=$swab_id AND swab='A'"); */ $failed = 1; }
	return $failed;
}

?>
