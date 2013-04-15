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

			  var html = "<div id=\"back\">";
			  for(var i in back_data) {
				  html += "<span id=\"temp_sensor_back_" + i + "\" class=\"temp_sensor_back\">" + back_data[i] + "&degC</span>";
			  }
			  html += "</div><div id=\"front\">";
			  for(var i in front_data) {
				  html += "<span id=\"temp_sensor_front_" + i + "\" class=\"temp_sensor_front\">" + front_data[i] + "&degC</span>";
			  }
			  html += "</div>";
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
				  
		  function received(amountData) {
				var amount  = $.parseJSON(amountData);
			  $('#antall_saker #mottatt #antall').html(amount.Received);
		  }

	function getGraphStats(graphStats) {
		var barArray = new Array();
		var stats = $.parseJSON(graphStats);
		/*for(var key in stats) {
		if(stats.hasOwnProperty(key)) {
		barArray.push(new Array(stats[key], key, '#f3f3f3'));
		}*/
		$('#graph').highcharts({
      	
		chart: {
        	renderTo: 'graph',
			width: 400,
			height: 175,
			animation: false,
   	   defaultSeriesType: 'column',
			marginTop: 12
      },
      	
		title: {
        	text: null
      },
     		
		xAxis: {
       	categories: ['Closed(today)', 'Received(today)', 'Closed(overall)', 'Active(overall)']
      },

      yAxis: {
        	title: {
           	text: null 
					  },
				endOnTick: false,
				max: parseInt(stats.Open)
      },
      
		legend: {
			enabled: false
		},

		labels: {
			rotation: -45,						 
			align: 'right'	
	
				  },
			
		series: [
			{
        		name: 'Amount',
        		data: [{ y:stats.Closed,
							color: '#32CD32'},
						 { y:stats.Received,
                     color: '#FF0000'},
						 { y:stats.ClosedAll,
                     color: '#32CD32'},
						 { y:stats.Open,
                     color: '#FF8F00'}
						],
				}
			],
		
		plotOptions: {
        	column: {
           	dataLabels: {
            	enabled: true
            },
				animation: false, 
				pointWidth: 30,        
		      borderWidth: 1
         }
       },
			
		tooltip: {
			enabled: false
		},
		credits: {
			enabled: false
		}
   });

	/*$('#graph').jqBarGraph({ data: barArray,
									 animate: false,
									 height: 100,
									 width: 150,
									 barSpace: 50,
								    legendWidth: 10

				 });*/ 
}

function load() {
	ajax("Temperature", temp, 'netbotz.php');
	ajax("Humidity", humid, 'netbotz.php');
//	ajax("Closed", closed, 'footprints.php');
//	ajax("Received", received, 'footprints.php');	
	ajax("Graph", getGraphStats, 'footprints.php');
	$("#nagioscontainer").load("nagdash.php");
}

$(document).ready(function() {
	$.ajaxSetup({ cache: false });
	load();
	$("#nagioscontainer").load("nagdash.php");
	setInterval(function() {
		load();
	}, 20000);
});
