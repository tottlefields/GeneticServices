<?php /* Template Name: Downloads */ ?>
<?php 

global $wpdb;

header('Content-type: text/plain; charset=utf-8');
header("Pragma: 0");
header("Expires: 0");


if (isset($wp_query->query_vars['download_type']) && isset($wp_query->query_vars[$wp_query->query_vars['download_type']])){
	$plate_q = $wp_query->query_vars[$wp_query->query_vars['download_type']];
	$plate_details = getPlateDetails($plate_q);
	
	$wells = array();
	$letters = range('A', 'H');
	$analysis_conditions = getTestAnalysisDetails();
	
	if (count($plate_details->wells) > 0){
		foreach ($plate_details->wells as $well){
			//$wells[$well->well] = array('sample' => $well->swab_id, 'test_code' => $well->test_code, 'task' => 'UNKNOWN');
			$wells[$well->well] = array('sample' => $well->DDT_ID, 'test_code' => $well->test_code, 'task' => 'UNKNOWN');
		}
	}
	if (isset($plate_details->other_wells) && count($plate_details->other_wells) > 0){
		foreach ($plate_details->other_wells as $well){	
			$task = 'NTC';			
			switch(substr($well->well_contents, -2)){
				case '_A':
					$task = 'PC_ALLELE_2';
					break;
				case '_C':
					$task = 'PC_ALLELE_BOTH';
					break;
				case '_N':
					$task = 'PC_ALLELE_1';
			}					
			$wells[$well->well] = array('sample' => $well->well_contents, 'test_code' => $well->test_code, 'task' => $task);
		}
	}
	
	
	
	
	if ($plate_details->plate_type == 'genotype'){
		$filename = urlencode( $plate_q.'.plt' );
		header("Content-Disposition: attachment; filename=".$filename);	
		
		echo "Container Name\tDescription\tContainerType\tAppType Owner\tOperator\n";
		echo $plate_q."\t\t96-Well Regular\t".$plate_details->created_by."\t".$plate_details->created_by."\n";
		echo "AppServer\tAppInstance\n";
		echo "GeneMapper\tGeneMapper_Generic_Instance\n";
		echo "Well\tSample Name\tComment\tPriority\tSize Standard\tSnp Set\tUser-Defined 3\tUser-Defined 2\tUser-Defined 1\tPanel\tStudy\tSample Type\tAnalysis Method\tResults Group 1\tInstrument Protocol 1\n";
		
		$export = array();
		$previous_protocol = '';
		$col_protocols = array();
		
		for ($c=1; $c<13; $c++){
			$col_protocols[$c] = '';
			
			for ($r=1; $r<=count($letters); $r++){
				$cell = $letters[$r-1].($c);
				$sample = "x";
				
				if (isset($wells[$cell])){ $sample = $wells[$cell]['sample']; }
				
				if (isset($wells[$cell])){
					$protocol = $analysis_conditions[$wells[$cell]['test_code']]->ins_protocol;
					$previous_protocol = $analysis_conditions[$wells[$cell]['test_code']]->ins_protocol;
					if ($col_protocols[$c] == '') { $col_protocols[$c] = $protocol; }
				}
				elseif ($r == 1){
					$protocol = "PROBLEM";
					$previous_protocol = "PROBLEM";
				}
				else { $protocol = $previous_protocol; }
				
				array_push($export, array(
						'cell' => $cell,
						'sample' => $sample,
						'protocol' => $protocol,
						'column' => $c
				));
			}
		}
		
		
		foreach ($export as $row){
			echo $row['cell']."\t".$row['sample']."\t100\t\t\t\t\t\t\t\t\t\tData2\t";
			
			if ($row['protocol'] == "PROBLEM"){
				if (isset($col_protocols[$row['column']]) && $col_protocols[$row['column']] != ''){
					echo $col_protocols[$row['column']]."\n";
				}
				else {	// totally empty column, need to pull from previous/next col, remembering the rull of 16...
					if ($row['column']%2 == 0){
						echo $col_protocols[$row['column']-1]."\n";
					}
					else{
						echo $col_protocols[$row['column']+1]."\n";
					}
				}
			} else { echo $row['protocol']."\n"; }
		}
	}
	
	elseif ($plate_details->plate_type == 'taqman'){
		$filename = urlencode( $plate_q.'.txt' );
		header("Content-Disposition: attachment; filename=".$filename);
		
		echo "* Block Type = 96well\n";
		echo "* Chemistry = TAQMAN\n";
		echo "* Experiment File Name = C:\Applied Biosystems\StepOne Software v2.1\experiments\Plate Record Setup.eds\n";
		echo "* Experiment Run End Time = Not Started\n";
		echo "* Instrument Type = steponeplus\n";
		echo "* Passive Reference = ROX\n";
		echo "[Sample Setup]\n";
		echo "Well\tSample Name\tSample Color\tSNP Assay Name\tSNP Assay Color\tTask\tAllele1 Name\tAllele1 Color\tAllele1 Reporter\tAllele1 Quencher\tAllele2 Name\tAllele2 Color\tAllele2 Reporter\tAllele2 Quencher\tComments\n";
		
		
		
		for ($r=1; $r<=count($letters); $r++){
			for ($c=1; $c<13; $c++){
				$cell = $letters[$r-1].($c);
				
				if (isset($wells[$cell])){ 
					$sample = $wells[$cell]['sample']; 
					$test_code = $wells[$cell]['test_code'];
					$task = $wells[$cell]['task'];
					$a1_dye = $analysis_conditions[$wells[$cell]['test_code']]->a1_dye;
					$a2_dye = $analysis_conditions[$wells[$cell]['test_code']]->a2_dye;
					
					echo $cell."\t".$sample."\tRGB(132,193,241)\t".$test_code."\tRGB(139,189,249)\t".$task."\tAllele 1\tRGB(208,243,98)\t".$a1_dye."\tNFQ-MGV\tAllele 2\tRGB(244,165,230)\t".$a2_dye."\tNFQ-MGB\n";
				}
				else{
					echo $cell."\t\t\t\t\t\t\t\t\t\t\t\t\t\t\n";
				}			
				
			}
		}		
		
	}
	
	//echo "\n\n===========\n\n";
	
	//print_r($plate_details);
	
	
	
	exit();	
}

echo $wp_query->query_vars['download_type'];
exit();	


?>