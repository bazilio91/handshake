/**
 * Created with JetBrains PhpStorm.
 * User: bazilio
 * Date: 2/14/13
 * Time: 7:02 PM
 */


var app = {
	socket:{
		connection:null,
		connected:false,
		queue:[],

		onError:function (e) {
		},
		onOpen:function (e) {
			var self = this;
			self.connected = true;
			while (self.queue.length > 0 && self.connected) {
				var data = self.queue.pop();
				self.send(data);
			}
		},
		onClose:function (e) {
			var self = this;
			self.connected = false;
		},
		onMessage:function (e) {
			console.log('Recieved: ', e.data)
		},

		connect:function (uri) {
			var self = this;
			console.log(self);
			self.connection = new WebSocket(uri);
			self.connection.onopen = function (e) {
				self.onOpen(e)
			};
			self.connection.onclose = function (e) {
				self.onClose(e)
			};
			self.connection.onmessage = function (e) {
				self.onMessage(e)
			};
			self.connection.onerror = function (e) {
				self.onError(e)
			};
		},

		send:function (data) {
			var self = this;
			if (self.connected) {
				console.log('Truelly send', data);
				self.connection.send(data);
			} else {
				self.queue.push(data);
			}
		}
	},
	init:function () {

	},
	send:function (data) {
		var self = this;

		console.log('Sending: ', data);
		self.socket.send(data);

	}
};