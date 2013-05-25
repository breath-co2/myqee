<input type="button" value="启动服务" onclick="MyQEE.run_ajax('<?php echo Core::url('websocket/start');?>');" />
<input type="button" value="停止服务" onclick="MyQEE.ask_todo('<?php echo Core::url('websocket/stop');?>','您确实要停止服务？')" />
<input type="button" value="重启服务" onclick="MyQEE.ask_todo('<?php echo Core::url('websocket/restart');?>','您确实要重启服务？')" />

<style>
#log {
    width: 440px;
    height: 200px;
    border: 1px solid #7F9DB9;
    overflow: auto;
}

#msg {
    width: 330px;
}
</style>

<script>
var socket;

function init(){
  var host = "ws://localhost:11101";
  try{
    socket = new WebSocket(host);
    log('WebSocket - status '+socket.readyState);
    socket.onopen    = function(msg){ log("Welcome - status "+this.readyState); };
    socket.onmessage = function(msg){ log("Received: "+msg.data); };
    socket.onclose   = function(msg){ log("Disconnected - status "+this.readyState); };
  }
  catch(ex){ log(ex); }
  $("msg").focus();
}

function send(){
  var txt,msg;
  txt = $("msg");
  msg = txt.value;
  if(!msg){ alert("Message can not be empty"); return; }
  txt.value="";
  txt.focus();
  try{ socket.send(JSON.stringify({'action':'test','msg':msg})); log('Sent: '+msg); } catch(ex){ log(ex); }
}
function quit(){
  log("Goodbye!");
  socket.close();
  socket=null;
}

// Utilities
function $(id){ return document.getElementById(id); }
function log(msg){ $("log").innerHTML+="<br>"+msg; }
function onkey(event){ if(event.keyCode==13){ send(); } }
</script>

<h3>WebSocket v2.00</h3>
<div id="log"></div>
<input id="msg" type="input" onkeypress="onkey(event)" />
<button onclick="send()">Send</button>
<button onclick="quit()">Quit</button>
<div>Commands: hello, hi, name, age, date, time, thanks, bye</div>


<script>
init();
</script>