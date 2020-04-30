jQuery(document).ready(function($) {
		$(document).on({ //no need to be inside document ready handler!
			ajaxStart: function () { $('body').addClass("loading"); },
			ajaxStop: function () { $('body').removeClass("loading"); }
		});
}); 

function countSamples(table){
	var sampleCount = table.rows('.selected').count();
	if (sampleCount == 0 ){ $("#createQPlate").prop("disabled", true); }
	else{ $("#createQPlate").prop("disabled", false); }
	if ($('.duplicateSwab:checked').length > 0){ sampleCount += ' ('+$('.duplicateSwab:checked').length+')'}
	$('#sample_count').text(sampleCount);
}

function resetSamples(tableSelector){
	var table = $(tableSelector).DataTable();
	table.rows().deselect();
	$(".selectSample").attr('disabled', false);
	$(".selectSample").removeClass('active');
	$('.duplicateSwab').bootstrapToggle('off');
	rebuildPlate();
	countSamples(table);
}

function rebuildPlate(){
	$("td.cell").empty();
	$("th.col").each(function(i){
		col = ($(this).attr('id')).replace('col', '');
		$("#col"+col).text(col);
	});
	$("th.row").each(function(i){
		row = ($(this).attr('id')).replace('row', '');
		$("#row"+row).text(row);
	});
	plateJson = {'cols': [], 'wells' : {}, 'groups' : []};
}

function updatePlate(testCode, testGroup, data){

	var lastCol = 0;
	var colCount = plateJson.cols.length;
	var wellCount = 0;
	var rowLetters = ['H','G','F','E','D','C','B','A'];
	var plateType = 'taqman';

	// make sure genotyping plates are in blocks of 16 for each grouping
	if (testGroup.charAt(0) == 'G'){
		plateType = 'genotype';
		if(colCount%2 != 0){	// we are on an odd column... need to check previous group
			if(testGroup != plateJson.groups[plateJson.cols.length-1]){
				colCount++;
				plateJson.cols.push('empty');
				plateJson.groups.push(testGroup);
				wellCount = 0;
			}
		}
	}

	for (var i=0; i<data.length; i++){
		rowData = data[i];

		plate = rowData[11];
		well = rowData[10];
		letter = well.substring(0,1);
		num = well.substring(1);

		if (lastCol != num){
			colCount++;
			colID = 'col'+colCount.toString();
			//plateJson.cols.push({ [colID] : plate+':'+num});
			plateJson.cols.push(plate+':'+num);
			$("#"+colID).text(plate+'/'+num);
			plateJson.groups.push(testGroup);
			wellCount = 0;
		}
		lastCol = num;

		newWell = rowLetters[wellCount]+colCount.toString();

		//var well = letter+colCount.toString();
		$("#"+newWell).text(rowData[7]);
		plateJson.wells[newWell] = rowData[0];
		wellCount++;
	}

	controls = testControls[testCode].split(':');

	if((8-wellCount) < controls.length){
		colCount++;
		//plateJson.cols.push({ ['col'+colCount] : testCode+'_controls'});
		plateJson.cols.push(testCode+'_controls');
		plateJson.groups.push(testGroup);
		wellCount = 0;
	}

	for (var i=0; i<controls.length; i++){
		var well = String.fromCharCode(65 + i)+colCount.toString();
		$("#"+well).html('<small class="text-muted">'+controls[i]+"</small>");
		plateJson.wells[well] = controls[i];
		wellCount++;
	}

	if ((8-wellCount) >= 1){ 
		var well = String.fromCharCode(65 + controls.length)+colCount.toString();
		$("#"+well).html('<small class="text-muted">'+controls[(controls.length - 1)]+"</small>");
		plateJson.wells[well] = controls[(controls.length - 1)];
		wellCount++;
	}

	$("#plate_type").val(plateType);
	//$("#plateStructure").val(JSON.stringify(plateJson));
}

function createPlate(){
	//console.log(plateJson);

	var data = {
		"action" : "add_plate",
		"plate_type" : $("#plate_type").val(),
		"plate_data" : plateJson
	}

	$.ajax({
		type : "post",
		dataType : "json",
		url : DennisAjax.ajax_url,
		data : data,
		success : function(results) {
			window.location.href = results.redirect;
		}
});
}