jQuery(document).ready(function($) {
	
	$.each(countryList, function(key, value) {
		$("#owner-country").append($('<option>').text(value).attr('value', key));
	});
	
	$("#noDogs").keyup(function() {
		if ($("#noDogs").val() == 0)
			$("#dogsTable").hide();
		else if ($("#noDogs").val() > 0) {
			$("#dogsTable > thead").html("");
			$("#dogsTable > tbody").html("");
			$("#dogsTable").show();
			var noDogs = $("#noDogs").val();
			
			var headerRowContent = '<tr><th style="width:30px;">&nbsp;</th>';
			headerRowContent += '<th style="width:10%;">Breed</th>';
			headerRowContent += '<th style="width:15%;">Registered Name</th>';
			headerRowContent += '<th style="width:10%;">Registration No.</th>';
			headerRowContent += '<th style="width:10%;">Pet Name</th>';
			headerRowContent += '<th style="width:10%;">Birth Date</th>';
			headerRowContent += '<th style="width:5%;">Sex</th>';
			headerRowContent += '<th style="width:10%;">Colour</th>';
			headerRowContent += '<th style="width:10%;">Microchip</th>';
			headerRowContent += '<th>Tests Available</th></tr>';
			$("#dogsTable thead").append(headerRowContent);
			
			for (i = 1; i <= noDogs; i++) {
				var newRowContent = '<tr><td style="vertical-align:top">'+ i + '</td>';
				newRowContent += '<td><select id="breed-select_'+i+'" class="form-control required input-sm breed-select" name="breed_'+i+'"><option value="">Select Breed...</option>';
				for (breedID in allBreeds){
					newRowContent += '<option value="'+breedID+'">'+ allBreeds[breedID]+ '</option>';
				}
				newRowContent += '</select></td>';
				newRowContent += '<td><input type="text" class="form-control input-sm" placeholder="Registered Name" value="" name="registered-name_'+i+'" id="registered-name_'+i+'"/></td>';
				newRowContent += '<td><input type="text" class="form-control input-sm" style="text-transform:uppercase" placeholder="Registration No." value="" name="registration-number_'+i+'" id="registration-number_'+i+'"/></td>';
				newRowContent += '<td><input type="text" class="form-control input-sm required" placeholder="Pet Name" value="" name="pet-name_'+i+'" id="pet-name_'+i+'"/></td>';
				newRowContent += '<td><input type="text" class="form-control input-sm datepicker-me num" value="" name="birth-date_'+i+'" id="birth-date_'+i+'" autocomplete="off" placeholder="dd/mm/yyyy" /></td>';
				newRowContent += '<td><select id="sex_'+i+'" class="form-control input-sm" name="sex_'+i+'"><option value="">Unknown</option><option value="Male">Male</option><option value="Female">Female</option></select></td>';
				newRowContent += '<td><input type="text" class="form-control input-sm" placeholder="Colour" value="" name="colour_'+i+'" id="colour_'+i+'"/></td>';
				newRowContent += '<td><input type="text" class="form-control input-sm" placeholder="Microchip" value="" name="microchip_'+i+'" id="microchip_'+i+'"/></td>';
				newRowContent += '<td id="available_tests_'+i+'"></td></tr>';
				$("#dogsTable tbody").append(newRowContent);
			}

			$('.datepicker-me').datepicker("destroy");
			$('.datepicker-me').datepicker({ format : 'dd/mm/yyyy' });

			$('.breed-select').change(function(event) {
				selectedBreed = $('#'+$(this).attr("id")+' option:selected').val();
				var a = $(this).attr("id").split("_");
				if (selectedBreed !== "") {
					var html = '';
					var testAll = breedTests['all'];
					for ( var key in testAll) {
						html += '<div class="checkbox input-sm"><label><input type="checkbox" class="test_checkbox" name="breed_tests_'+a[1]+'[]" value="'+key+'">'+ testAll[key]+ '</label></div>';
					}
					var testList = breedTests[selectedBreed];
					if (testList && Object.keys(testList).length > 0) {
						for ( var key in testList) {
							html += '<div class="checkbox input-sm"><label><input type="checkbox" class="test_checkbox" name="breed_tests_'+a[1]+'[]" value="'+key+'">'+ testList[key]+ '</label></div>';
						}
					}
					$('#available_tests_'+a[1]).html(html);
				}

				$('.test_checkbox').change(function(e) {
					if ($('#swab_details_form').valid()) { $('#form_submission_but').prop('disabled', false); }
					else { $('#form_submission_but').prop('disabled', 'disabled'); }
				});
			});
		}
	});

	$("#swab_details_form").validate({
			errorPlacement: function(error, element){
				/* Need to add this function to remove the error default placement */
			}
	});
	$('input[class="radiorequired"]').rules("add", "required");
	
	$('#swab_details_form input').on('keyup blur', function () { // fires on every keyup & blur
			if ($("#noDogs").val() > 0 && $('#swab_details_form').valid()) { // checks form for validity
				$('#form_submission_but').prop('disabled', false);        // enables button
			} else {
				$('#form_submission_but').prop('disabled', 'disabled');   // disables button
			}
	});
	
	var vetDetails;
	try{ vetDetails = JSON.parse(localStorage.getItem('vetDetails')) || []; }
	catch (err) { vetDetails = []; }
	for (var i = 0; i < vetDetails.length; i++) {
		var vet = vetDetails[i];
		$('#vet-select').append($('<option>', {value:vet.id, text:vet.name}));
	}

	
	$('#vet-select').change(function(e) {
			if ($(this).val() == 0){
				showVetModal();
			}
	});
	
	$('#vet-verified').change(function() {
			if ( $(this).prop('checked')){
				if (vetDetails.length == 0){
					showVetModal();
				}else{
					$('#vet-select option:last').prop('selected', true);
				}
				$('#vet-select-div').show();
			}
			else{
				$('#vet-select-div').hide();
			}						
	});
	
	$('#but-add-vet').click(function(){
			addVetDetails();
	});
	
	$('#swab_details_form input').on('keyup blur',function() { // fires on every keyup & blur
		if ($("#noDogs").val() > 0 && $('#swab_details_form').valid()) { // checks form for validity
			$('#form_submission_but').prop('disabled', false); // enables button
		} else {
			$('#form_submission_but').prop('disabled', 'disabled'); // disables button
		}
	});
	
	$('input[name="format"]').change(function() {
		if($(this).val() == 'Email') {
			$("#vet-email").addClass("required");
			$("#owner-email").addClass("required");
			$('#vet-address').removeClass("error required");
			$('#owner-address').removeClass("error required");
			$('#vet-postcode').removeClass("error required");
			$('#owner-postcode').removeClass("error required");
			$('#owner-phone').removeClass("error required");
		}
		else if ($(this).val() == 'Post') {
			$('#vet-address').addClass("required");
			$('#owner-address').addClass("required");
			$('#vet-postcode').addClass("required");
			$('#owner-postcode').addClass("required");
			$('#owner-phone').addClass("required");
			$("#vet-email").removeClass("error required");
			$("#owner-email").removeClass("error required");				
		}
	});
	
/*	$('#breed-select').change(
			function(event) {
				selectedBreed = $('#breed-select option:selected').val();
				if (selectedBreed !== "") {
					var html = '';
					var data = {
							'action' : 'breed_tests',
							'breedId' : selectedBreed
					};

					jQuery.ajax({
						type : "post",
						dataType : "json",
						url : DennisAjax.ajax_url,
						data : data,
						success : function(data) {
							for (var i in data.tests){
								var test = data.tests[i];
								html += '<div class="checkbox"><label><input type="checkbox" name="breed_tests[]" value="'+test.test_code+'">'+test.test_name+'</label></div>'
							}
							$('#available_tests').html(html);
						}
					});
				} else {
					$('#available_tests')
					.html(
					'<p>Please first select your dog breed from the dropdown list in DOG DETAILS section to view the list of tests available.</p>');
				}
	});*/
	
	$('#swab_details_form').submit(function(event) {
		//event.preventDefault();
		if($('input:checkbox[class="test_checkbox"]:checked').length === 0){
			alert("You need to select one or more tests for your dog.");
			return false;
		}
		//return true;
	});
});
