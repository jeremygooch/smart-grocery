<?php

// Open an inotify instance
$fd = inotify_init();

// - Using stream_set_blocking() on $fd
stream_set_blocking($fd, 0);

// Watch __FILE__ for metadata changes (e.g. mtime)
$watch_descriptor = inotify_add_watch($fd, __FILE__, IN_ATTRIB);

// generate an event
touch(__FILE__);

// this is a loop
while(true){

  $events = inotify_read($fd); // Does no block, and return false if no events are pending  

  // do other stuff here, break when you want...
  
}

// Stop watching __FILE__ for metadata changes
inotify_rm_watch($fd, $watch_descriptor);

// Close the inotify instance
// This may have closed all watches if this was not already done
fclose($fd);