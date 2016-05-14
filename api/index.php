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
   * @api[str]    : Which API you are wanting to reference.
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
case "receipts":
  switch($request['method']){
  case "getNewReceipts":
    //Load Class
    $class = new receipts();
    $data = $class->get_new_receipts();

    //Send JSON Response
    header('Content-Type: application/json');
    echo $data;
    break;

  case "getAllReceipts":
    //Load Class
    $class = new receipts();
    $data = $class->get_all_scans();

    //Send JSON Response
    header('Content-Type: application/json');
    echo $data;
    break;

  case "deleteReceipt":
    if ($request['id']) {
      //Load Class
      $class = new receipts();
      $data = $class->delete_receipt($request['id']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
      
    break;

  case "saveItem":
    if ($request['id'] && $request['inventory_item_id'] && $request['quantity'] && $request['expires'] && $request['category']) {
      //Load Class
      $class = new receipts();
      $data = $class->save_item($request['id'],$request['inventory_item_id'],$request['quantity'],$request['units'],$request['expires'], $request['category'],$request['freezer']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;

  case "deleteItem":
    if ($request['item_id']) {
      //Load Class
      $class = new receipts();
      $data = $class->delete_item($request['item_id']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;

  case "archiveReceipt":
    if ($request['id']) {
      //Load Class
      $class = new receipts();
      $data = $class->archive_receipt($request['id']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;

  default:
    //ERROR! Uncaught Request Method
    error_invalid_api($request);
    break;

  }
  break;

case "inventory":
  switch($request['method']){
  case "getInventoryItems":
    //Load Class
    $class = new inventory();
    $data = $class->get_inventory_items();

    //Send JSON Response
    header('Content-Type: application/json');
    echo $data;
    break;
  case "deleteItem":
    if ($request['item_id']) {
      //Load Class
      $class = new inventory();
      $data = $class->delete_item($request['item_id']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;
  case "updateItem":
    if ($request['id'] && $request['data']) {
      //Load Class
      $class = new inventory();
      $data = $class->update_item($request['id'], $request['data']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;
  case "deleteItems":
    if ($request['items']) {
      //Load Class
      $class = new inventory();
      $data = $class->delete_items($request['items']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;
  default:
    //ERROR! Uncaught Request API
    error_invalid_api($request);
    break;
  }
  break;


case "recipes":
  switch($request['method']){
  case "getRecipesByCurrentInventory":
    if ($request['page']) {
      error_log('=======================');
      //Load Class
      $class = new recipes();
      $data = $class->get_recipes_by_current_inventory($request['page']);

      //Send JSON Response
      header('Content-Type: application/json');
      echo $data;
    } else {
      error_missing_arguments($request);
    }
    break;
  case "getRecipesById":
    //Load Class
    $class = new recipes();
    $data = $class->get_recipes_by_id($request['id']);

    //Send JSON Response
    header('Content-Type: application/json');
    echo $data;
    break;
  default:
    //ERROR! Uncaught Request API
    error_invalid_api($request);
    break;
  }
  break;
case 'NEXT':
  switch($request['method']){
  case "xxx":
    //
    break;
    //***** END ***** METHODS *****//
  default:
    //ERROR! Uncaught Request API
    error_invalid_api($request);
    break;
  }
  
default:
  //
  break;
}




//----- ERROR FUNCTIONS -----//
function error_missing_arguments($request){
  $message = "ERROR: Missing Parameters for this type! ";
  error_log(print_r($message."\n".print_r($request,1), 1));
  header('Content-Type: application/json');
  echo(json_encode(array("code"=>"402", "message"=>$message, "request"=>print_r($request,1))));
  return;
}

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
