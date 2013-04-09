<?php
   require_once 'config.php';


function fetchAmount($dummydata, $query,$type) {
	if($dummydata) {
		return array($type => 12);
    }
	$result = mssql_query($query);
   	$amount = mssql_fetch_assoc($result);
   	return array($type => $amount['computed'])
	
}

if(isset($_GET['type'])) {
	if($_GET['type'] == 'Closed') {
			$query = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0 AND mrSTATUS = 'Closed'";
			echo json_encode(fetchAmount($dummydata, $query, 'Closed'), JSON_NUMERIC_CHECK);
	} 
	elseif ($_GET['type'] == 'Recieved') {
		$query = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0";
		echo json_encode(fetchAmount($dummydata, $query, 'Recieved'), JSON_NUMERIC_CHECK);
	}
}
