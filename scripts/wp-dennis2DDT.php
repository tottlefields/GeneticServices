<?php
global $wpdb;

$sex_lookup = array('m' => 'Male', 'f' => 'Female');

$results = $wpdb->get_results("select t1.id as swabId, t2.OrderId as webshopID, t2.id as orderId, t4.id as clientID, t3.id as animalID, Quantity, test_name, Breed, RegisteredName, RegistrationNo,
	case when ddt_code is not null then ddt_code else t1.test_code end as test_code,
	Sex, DATE_FORMAT(BirthDate, '%d/%m/%Y') as BirthDate, TattooOrChip, t4.Tel as clientTel, t4.Fax as clientFax, t4.Email as clientEmail, t4.FullName as clientName,
	t4.Address as clientAddress, t4.Town as clientTown, t4.county as clientCounty, t4.Postcode as clientPostcode, t4.Country as clientCountry,
	t2.ReportFormat, t2.VetReportFormat, AgreeResearch, PetName, Colour, DATE_FORMAT(OrderDate, '%d/%m/%Y') as OrderDate, PortalID, DATE_FORMAT(returned_date, '%d/%m/%Y') as returned_date, 
	t2.ShippingName, t2.ShippingCompany, t2.ShippingAddress, t2.ShippingAddress2, t2.ShippingAddress3, t2.ShippingTown, t2.ShippingCounty, t2.ShippingPostcode, t2.ShippingCountry, 
	t1.VetID, t6.FullName as vetName, t6.Tel as vetTel, t6.Fax as vetFax, t6.Email as vetEmail, t6.Address as vetAddress, t6.Address2 as vetAddress2, t6.Address3 as vetAddress3, 
	t6.Town as vetTown, t6.County as vetCounty, t6.Postcode as vetPostcode, t6.Country as vetCountry
	from order_tests t1 inner join orders t2 on t1.order_id=t2.id 
	inner join animal t3 on t1.animal_id=t3.id 
	inner join client t4 on t2.client_id=t4.id 
	inner join test_codes t5 on t1.test_code=t5.test_code
	left outer join vet t6 on t1.VetID=t6.id 
	where returned_date>DATE_SUB(NOW(), INTERVAL 1 day) and returned_date<=NOW() and cancelled_date is NULL"
);
if(count($results > 0)){
	echo "OrderID\tClientID\tAnimalID\tQuantity\tTestDescription\tTestCode\tBloodTestRequired\tSwabRequired\tSpecies\tBreed\tRegistryName\tRegisteredName\tRegistration\tSex\tBirthDate\tTattooOrChip\tClientCode\tReportByEmail\tReportByPost\tReportByFax\tTel\tFax\tEmail\tFullName\tOrganisation\tAddress1\tTown\tCounty\tPostcode\tCountry\tAgreeToResearch\tVetPracticeName\tVetSurgeonName\tVetAddressLine1\tVetAddressLine2\tVetAddressLine3\tVetTown\tVetPostcode\tVetCounty\tVetCountry\tVetEmail\tVetFaxNumber\tVetReportByEmail\tVetReportByPost\tVetReportByFax\tPetName\tColour\tOrderDate\tSwabReceived\tSwabKitNumber\tSampleType\tSampleReceivedDate\tDeliveryFullName\tDeliveryOrganisation\tDeliveryAddress1\tDeliveryTown\tDeliveryCounty\tDeliveryPostcode\tDeliveryCountry\tVerifiedByVet\n";
	
	foreach ( $results as $row ) {
		# OrderID	ClientID	AnimalID	Quantity	TestDescription	TestCode	BloodTestRequired	SwabRequired	Species	Breed	
		# RegistryName	RegisteredName	Registration	Sex	BirthDate	TattooOrChip	
		# ClientCode	ReportByEmail	ReportByPost	ReportByFax	Tel	Fax	Email	
		# FullName	Organisation	Address1	Town	County	Postcode	Country	AgreeToResearch	
		# VetPracticeName	VetSurgeonName	VetAddressLine1	VetAddressLine2	VetAddressLine3	VetTown	VetPostcode	
		# VetCounty	VetCountry	VetEmail	VetFaxNumber	VetReportByEmail	VetReportByPost	
		# VetReportByFax	PetName	Colour	OrderDate	SwabReceived	SwabKitNumber	SampleType	SampleReceivedDate	
		# DeliveryFullName	DeliveryOrganisation	DeliveryAddress1	DeliveryTown	
		# DeliveryCounty	DeliveryPostcode	DeliveryCountry	VerifiedByVet
		
		$order_id = '';
		if (isset($row->PortalID)){ $order_id = $row->PortalID; }
		elseif (isset($row->webshopID)){ $order_id = $row->webshopID; }
		else{ $order_id = "AHT".$row->orderId; }
		
		$report_format = "TRUE\tFALSE\tFALSE";
		if ($row->ReportFormat === 'POST'){ $report_format = "FALSE\tTRUE\tFALSE"; }
		if ($row->ReportFormat === 'FAX'){ $report_format = "FLASE\tFALSE\tTRUE"; }
		
		$vet_report_format = "FALSE\tFALSE\tFALSE";
		if ($row->VetReportFormat === 'EMAIL'){ $vet_report_format = "TRUE\tFALSE\tFALSE"; }
		if ($row->VetReportFormat === 'POST'){ $vet_report_format = "FALSE\tTRUE\tFALSE"; }
		if ($row->VetReportFormat === 'FAX'){ $vet_report_format = "FLASE\tFALSE\tTRUE"; }

		$ddt_row = array();
		array_push($ddt_row, $order_id);
		array_push($ddt_row, $row->clientID);
		array_push($ddt_row, $row->animalID);
		array_push($ddt_row, $row->Quantity);
		array_push($ddt_row, $row->test_name);
		array_push($ddt_row, $row->test_code);
		array_push($ddt_row, "FALSE");
		array_push($ddt_row, "TRUE");
		array_push($ddt_row, 'Canine');
		array_push($ddt_row, $row->Breed);
		array_push($ddt_row, '');
		array_push($ddt_row, stripslashes($row->RegisteredName));
		array_push($ddt_row, $row->RegistrationNo);
		array_push($ddt_row, $sex_lookup[strtolower($row->Sex)]);
		array_push($ddt_row, $row->BirthDate);
		array_push($ddt_row, $row->TattooOrChip);
		array_push($ddt_row, "");
		array_push($ddt_row, $report_format);
		array_push($ddt_row, $row->clientTel);
		array_push($ddt_row, $row->clientFax);
		array_push($ddt_row, $row->clientEmail);
		array_push($ddt_row, $row->clientName);
		array_push($ddt_row, "");
		array_push($ddt_row, preg_replace( "/\r|\n/", ", ", $row->clientAddress));
		array_push($ddt_row, $row->clientTown);
		array_push($ddt_row, $row->clientCounty);
		array_push($ddt_row, $row->clientPostcode);
		array_push($ddt_row, $row->clientCountry);
		array_push($ddt_row, $row->AgreeResearch);
		array_push($ddt_row, "");
		array_push($ddt_row, $row->vetName);
		array_push($ddt_row, $row->vetAddress);
		array_push($ddt_row, $row->vetAddress2);
		array_push($ddt_row, $row->vetAddress3);
		array_push($ddt_row, $row->vetTown);
		array_push($ddt_row, $row->vetPostcode);
		array_push($ddt_row, $row->vetCounty);
		array_push($ddt_row, $row->vetCountry);
		array_push($ddt_row, $row->vetEmail);
		array_push($ddt_row, $row->vetFax);
		array_push($ddt_row, $vet_report_format);
		array_push($ddt_row, $row->PetName);
		array_push($ddt_row, $row->Colour);
		array_push($ddt_row, $row->OrderDate);
		array_push($ddt_row, "TRUE");
		array_push($ddt_row, $row->swabId);
		array_push($ddt_row, "");
		array_push($ddt_row, $row->returned_date);
		array_push($ddt_row, ($row->ShippingName == "") ? $row->clientName : $row->ShippingName);
		array_push($ddt_row, ($row->ShippingCompany == "") ? "" : $row->ShippingCompany);
		array_push($ddt_row, ($row->ShippingAddress == "") ? preg_replace( "/\r|\n/", ", ", $row->clientAddress) : preg_replace( "/\r|\n/", ", ", $row->ShippingAddress));
		array_push($ddt_row, ($row->ShippingTown == "") ? $row->clientTown : $row->ShippingTown);
		array_push($ddt_row, ($row->ShippingCounty == "") ? $row->clientCounty : $row->ShippingCounty);
		array_push($ddt_row, ($row->ShippingPostcode == "") ? $row->clientPostcode : $row->ShippingPostcode);
		array_push($ddt_row, ($row->ShippingCountry == "") ? $row->clientCountry : $row->ShippingCountry);
		array_push($ddt_row, ($row->VetID >0) ? "TRUE" : "");
		
		echo implode("\t", $ddt_row)."\n";
	}
}
?>
