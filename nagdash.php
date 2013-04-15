<?php
require_once 'config.php';
error_reporting(!E_NOTICE);
//require_once 'timeago.php';

$nagios_host_status = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE");
$nagios_service_status = array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN");
$nagios_host_status_colour = array(0 => "status_green", 1 => "status_red", 2 => "status_yellow");
$nagios_service_status_colour = array(0 => "status_green", 1 => "status_yellow", 2 => "status_red", 3 => "status_grey");

$nagios_toggle_status = array(0 => "disabled", 1 => "enabled");

$hostQuery = "filter[OR(HOST_CURRENT_STATE|=|1;HOST_CURRENT_STATE|=|2)]/columns[HOST_CURRENT_CHECK_ATTEMPT|HOST_OUTPUT|HOST_NAME|HOST_LAST_STATE_CHANGE|HOST_CURRENT_STATE|HOST_PROBLEM_HAS_BEEN_ACKNOWLEDGED|HOST_SCHEDULED_DOWNTIME_DEPTH|HOST_NOTIFICATIONS_ENABLED|HOST_MAX_CHECK_ATTEMPTS]/order(HOST_CURRENT_STATE;DESC)/countColumn=HOST_ID/authkey=$APIkey/json";


$serviceQuery = "filter[AND(HOST_CURRENT_STATE%7C=%7C0;OR(SERVICE_CURRENT_STATE%7C=%7C1;SERVICE_CURRENT_STATE%7C=%7C2))]/columns[HOST_SCHEDULED_DOWNTIME_DEPTH%7CSERVICE_OUTPUT%7CSERVICE_NOTIFICATIONS_ENABLED%7CSERVICE_SCHEDULED_DOWNTIME_DEPTH%7CSERVICE_PROBLEM_HAS_BEEN_ACKNOWLEDGED%7CSERVICE_NAME%7CHOST_NAME%7CSERVICE_CURRENT_STATE%7CHOST_NAME%7CHOST_CURRENT_STATE%7CSERVICE_LAST_STATE_CHANGE%7CSERVICE_MAX_CHECK_ATTEMPTS%7CSERVICE_CURRENT_CHECK_ATTEMPT]/order(SERVICE_CURRENT_STATE;DESC)/countColumn=SERVICE_ID/authkey=$APIkey/json";

$hostTotalQuery = "filter/columns[HOST_CURRENT_STATE]/countColumn=HOST_ID/authkey=$APIkey/json";
$serviceTotalQuery = "filter/columns[SERVICE_CURRENT_STATE]/countColumn=SERVICE_ID/authkey=$APIkey/json";
$hostArray=array();
$serviceArray=array();
$errors = array();
$state = array();
$host_summary = array();
$service_summary = array();
$down_hosts = array();
$known_hosts = array();
$known_services = array();
$broken_services = array();
$curl_stats = array();

// Function that does the dirty to connect to the Nagios API
function fetchStatus($APIhost,$target, $query) {
	$statusDecoded = json_decode(file_get_contents("http://$APIhost/icinga-web/web/api/{$target}/$query"));
   if(!$statusDecoded) die("<div class='status_red'>Error with $target query </div>");
	if($statusDecoded->success !== 'true') die("<div class='status_red'>{$statusDecoded->errors[0]}</div>");
	$vars = get_object_vars($statusDecoded);

	return($vars);
}
	
	$hosts = fetchStatus($APIhost, "host", $hostTotalQuery);
	$services = fetchStatus($APIhost, "service", $serviceTotalQuery);
	
	$hostArray = fetchStatus($APIhost, "host", $hostQuery);
	$serviceArray = fetchStatus($APIhost, "service", $serviceQuery);
	
	foreach($hosts['result'] as $host) {
		$host_summary[$host->{'HOST_CURRENT_STATE'}] += 1;
	}

	foreach($services['result'] as $service) {
		$service_summary[$service->{'SERVICE_CURRENT_STATE'}] += 1;
	}


function serviceCompare($x, $y) {
    #return ($x['state'] < $y['state']) ? 1 : -1;
	  return 1;
	}

// At this point, the data collection is completed. 


/*if (count($errors) > 0) {
	foreach ($errors as $error) {
		echo "<div class='status_red'>{$error}</div>";
	}
}*/

foreach($hostArray["result"] as $host_details) {
	$host_attributes = get_object_vars($host_details);
	 if ( ($host_attributes['HOST_PROBLEM_HAS_BEEN_ACKNOWLEDGED'] > 0) || ($host_attributes['HOST_SCHEDULED_DOWNTIME_DEPTH'] > 0) || ($host_attributes['HOST_NOTIFICATIONS_ENABLED'] == 0) ) {
                $array_name = "known_hosts";
            } else {
                $array_name = "down_hosts";
            }
	// Populate the array. 
	array_push($$array_name, array(
				"hostname" => $host_attributes['HOST_NAME'],
				"state" => $host_attributes['HOST_CURRENT_STATE'],
				"duration" => $host_attributes['HOST_LAST_STATE_CHANGE'],
				"detail" => $host_attributes['HOST_OUTPUT'],
				"current_attempt" => $host_attributes['HOST_CURRENT_CHECK_ATTEMPT'],
				"max_attempts" => $host_attributes['HOST_MAX_CHECK_ATTEMPTS'],
				"tag" => "TAG",
				"is_hard" => ($host_attributes['HOST_CURRENT_CHECK_ATTEMPTS'] >= $host_attributes['HOST_MAX_CHECK_ATTEMPTS']) ? true : false,
				"is_downtime" => ($host_attributes['HOST_SCHEDULED_DOWNTIME_DEPTH'] > 0) ? true : false,
				"is_ack" => ($host_attributes['HOST_PROBLEM_HAS_BEEN_ACKNOWLEDGED'] > 0) ? true : false,
				"is_enabled" => ($host_attributes['HOST_NOTIFICATIONS_ENABLED'] > 0) ? true : false,
				)); 
}
// In any case, increment the overall status counters.
//        $host_summary = $hostArray['total'];

// Now parse the statuses for this host. 
foreach($serviceArray['result'] as $service_detail) {

	$service_attributes = get_object_vars($service_detail);
	//if the host is OK, AND the service is NOT OK. 
	// Sort the service into the correct array. It's either a known issue or not. 
   if ( ($service_attributes['SERVICE_PROBLEM_HAS_BEEN_ACKNOWLEDGED'] > 0) || ($service_attributes['SERVICE_SCHEDULED_DOWNTIME_DEPTH'] > 0) || ( $service_attributes['SERVICE_NOTIFICATIONS_ENABLED'] == 0 ) || ($service_attributes['HOST_SCHEDULED_DOWNTIME_DEPTH'] > 0) ) {
                    $array_name = "known_services";
                } else {
                    $array_name = "broken_services";
			}
	array_push($$array_name, array(
				"hostname" => $service_attributes['HOST_NAME'],
				"service_name" => $service_attributes['SERVICE_NAME'],
				"state" => $service_attributes['SERVICE_CURRENT_STATE'],
				"duration" => $service_attributes['SERVICE_LAST_STATE_CHANGE'],
				"detail" => $service_attributes['SERVICE_OUTPUT'],
				"current_attempt" => $service_attributes['SERVICE_CURRENT_CHECK_ATTEMPT'],
				"max_attempts" => $service_attributes['SERVICE_MAX_CHECK_ATTEMPTS'],
				"tag" => "TAG",
				"is_hard" => ($service_attributes['SERVICE_CURRENT_CHECK_ATTEMPT'] >= $service_attributes['SERVICE_MAX_CHECK_ATTEMPTS']) ? true : false,
				"is_downtime" => ($service_attributes['SERVICE_SCHEDULED_DOWNTIME_DEPTH|'] > 0) ? true : false,
				"is_ack" => ($service_attributes['SERVICE_PROBLEM_HAS_BEEN_ACKNOWLEDGED'] > 0) ? true : false,
				"is_enabled" => ($service_attributes['SERVICE_NOTIFICATIONS_ENABLED'] > 0) ? true : false,
				));
} 
//$service_summary = $serviceArray['total'];            
?>
</div>
<div id="info-window"><button class="close" onClick='$("#info-window").fadeOut("fast");'>&times;</button><div id="info-window-text"></div></div>
<div id="frame">
<div class="section">
<p class="totals"><b>Hosts total:</b> <?php foreach($host_summary as $state => $count) { echo "<span class='{$nagios_host_status_colour[$state]}'>{$count}</span> "; } ?></p>
<?php if (count($down_hosts) > 0) {  uasort($down_hosts, 'hostCompare');?>
	<table id="broken_hosts" class="widetable">
		<tr><th>Hostname</th><th width="150px">State</th><th>Down Since</th><th>Attempt</th><th>Detail</th></tr>
		<?php
		foreach($down_hosts as $host) {
			echo "<tr id='host_row' class='{$nagios_host_status_colour[$host['state']]}'>";
			echo "<td>{$host['hostname']}</td>";
			echo "<td>{$nagios_host_status[$host['state']]}</td>"; 
			echo "<td>{$host['duration']}</td>";
			echo "<td>{$host['current_attempt']}/{$host['max_attempts']}</td>";
			echo "<td class=\"desc\">{$host['detail']}</td>";
			echo "</tr>";
		}
	?>
		</table>
		<?php } else { ?>
			<table class="widetable status_green"><tr><td><b>All hosts OK</b></td></tr></table>
				<?php 

		}
if (count($known_hosts) > 0) {
	foreach ($known_hosts as $this_host) {
		if ($this_host['is_ack']) $status_text = "ack";
		if ($this_host['is_downtime']) $status_text = "downtime";
		if (!$this_host['is_enabled']) $status_text = "disabled";
		$known_host_list[] = "{$this_host['hostname']}<span class='known_hosts_desc'>({$status_text} - {$this_host['duration']})</span>";
	} 
	$known_host_list_complete = implode(" &bull; ", $known_host_list);
	echo "<table class='widetable known_hosts'><tr><td><b>Known Problem Hosts: </b> {$known_host_list_complete}</td></tr></table>";
}
?>

</div>
</div>

<div id="frame">
<div class="section">
<p class="totals"><b>Total:</b> <?php foreach($service_summary as $state => $count) { echo "<span class='{$nagios_service_status_colour[$state]}'>{$count}</span> "; } ?><span class="section_title">Services</span></p>
<?php if (count($broken_services) > 0) {  uasort($broken_services, 'compare');?>
	<table class="widetable" id="broken_services">
		<tr><th width="30%">Hostname</th><th width="40%">Service</th><th width="15%">State</th><th width="10%">Down Since</th><th width="5%">Attempt</th></tr>
		<?php
		foreach($broken_services as $service) {
			if ($service['is_hard']) { $soft_tag = ""; } else { $soft_tag = "(soft)"; }
			/*$controls = build_controls($service['tag'], $service['hostname'], $service['service_name']); */
			echo "<tr>";
			echo "<td>{$service['hostname']} " . /*<span class='controls'>{$controls}</span>*/"</td>";
			echo "<td class='{$nagios_service_status_colour[$service['state']]}'>{$service['service_name']} - {$service['detail']}</td>";
			echo "<td class='{$nagios_service_status_colour[$service['state']]}'>{$nagios_service_status[$service['state']]} {$soft_tag}</td>";
			echo "<td>{$service['duration']}</td>";
			echo "<td>{$service['current_attempt']}/{$service['max_attempts']}</td>";
			echo "</tr>";
		}
	?>
		</table>
		<?php } else { ?>
			<table class="widetable status_green"><tr><td><b>All services OK</b></td></tr></table>
				<?php } 

				if (count($known_services) > 0) { ?>
					<h4>Known Service Problems</h4>
						<table class="widetable known_service" id="known_services">
						<tr><th width="30%">Hostname</th><th width="37%">Service</th><th width="18%">State</th><th width="10%">Duration</th><th width="5%">Attempt</th></tr>
						<?php 
						foreach($known_services as $service) {
							if ($service['is_ack']) $status_text = "ack";
							if ($service['is_downtime']) $status_text = "downtime";
							if (!$service['is_enabled']) $status_text = "disabled";
							echo "<tr class='known_service'>";
							echo "<td>{$service['hostname']}" . /*<span class='tag tag_{$service['tag']}'>{$service['tag']}*/"</td>";
							echo "<td>{$service['service_name']}</td>";
							echo "<td class='{$nagios_service_status_colour[$service['service_state']]}'>{$nagios_service_status[$service['service_state']]} ({$status_text})</td>";
							echo "<td>{$service['duration']}</td>";
							echo "<td>{$service['current_attempt']}/{$service['max_attempts']}</td>";
							echo "</tr>";
						}
					?>

						</table>
						<?php } ?>

						</div>
						</div>

