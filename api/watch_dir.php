<?php

include_once("../config/config.php");

$receipt_path = "../receipts/";

// Setup class for monitoring changes to the receipt directory
$f = new FileAlterationMonitor($receipt_path);

// Setup class for interacting with uploaded receipts
$process = new processReceipts();
$receiptURL = SITE_URL . '/receipts/';


while (TRUE) {
  sleep(1);

  if ($newFiles = $f->getNewFiles()) {
    // Code to handle new files
    // $newFiles is an array that contains added files
    /* error_log(print_r($newFiles)); */

    for ($i=0; $i< count($newFiles); ++$i) {
      $extractText = $process->extractText($receiptURL . $newFiles[$i]);
    }
  }

  if ($removedFiles = $f->getRemovedFiles()) {
    // Code to handle removed files
    // $newFiles is an array that contains removed files
  }

  $f->updateMonitor();
}