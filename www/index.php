<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script src="static/js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="static/js/app.js"></script>
</head>
<body>

<script>
	$(function () {
		var wsUri = "ws://handshake.bazilio:12345/echo/";
		app.socket.connect(wsUri);
		$('form').submit(function(){
			app.send($('#msg').val());
			return false;
		});
	})

</script>
<h2>WebSocket Test</h2>
<form>
	<textarea id="msg"></textarea>
	<button type="submit">Yep!</button>
</form>
<div id="output"></div>
</body>
</html>