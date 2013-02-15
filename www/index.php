<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script src="static/js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="static/js/app.js"></script>
	<script type='text/javascript' src='/static/js/strophe.js'></script>
	<script type='text/javascript' src='/static/js/strophe.archive.js'></script>
</head>
<body>

<script>
	$(function () {
		var uri = 'http://localhost:5280/http-bind/';
		app.transport.connect(uri, 'admin@localhost', 'admin');
		$('form').submit(function () {
			app.send($('#msg').val(), $('#to').val());
			app.getHistory($('#to').val());
			return false;
		});
	})

</script>
<h2>WebSocket Test</h2>

<form>
	<textarea id="msg"></textarea><br>
	<input id="to" value="bazilio@localhost"/>
	<button type="submit">Yep!</button>
</form>
<div id="output"></div>
</body>
</html>