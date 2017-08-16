jQuery(document).ready(function($) {

	$("#swab_details_form").validate({
			errorPlacement: function(error, element){
				/* Need to add this function to remove the error default placement */
			}
	});
	$('input[class="radiorequired"]').rules("add", "required");
	
	$('#swab_details_form input').on('keyup blur', function () { // fires on every keyup & blur
			if ($('#swab_details_form').valid()) {                   // checks form for validity
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
	
	$('input[name="format"]').change(function() {
		if($(this).val() == 'Email') {
			$("#vet-email").addClass("required");
			$("#owner-email").addClass("required");
			$('#vet-address').removeClass("error required");
			$('#owner-address').removeClass("error required");
			$('#vet-postcode').removeClass("error required");
			$('#owner-postcode').removeClass("error required");
			$('#owner-phone').removeClass("error required");
			$('#vet-fax').removeClass("error required");
			$('#owner-fax').removeClass("error required");
		}
		else if ($(this).val() == 'Fax') {
			$('#vet-fax').addClass("required");
			$('#owner-fax').addClass("required");
			$("#vet-email").removeClass("error required");
			$("#owner-email").removeClass("error required");
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
			$('#vet-fax').removeClass("error required");
			$('#owner-fax').removeClass("error required");						
		}
	});
	
	$('#breed-select').change(
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
	});
	
	$('#swab_details_form').submit(function(event) {
		//event.preventDefault();
		if($('input:checkbox[name="breed_tests[]"]:checked').length === 0){
			alert("You need to select one or more tests for your dog.");
			return false;
		}
		//return true;
	});
});
