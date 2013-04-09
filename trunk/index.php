<html>
<head>
	<title>Icinga Status</title>
	<link href="style.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/js/bootstrap.min.js"></script>
	<script src="js/external.js"></script>
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
			<table>
				<th>Temp</th><th>Humidity</th>
				<tr><td>
				<div id="temperature">
					<div id="data"></div>
				</div></td><td>
				
				<div id="humidity">
					<div id="data"></div></td></tr>
				</div>
			</table>
		</div>
		<div id="antall_saker">
		<div>
			<h3>Saker i dag</h3>
			<table>
				<th>Lukket</th><th>Mottatt</th>
				<tr><td>
				<div id="lukket">
					<div id="antall"></div>
				</td><td>
				</div>
				<div id="mottatt">
					<div id="antall"></div>
				</div>
				</td></tr>
			</table>
		</div>	
		</div>
		<div id="nagioscontainer"></div>
	</div>
</body>
