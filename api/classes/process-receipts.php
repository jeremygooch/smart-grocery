<?php

class processReceipts{
  public function __construct() {}
  public function extractText($image_url) {

    $grocr = 'http://local.grocr.com:5000/v1/ocr';
    $ch = curl_init($grocr);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('image_url'=> SITE_URL . $image_url)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $res = curl_exec($ch);
    curl_close($ch);


    if ($res) {
      $ocrData = json_decode($res);

      if (array_key_exists('error', $ocrData)) {
        header('Content-Type: application/json');
        echo ($res);
        die();
      }
      
      // ////////////////////////////////////////////
      // NEED TO ARCHIVE THE IMAGE HERE... 
      // CHECKED FILE PERMISSIONS
      // CHECKED PATH VALIDITY
      // NOT WORKING
      // ////////////////////////////////////////////
      /* error_log(file_exists("/home/jgooch/smart-grocery" . $image_url)); */
      /* rename("/home/jgooch/smart-grocery" . $image_url, IMG_ARCHIVE_PATH); */



      // Break the results apart
      $itmList = explode("\n", $ocrData->output);
      $clnList = array();
      for ($i = 0; $i < count($itmList); $i++) {
        if ($itmList[$i] != '') {
          $words = explode(" ", $itmList[$i]);
          for ($x = 0; $x < count($words); $x++) {
            // Find matches against words first
          }
        }
        // If Not matches, drop the row like a bad habit!
      }


      
      //Send JSON Response
      /* header('Content-Type: application/json'); */
      /* echo ($res); */
      /* break; */
    }
    
  }

}