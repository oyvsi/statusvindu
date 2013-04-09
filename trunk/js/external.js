function ajax(arg, func, url) {
	$.ajax({ 
        		type: 'GET',
                url: url,
                data: { type: arg },
                success: func
	});
}

function temp(sensor_data) {
	var front_layout = [2, 1];
	var back_layout = [5, 4];
	var front_data = new Array();
	var back_data = new Array();
	$.each($.parseJSON(sensor_data), function(index, sensor) {
		if(front_layout.indexOf(sensor.name) > -1) {
			front_data[front_layout.indexOf(sensor.name)] = sensor.data;
		}
		else if(back_layout.indexOf(sensor.name) > -1) {
			back_data[back_layout.indexOf(sensor.name)] = sensor.data;
		}
	});

	var html = "<table><tr>";
	for(var i in back_data) {
		html += "<td>" + back_data[i] + "&degC</td>";
	}
	html += "</tr><tr>"
	for(var i in front_data) {
		html += "<td>" + front_data[i] + "&degC</td>";
	}
	
	$('#serverroom_climate #temperature #data').html(html);
}

function humid(sensor_data) {
	var total = 0;
	var sensors = $.parseJSON(sensor_data);
	$.each(sensors, function(index, sensor) {
		total += sensor.data;
	});
	var result = Math.round(total*100/sensors.length)/100;

	$('#serverroom_climate #humidity #data').html(result + '%');
}

function closed(amountData) {
	var amount  = $.parseJSON(amountData);
    $('#antall_saker #lukket #antall').html(amount.Closed);

}
		
function recieved(amountData) {
    var amount  = $.parseJSON(amountData);
	$('#antall_saker #mottatt #antall').html(amount.Recieved);
}

function load() {
	ajax("Temperature", temp, 'netbotz.php');
	ajax("Humidity", humid, 'netbotz.php');
	ajax("Closed", closed, 'footprints.php');
	ajax("Recieved", recieved, 'footprints.php');	
	$("#nagioscontainer").load("nagdash.php");
}

$(document).ready(function() {
	$.ajaxSetup({ cache: false });
	load();
	$("#nagioscontainer").load("nagdash.php");
	setInterval(function() {
		load();
	}, 5000);
});
