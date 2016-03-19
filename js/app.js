function listen () {
    socket = new WebSocket(host);
    log('WebSocket - status ' + socket.readyState);

    socket.onopen = function(msg) { 
        if(this.readyState == 1)
        {
            log("We are now connected to websocket server. readyState = " + this.readyState); 
        }
    };

    //Message received from websocket server
    socket.onmessage = function(msg) 
    { 
        log(" [ + ] Received: " + msg.data); 
    };

    //Connection closed
    socket.onclose = function(msg) 
    { 
        log("Disconnected - status " + this.readyState); 
    };

    socket.onerror = function()
    {
        log("Some error");
    }
}
