<?php
   require_once 'config.php';


function fetchAmount($dummydata, $query,$type, $amount) {
	if($dummydata) {
		return array($type => $amount);
    }
	$result = mssql_query($query);
   $amount = mssql_fetch_assoc($result);
   return array($type => $amount['computed']);
	
}

if(isset($_GET['type'])) {
	if($_GET['type'] == 'Graph') {
			$resultArray = array();
			$closedQuery = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0 AND mrSTATUS = 'Closed'";
			$receivedQuery = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0"; 	
			$resultArray = array_merge(fetchAmount($dummydata, $closedQuery, 'Closed', 7), fetchAmount($dummydata, $receivedQuery, 'Received', 13),
												fetchAmount($dummydata, $closedQuery, 'ClosedAll', 12), fetchAmount($dummydata, $closedQuery, 'Open', 60));
			
			echo json_encode($resultArray, JSON_NUMERIC_CHECK);
	} 
	elseif ($_GET['type'] == 'Received') {
		$query = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0";
		echo json_encode(fetchAmount($dummydata, $query, 'Received'), JSON_NUMERIC_CHECK);
	}
}
