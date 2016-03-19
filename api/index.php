<?php
  /*
   * ========================================
   * Smart Grocery API 
   * Author: Jeremy Gooch
   * 
   * ========================================
   *
   * This "index.php" file is the controller file for the smart grocery API.
   * It will be used to route the users to the proper method/file.
   * This API requires the "api" and "method" paramaters to be sent.
   *
np    * @api[str]    : Which API you are wanting to reference.
   * @method[str] : API Method to be used.
   * @data[obj]   : Any arguments, data or flags need to be passed in using this "data" object.
   *
   */



  //Collect data from HTTP Request
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);

//Load Config File
include_once("../config/config.php");


/* //ERROR! The $request[type] Value is Empty */
if(empty($request['api'])){
  $message = "ERROR: No API Provided!";
  error_log(print_r($message."\n".__FILE__, 1));
  header("Content-Type: application/json");
  echo(json_encode(array("code"=>"401", "message"=>$message)));

  die;
}


//----- START ----- API Controller -----//
switch($request['api']){
  // Methods
case "inventory":
  switch($request['method']){
    //Get Branch Data
  case "getLatestScan":


    /* $url = 'http://local.grocr.com:5000/v1/ocr'; */
    /* $ch = curl_init($url); */
    /* curl_setopt($ch, CURLOPT_POST, true); */
    /* curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); */
    /* curl_setopt($ch, CURLOPT_POSTFIELDS, (array('image_url'=>'http://jeremygooch.com/ocr/gmic-out.jpg'))); */
    /* curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); */

    /* $res = curl_exec($ch); */
    /* curl_close($ch); */


    /* if ($res) { */
    /*   $ocrData = json_decode($res); */

    /*   if ($ocrData->error) { */
    /*     header('Content-Type: application/json'); */
    /*     echo ($res); */
    /*     die(); */
    /*   } */

    /* } */

    /* //Send JSON Response */
    /* header('Content-Type: application/json'); */
    /* echo ($res); */
    /* break; */
  }
  break;
case 'NEXT':
  switch($request['TYPE']){
    //
  break;
  //***** END ***** APPLICANT METHODS *****//
default:
  //ERROR! Uncaught Request API
  error_invalid_api($request);
  break;
}




//----- ERROR FUNCTIONS -----//
function error_invalid_api($request){
  $message = "ERROR: Invalid API Type! ";
  error_log(print_r($message."\n".print_r($request,1), 1));
  header('Content-Type: application/json');
  echo(json_encode(array("code"=>"402", "message"=>$message, "request"=>print_r($request,1))));
  return;
}

function error_invalid_method($request){
  $message = "ERROR: Invalid API Method!\n";
  error_log(print_r($message."\n".print_r($request,1), 1));
  header('Content-Type: application/json');
  echo(json_encode(array("code"=>"403", "message"=>$message, "api"=>$request[api], "method"=>print_r($request[method],1), 'request'=>print_r($request,1))));
  return;
}
/* //---------------// */
?>
