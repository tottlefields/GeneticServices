<?php

$order_steps = array(
		'Order Placed',
		'Dispatched',
		'Received',
		'Processed',
		'Data Analysis',
		'Results'
);

$sexes = array('m' => 'Male', 'f', 'Female');

function debug_array($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function dateToSQL($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('d/m/Y', $date), 'Y-m-d');
}

function SQLToDate($date){
	if ($date == ""){ return ""; }
	if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)){ return date_format(DateTime::createFromFormat('Y-m-d', $date), 'd/m/Y'); }
	else { return date_format(DateTime::createFromFormat('Y-m-d H:i:s', $date), 'd/m/Y'); }
}

function countOrders($status){
	global $wpdb;
	$sql = "SELECT count(distinct orders.id) from orders inner join order_tests on orders.id=order_id where cancelled_date is null";
	switch($status){
		case 'Order Placed' : $sql .= ' AND kit_sent IS NULL'; break;
		case 'Received' : $sql .= ' AND returned_date IS NOT NULL'; break;
		case 'Processed' : $sql .= ''; break;
	}
	$count = $wpdb->get_var($sql);
	return $count;	
}

function countUnextracted(){
	global $wpdb;
	$sql = "SELECT count(distinct order_tests.id) from order_tests left outer join test_swabs on order_tests.id=test_id 
	where returned_date is not null and cancelled_date is null and extraction_plate is null and test_code<>'WP'";
	$count = $wpdb->get_var($sql);
	return $count;	
}

function countUntested(){
	global $wpdb;
	$sql = "SELECT count(DISTINCT test_id) FROM test_swabs t1 LEFT OUTER JOIN (
			SELECT id, test_id, swab_failed, plate_allocated FROM test_swabs WHERE plate_allocated=1
		) t2 USING (test_id) 
		WHERE t1.plate_allocated=0 AND extraction_plate IS NOT NULL AND  
		((t2.plate_allocated = 1 AND t2.swab_failed IS NOT NULL) OR t2.plate_allocated IS NULL) ";
	$count = $wpdb->get_var($sql);
	return $count;
}

function countUnanalysed(){
	global $wpdb;
	$sql = "SELECT count(distinct order_tests.id) from order_tests left outer join test_swab_results on order_tests.id=test_id
	where returned_date is not null and cancelled_date is null and result_entered_date is null and order_tests.test_code<>'WP'";
	$count = $wpdb->get_var($sql);
	return $count;	
}

function countUnreported(){
	global $wpdb;
	$sql = "SELECT count(distinct order_tests.id) from order_tests left outer join test_swab_results on order_tests.id=test_id 
	where returned_date is not null and cancelled_date is null and result_entered_date is not null and result_reported_date is NULL and cert_code is null and order_tests.test_code<>'WP'";
	$count = $wpdb->get_var($sql);
	return $count;	
}	

function countRepeats(){
	global $wpdb;
	$sql = "select count(*) as repeatCount from order_tests t1 inner join order_tests t2 on t1.repeat_swab=t2.id 
	where t1.repeat_swab is not null and t2.repeat_swab is null and t2.returned_date is null";
	$count = $wpdb->get_var($sql);
	return $count;	
}

function clientSearch($search_terms){
	global $wpdb;	
	$clients = array();
	
	if(count($search_terms)>0){
		$sql = "SELECT * FROM client WHERE (";
		//$last = array_pop(array_keys($search_terms));
		$where = array();
		foreach ($search_terms as $field => $term){
			if($term !== ""){
				array_push($where, "($field <> ''  AND $field = '$term')");
			}
			//$sql .= "($field <> ''  AND $field = '$term')";
			//if($field != $last) { $sql .= ' OR '; }
		}
		if (count($where) > 0){
			$sql .= implode(" AND ", $where);
			$sql .= ")";
			$clients = $wpdb->get_results($sql, OBJECT );
			//echo $wpdb->last_query."\n";
		}
	}
	return $clients;
}

function animalSearch($search_terms, $client_id=0){
	global $wpdb;	
	$animals = array();
	
	if(count($search_terms)>0){
		$sql = "SELECT *, date_format(BirthDate, \"%d/%m/%Y\") as DOB, case when Sex='f' then 'Female' else 'Male' end as sex FROM animal WHERE (";
		//$last = array_pop(array_keys($search_terms));
		$where = array();
		foreach ($search_terms as $field => $term){
			if($term !== ""){
				array_push($where, "($field <> ''  AND $field = '$term')");
			}
			//$sql .= "($field <> ''  AND $field = '$term')";
			//if($field != $last) { $sql .= ' OR '; }
		}
		if (count($where) > 0){
			$sql .= implode(" AND ", $where);
			$sql .= ")";
				if (isset($client_id) && $client_id > 0){
				$sql .= " AND client_id=$client_id";
			}
			$animals = $wpdb->get_results($sql, OBJECT );
			//echo $wpdb->last_query."\n";
		}
	}
	return $animals;
	
}

function orderSearch($search_terms){
	global $wpdb;	
	$clients = array();
	
	if(count($search_terms)>0){
//		$sql = "SELECT * FROM orders WHERE (";
		$sql = "select o.OrderID as webshop_id, o.id as ID, o.paid, OrderDate, ReportFormat, VetReportFormat, Paid, AgreeResearch, 
			client_id, FullName, Email, o.ShippingCountry, count(*) as TestCount, content 
			from orders o left outer join client on client.id=client_id left outer join order_tests on o.id=order_id WHERE (";
		//$last = array_pop(array_keys($search_terms));
		$where = array();
		foreach ($search_terms as $field => $term){
			//$sql .= "$field = '$term'";
			//if($field != $last) { $sql .= ' AND '; }
			if($term !== ""){
				if ($field == 'id'){ $field = 'o.id'; }
				array_push($where, "$field = '$term'");
			}
		}
		$sql .= implode(" AND ", $where);
		$sql .= ")  group by o.id";
		$orders = $wpdb->get_results($sql, OBJECT );
	}
	return $orders;
}

function getTestsByOrder($order_id, $pending=0){
	global $wpdb;	
	$tests = array();
	
	$sql = "select id from order_tests where order_id=".$order_id;
	if ($pending){ $sql .= ' AND kit_sent IS NULL'; }
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$test_details = getTestDetails($row[0]);
		array_push($tests, $test_details);
	}
	return $tests;	
}


function getTestsByAnimal($animal_id){
	global $wpdb;	
	$tests = array();
	
	$sql = "select id from order_tests where animal_id=".$animal_id." order by order_id desc";
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$test_details = getTestDetails($row[0]);
		array_push($tests, $test_details);
	}
	return $tests;	
}

function getTestsByClient($client_id){
	global $wpdb;	
	$tests = array();
	
	$sql = "select t.* from order_tests t inner join orders o on order_id=o.id  where client_id=".$client_id." order by order_id desc";
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$test_details = getTestDetails($row[0]);
		array_push($tests, $test_details);
	}
	return $tests;	
}

function getBasicTestDetails($swab_id){
    global $wpdb;
    
    $sql = "select * from order_tests where id=".$swab_id;
    $test_details = $wpdb->get_results($sql, OBJECT );
    
    return $test_details[0];
}

function getTestDetails($swab_id){
	global $wpdb;	
	$test_details = array();
	
	$sql = 'select case when b.breed is NOT NULL then b.breed else a.Breed end as breed, date_format(o.OrderDate, "%d/%m/%Y") as order_date,
		a.*, t.*, date(kit_sent) as date_sent, date(returned_date) as date_returned, date(cancelled_date) as date_cancelled,
		test_name, ddt_sheet, no_results, no_swabs, sub_tests, date_format(a.BirthDate, "%d/%m/%Y") as DOB, case when Sex="f" then "Female" else "Male" end as sex
		from orders o inner join order_tests t on o.id=order_id 
		left outer join animal a on animal_id=a.id 
		left outer join breed_list b on a.breed_id=b.id 
		left outer join test_codes using(test_code) 
		where (a.breed_id is NULL or b.is_primary=1) and t.id='.$swab_id;
	$test_details = $wpdb->get_results($sql, OBJECT );
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$notes = getTestNotes($swab_id);
	$swabs = getSwabDetails($swab_id);
	$analysis = getSwabAnalysis($swab_id);
	$results = getTestResults($swab_id);
	
	$test_details[0]->notes = $notes;
	$test_details[0]->note_count = count($notes);
	$test_details[0]->swabs = $swabs;
	$test_details[0]->analysis = $analysis;
	$test_details[0]->results = $results;
	
	return $test_details[0];	
}

function getSwabDetails($swab_id){
    global $wpdb;
    $results = array();
    
    $sql = 'select id, swab, extraction_plate, extraction_well, date_format(date(extraction_date), "%d/%m/%Y") as extraction_date, extracted_by, swab_failed
    from test_swabs where test_id='.$swab_id." order by swab";
    $results = $wpdb->get_results($sql, OBJECT );
    
    return $results;
}

function getSwabAnalysis($swab_id){
    global $wpdb;
    $results = array();
    
    $sql = 'select distinct test_plate, test_plate_well, result_entered_by, date_format(date(result_entered_date), "%d/%m/%Y") as result_entered_date
	from test_swab_results WHERE test_id='.$swab_id;
    $results = $wpdb->get_results($sql, OBJECT );
    
    return $results;
}

function getTestResults($swab_id){
	global $wpdb;	
	$results = array();
	
	$sql = 'select test_code, test_plate, test_plate_well, test_result, result_entered_by, date_format(date(result_entered_date), "%d/%m/%Y") as result_entered_date, 
    result_authorised_by, date_format(date(result_authorised_date), "%d/%m/%Y") as result_authorised_date, date_format(date(result_reported_date), "%d/%m/%Y") as result_reported_date, cert_code
	from test_swab_results WHERE swab_failed=0 and test_id='.$swab_id;
	$results = $wpdb->get_results($sql, OBJECT );
	
	return $results;
}

function getTestNotes($swab_id){
	global $wpdb;
	$notes = array();
	
	$sql = "select * from order_test_notes where test_id=".$swab_id." order by note_date";
	$notes = $wpdb->get_results($sql, OBJECT );

	return $notes;
}

function getPlateDetails($plate_id){
	global $wpdb;
	$test_details = array();
	
	$sql = "select *, DATE_FORMAT(created_date, '%D %M %Y') as readable_date FROM plates WHERE UPPER(test_plate)='$plate_id'";
	$plate_details = $wpdb->get_results($sql, OBJECT );
	
	if (count($plate_details) == 0){
		return null;
	}
	
	if ($plate_details[0]->plate_type == 'extraction'){
		$sql = "select distinct s.id as swab_id, t.order_id, s.test_id as test_id, t.test_code, s.swab, s.extraction_well as well 
		from test_swabs s inner join order_tests t on s.test_id=t.id where s.extraction_plate='".$plate_id."'
		order by 4,5";		
	}
	else {
		$sql = "select well_id as well, well_contents, test_code from plate_wells where test_plate='".$plate_id."'";
		$plate_details[0]->other_wells = $wpdb->get_results($sql, OBJECT);
		
		$sql = "select t.order_id, t.DDT_ID, r.test_id, r.swab_id, t.test_code, r.test_plate_well as well, 
		group_concat(r.test_code) as test_result, count(r.test_code) as multi_results
		from test_swab_results r inner join order_tests t on t.id=test_id where test_plate='".$plate_id."'
		group by order_id, DDT_ID, test_id, swab_id, t.test_code, well";
		
	}
	$wells = $wpdb->get_results($sql, OBJECT);	
	$plate_details[0]->wells = $wells;
	
	return $plate_details[0];	
}

function getNextPlate($plate_type){
	global $wpdb;	
	$new_plate = '';
	
	$last_plate = $wpdb->get_var("select distinct test_plate as plate 
	from plates where test_plate is not null and plate_type='".$plate_type."' 
	order by 1 desc limit 1");
	
	switch($plate_type){
		case 'extraction':
			$last_plate = str_replace('Q', '', $last_plate);
			$new_plate = "Q".($last_plate+1);
			break;
		case 'taqman':
			$last_plate = str_replace('TM', '', $last_plate);
			$new_plate = "TM".($last_plate+1);
			break;
		case 'genotype':
			$last_plate = str_replace('G', '', $last_plate);
			$new_plate = ($last_plate+1)."G";
			break;
	}  
	return $new_plate;
}

function getTestAnalysisDetails($test_code=null){
    global $wpdb;
    $testAnalysis_details = array();
    
    $sql = "select * from test_codes left outer join analysis_conditions using (test_type,type_group)";
    if (isset($test_code)){ $sql .= " WHERE test_code='".$test_code."'"; }
    $sql .= " ORDER BY no_swabs desc, no_results desc";
    $results = $wpdb->get_results($sql, OBJECT);
    
    foreach ($results as $r){
        $testAnalysis_details[$r->test_code] = $r;
    }
    
    return $testAnalysis_details;
}

function addNewClient($client){
	//| id | ClientID | Tel | Fax | Email | FullName | Address | Address2 | Address3 | Town | County | Postcode | Country |
	//| ShippingName | ShippingCompany | ShippingAddress | ShippingAddress2 | ShippingAddress3 | ShippingTown | ShippingCounty | ShippingPostcode | ShippingCountry |
	global $wpdb;
	
	$wpdb->insert('client', $client);
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
	
}


function addNewAnimal($animal){
	//| id   | AnimalID | ClientID | Species | Breed                  | RegisteredName             | RegistrationNo | Sex  | BirthDate | TattooOrChip    | PetName | Colour            | breed_id | client_id |
	global $wpdb;
	
	$wpdb->insert('animal', $animal);
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
	
}

function addNewOrder($order){
	//| id   | client_id | OrderID | ClientID | OrderDate  | ReportFormat | VetReportFormat | Paid | AgreeResearch |
	global $wpdb;
	
	$wpdb->insert('orders', $order);
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
}

function addOrderTest($order_test){
	//| id   | OrderID | AnimalID | TestCode | Quantity | SampleType | VetID | kit_sent | returned_date | received_by | order_id | animal_id | test_code |
	global $wpdb;

	$wpdb->insert('order_tests', $order_test);
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$insert_id = $wpdb->insert_id;

	return $insert_id;
}





//test/order statistics
function countTests($year=null, $month=0){
	global $wpdb;
	
	if(!isset($year))
		$year = date('Y');
	
	$sql = "SELECT COUNT(*) FROM order_tests t INNER JOIN orders o ON t.order_id=o.id WHERE YEAR(OrderDate)='".$year."'";
	if (isset($month) && $month > 0){
		$sql .= " AND MONTH(OrderDate)='".$month."'";
	}
	$test_count = $wpdb->get_var($sql);
	return $test_count;
}

function orderStats($year=null, $month=0){
	global $wpdb;
	
	if(!isset($year))
		$year = date('Y');
	
	$sql = "SELECT COUNT(*) FROM orders WHERE YEAR(OrderDate)='".$year."'";
	if (isset($month) && $month > 0){
		$sql .= " AND MONTH(OrderDate)='".$month."'";
	}
	$test_count = $wpdb->get_var($sql);
	return $test_count;
}

function createSwabs($test_id){
	global $wpdb;
	$wpdb->insert( 'test_swabs', array('test_id' => $test_id, 'swab' => 'A'));	
}

function drawPlate($id = "plate_table", $row_height = "50px"){

	$plate_html = '<table width="100%" class="plate_table" id="'.$id.'">';

	$letters = range('A', 'H');
	for ($r=0; $r<9; $r++){
		$plate_html .= '<tr style="height:'.$row_height.'">';
		for ($c=0; $c<13; $c++){
			$cell = $letters[$r-1].($c);
			if ($r == 0 & $c == 0){ $plate_html .= '<td style="border:0px">&nbsp;</td>'; }
			elseif($r == 0){ $plate_html .= '<th id="col'.$c.'" class="cell col">'.$c.'</th>'; }
			elseif($c == 0){ $plate_html .= '<th id="row'.$letters[$r-1].'" class="cell row">'.$letters[$r-1].'</th>'; }
			else{ $plate_html .= '<td id="'.$cell.'" width="8%" class="cell"></td>'; }
		}
		$plate_html .= '</tr>';
	}
	$plate_html .= '</table>';

	return $plate_html;
}

function getWellOrder($start='H1', $order='up-across'){
    $wells = array();    
    
    $letters = range('A', 'H');
    if (substr($start, 0, 1) == "H"){ $letters = range('H', 'A'); }
    elseif (substr($start, 0, 1) == "H"){ $letters = range('A', 'H'); }
    //debug_array($letters);
    
    if ($order == 'up-across' || $order == 'down-across'){
        for ($c=1; $c<=12; $c++){
            for ($r=0; $r<8; $r++){
                array_push($wells, $letters[$r].($c));
            }
        }        
    }
    else{    
        for ($r=0; $r<8; $r++){
            for ($c=1; $c<=12; $c++){
                $cell = $letters[$r].($c);
                array_push($wells, $cell);
            }
        }
    }
    
    for ($i=0; $i<count($wells); $i++){
        $this_well = $wells[$i];
        $next_well = (count($wells) != $i+1) ? $wells[$i+1] : null;
        $wellOrder[$this_well] = $next_well;
    }
    
    return $wellOrder;
}



?>
