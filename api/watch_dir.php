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
      // Clean up the image using G'MIC
      $edited = '[' . date('M_d_Y') . ']' . $files[$i];
      shell_exec('gmic -input ' . SITE_DIRECTORY . $receiptURL . $files[$i] . ' -v -99 -gimp_stamp 1,50,0,0,0,0,0 -output ' . SITE_DIRECTORY . $receiptURL . $edited);

      // Send the cleaned image to be OCRed and have the text extracted
      $extractText = $process->extractText($receiptURL . $edited);
    }
  }

  if ($removedFiles = $f->getRemovedFiles()) {
    // Code to handle removed files
    // $newFiles is an array that contains removed files
  }

  $f->updateMonitor();
}