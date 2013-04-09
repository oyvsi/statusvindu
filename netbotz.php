<?php

require_once 'config.php';

function check_sensor($query, $type, $dummydata) {
	if($dummydata) {
	 return array(array('name' => 1, 'data' => 12), array('name' => 2, 'data' => 12), array('name' => 4, 'data' => 12), array('name' => 5, 'data' => 12)); 
	}

	$result = json_decode(file_get_contents($query));
	$result_obj = $result->result[0];
	// 'Sensor MM:1 Temperature'=15;;;60;0; 'Sensor MM:2 Temperature'=17;;;60;0; 'Sensor MM:5 Temperature'=22;;;60;0; 'Sensor MM:4 Temperature'=22;;;60;0;
	// 'Sensor MM:1 Humidity'=15%;;;99;0; 'Sensor MM:2 Humidity'=14%;;;99;0; 'Sensor MM:5 Humidity'=15%;;;99;0; 'Sensor MM:4 Humidity'=15%;;;99;0;
	preg_match_all('/Sensor MM:(\d+) ' . $type . '\'=(\d+)|{%};/', $result_obj->SERVICE_PERFDATA, $matches);
	$result = array();

	for($i = 0; $i < count($matches[1]); $i++) {
		$sensor = array();
		$sensor['name'] = $matches[1][$i];
		$sensor['data'] = $matches[2][$i];
		
		array_push($result, $sensor);
	}
	return $result;
}

if(isset($_GET['type'])) {
	$query = $type = null;
	if($_GET['type'] == 'Temperature') {
			$query = 'http://' . $APIhost . '/icinga-web/web/api/service/filter[(SERVICE_NAME%7C=%7CCheck%20Netbotz%20Temp)]/countColumn=SERVICE_ID/authkey=' . $APIkey . '/json';
			echo json_encode(check_sensor($query, 'Temperature', $dummydata), JSON_NUMERIC_CHECK);
	} 
	elseif ($_GET['type'] == 'Humidity') {
		$query = 'http://' . $APIhost . '/icinga-web/web/api/service/filter[(SERVICE_NAME%7C=%7CCheck%20Netbotz%20Humid)]/countColumn=SERVICE_ID/authkey=' . $APIkey . '/json';
		echo json_encode(check_sensor($query, 'Humidity', $dummydata), JSON_NUMERIC_CHECK);
	}
}
