function selectProvince() {
	$.ajax( {
		type : "post",
		url : "/index.php/Core/Region/getDataByParentId",
		data : {},
		success : function(msg) {
			$("#province").html("<option value=''>省</option>");
			for ( var i = 0; i < msg.length; i++) {
				$("#province").append("<option data-value=" + msg[i].id + " value=" + msg[i].region_name + ">" + msg[i].region_name+ "</option>");
					
			}
			if(typeof(isEdit) != "undefined" && isEdit==1){
				$('#province').val(province);
			}
			selectCity();
		}
	})
};
function selectCity() {
	$("#city").html("");
	
	if($('#province').val()==''){
		$("#city").html("<option value=''>市</option>");
		selectCountry();
		return;
	}

	$.ajax( {
		type : "post",
		url : "/index.php/Core/Region/getDataByParentId",
		data : {
			"parent_id" : $('#province').find("option:selected").attr('data-value')
		},
		success : function(msg) {
			$("#city").html("<option value=''>市</option>");
			for ( var i = 0; i < msg.length; i++) {
				$("#city").append("<option data-value=" + msg[i].id + " value=" + msg[i].region_name + ">" + msg[i].region_name+ "</option>");
			}
			if(typeof(isEdit) != "undefined" && isEdit==1){
				$('#city').val(city);
			}
			selectCountry();
		}
	})
};
function selectCountry() {
	$("#district").html("");
	if($('#city').val()==''){
		$("#district").html("<option value=''>县/区</option>");
		return;
	}
	$.ajax( {
		type : "post",
		url : "/index.php/Core/Region/getDataByParentId",
		data : {
			"parent_id" : $('#city').find("option:selected").attr('data-value')
		},
		success : function(msg) {
			$("#district").html("<option value=''>县/区</option>");
			for ( var i = 0; i < msg.length; i++) {
				$("#district").append("<option data-value=" + msg[i].id + " value=" + msg[i].region_name + ">" + msg[i].region_name + "</option>");
			}
			if(typeof(isEdit) != "undefined" && isEdit==1){
				$('#district').val(district);
				isEdit=0;
			}
		}
	})
};
$(function() {
	selectProvince();
	$('#province').bind("change", selectCity);
	$('#city').bind("change", selectCountry);
});