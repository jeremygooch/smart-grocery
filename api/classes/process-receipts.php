<?php

class processReceipts{
  public function __construct() {}
  public function extractText($image_url) {

    $grocr = 'http://local.grocr.com:5000/v1/ocr';
    $ch = curl_init($grocr);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('image_url'=>$image_url)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $res = curl_exec($ch);
    curl_close($ch);


    if ($res) {
      $ocrData = json_decode($res);

      if ($ocrData->error) {
        header('Content-Type: application/json');
        echo ($res);
        die();
      }
      error_log(print_r($res,1));
      //Send JSON Response
      /* header('Content-Type: application/json'); */
      /* echo ($res); */
      /* break; */
    }
    
  }
}