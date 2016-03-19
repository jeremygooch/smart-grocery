<?php include_once(config/config.php); ?>
<html>
    <head>
        <title>WebSocket</title>
        
        <style type="text/css">
        #log {
            width:600px; 
            height:300px; 
            border:1px solid #7F9DB9; 
            overflow:auto;
            padding:10px;
        }
        #msg {
            width:300px;
        }
        </style>
        <script src="js/app.js"></script>
    <script type="text/javascript">
    var socket;

function init() {
    var host = "ws://<?php SITE_RESOURCE_NAME?>:9000/websockets";
    try { listen(); }
    catch(ex) { log('Some exception : '  + ex); }
    $("msg").focus();
}

function send() {
    var txt, msg;
    txt = $("msg");
    msg = txt.value;
    
    if(!msg) { 
        alert("Message can not be empty"); 
        return; 
    }
    
    txt.value="";
    txt.focus();
    
    try { 
        socket.send(msg); 
        log('Sent : ' + msg); 
    } 
    catch(ex) { 
        log(ex); 
    }
}

function quit() {
    if (socket != null) {
        log("Goodbye!");
        socket.close();
        socket=null;
    }
}

function reconnect() {
    quit();
    init();
}

// Utilities
function $(id) { 
    return document.getElementById(id); 
}

function log(msg) { 
    $('log').innerHTML += '<br />' + msg; 
    $('log').scrollTop = $('log').scrollHeight;
}

function onkey(event) { 
    if(event.keyCode==13) { 
        send(); 
    } 
}
</script>
        
    </head>
    
    <body onload="init()">
        
        <h3>WebSocket</h3>
        
        <div id="log"></div>
        
        Enter Message <input id="msg" type="textbox" onkeypress="onkey(event)"/>
        
        <button onclick="send()">Send</button>
        <button onclick="quit()">Quit</button>
        <button onclick="reconnect()">Reconnect</button>
        
    </body>
</html>
