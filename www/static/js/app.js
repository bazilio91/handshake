/**
 * Created with JetBrains PhpStorm.
 * User: bazilio
 * Date: 2/14/13
 * Time: 7:02 PM
 */


var app = {
	transport:{
		connection:null,
		connected:false,
		queue:[],

		onConnect:function (status) {
			var self = this;
			switch (status) {
				case Strophe.Status.CONNECTING:
					app.transport.onOpen();
					break;
				case Strophe.Status.CONNFAIL:
					app.transport.onFail();
					break;
				case Strophe.Status.DISCONNECTING:
					app.transport.onDisconnecting();
					break;

				case Strophe.Status.DISCONNECTED:
					app.transport.onDisconnected();
					break;

				case Strophe.Status.CONNECTED:
					app.transport.onConnected();
					break;

			}
		},
		onOpen:function () {
			console.log('Connecting.');
		},
		onConnected:function () {
			var self = this;
			console.log('Connected.');
			self.connected = true;
			self.connection.addHandler(self.onMessage, null, 'message', null, null, null);
			self.connection.send($pres().tree());

			while (self.queue.length > 0 && self.connected) {
				var data = self.queue.pop();
				app.send(data.msg, data.to);
			}

		},
		onFail:function () {
			console.log('Failed to connect.');
		},
		onDisconnecting:function () {
			console.log('Disconnecting.');
		},
		onDisconnected:function () {
			var self = this;
			self.connected = false;
		},
		onMessage:function (msg) {
			var to = msg.getAttribute('to');
			var from = msg.getAttribute('from');
			var type = msg.getAttribute('type');
			var elems = msg.getElementsByTagName('body');

			if (type == "chat" && elems.length > 0) {
				var body = elems[0];
				var text = Strophe.getText(body);
				console.log('Recieved from ' + from + ': ' + text);
				$('body').append("<p><b>" + from + "</b>: " + text + "</p>");
			}

			// we must return true to keep the handler alive.
			// returning false would remove it after it finishes.
			return true;
		},
		/**
		 * http://stackoverflow.com/questions/12062950/handling-presence-in-strophe-js-based-chat-application
		 * @param presence
		 */
		onPresence:function (presence) {
			var presence_type = $(presence).attr('type'); // unavailable, subscribed, etc...
			var from = $(presence).attr('from'); // the jabber_id of the contact
			if (presence_type != 'error') {
				if (presence_type === 'unavailable') {
					// Mark contact as offline
				} else {
					var show = $(presence).find("show").text(); // this is what gives away, dnd, etc.
					if (show === 'chat' || show === '') {
						// Mark contact as online
					} else {
						// etc...
					}
				}
			}
		},

		connect:function (uri, user, password) {
			var self = this;
			self.connection = new Strophe.Connection(uri);

			self.connection.connect(user, password, app.transport.onConnect);
		},

		send:function (msg, to) {
			var self = this;
			if (self.connected) {
				var message = $msg({to:to, from:self.connection.jid, type:'chat'}).c("body").t(msg);
				self.connection.send(message.tree());

				$('body').append("<p><b>Вы:</b>: " + msg + "</p>");
				console.log('TRANSPORT: Send to ' + to + ' : ' + msg);
			} else {
				self.queue.push({msg:msg, to:to});
			}
		}

	},
	init:function () {

	},
	send:function (msg, to) {
		var self = this;

		self.transport.send(msg, to);

	},
	getHistory:function (jid) {
		app.transport.connection.archive.listCollections('bazilio@localhost', null, function (e, r) {
			console.log(e, r);
		});
	}
};