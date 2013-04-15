<html>
<head>
	<title>Icinga Status</title>
	<link href="style.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/js/bootstrap.min.js"></script>
	<script src="js/external.js"></script>
   <script src="Highcharts/js/highcharts.js"></script>
	<script src="js/jqBarGraph.1.1.min.js"></script>
	<script src="js/clock.js"></script>
</head>

<body>
	<div id="contents">
		<div id="clock">
   			<div id="Date"></div>
			<ul>
				<li id="hours"></li>
				<li id="point">:</li>
				<li id="min"></li>
				<li id="point">:</li>
				<li id="sec"></li>
			</ul>
		</div>

		<div id="serverroom_climate">
				<div id="temperature">
					<img src="room.png">
					<div id="data"></div>
				</div>
				
				<div id="humidity">
					<img src="humid.png">
					<div id="data"></div>
				</div>
		</div>
		<div id="footprints">
			<div id="graph"></div>
		</div>
		<div id="nagioscontainer"></div>
	</div>
</body>
