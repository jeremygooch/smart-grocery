<?php

include_once("../config/config.php");

$receipt_path = "../receipts/";

// Setup class for monitoring changes to the receipt directory
$f = new FileAlterationMonitor($receipt_path);

// Setup class for interacting with uploaded receipts
$process = new processReceipts();
$receiptURL = '/receipts/';


while (TRUE) {
  sleep(1);

  if ($newFiles = $f->getNewFiles()) {
    // Code to handle new files
    // $newFiles is an array that contains added files

    $files = array_values($newFiles); // Clean up the array
    
    for ($i=0; $i< count($files); ++$i) {
      $extractText = $process->extractText($receiptURL . $files[$i]);
    }
  }

  if ($removedFiles = $f->getRemovedFiles()) {
    // Code to handle removed files
    // $newFiles is an array that contains removed files
  }

  $f->updateMonitor();
}