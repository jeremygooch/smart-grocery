<?php

include_once("../config/config.php");

$receipt_path = "../receipts/";

$f = new FileAlterationMonitor($receipt_path);

while (TRUE) {
  sleep(1);

  if ($newFiles = $f->getNewFiles()) {
    // Code to handle new files
    // $newFiles is an array that contains added files
    /* error_log(print_r($newFiles)); */

    for (var $i=0; $i< count($newFiles); $i++) {
      
    }
  }

  if ($removedFiles = $f->getRemovedFiles()) {
    // Code to handle removed files
    // $newFiles is an array that contains removed files
  }

  $f->updateMonitor();
}