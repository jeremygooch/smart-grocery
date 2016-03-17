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


/* curl -X POST http://localhost:5000/v1/ocr -d '{"image_url": "http://jeremygooch.com/ocr/20160226_200114.jpg"}' -H "Content-Type: application/json" */



//----- START ----- API Controller -----//
switch($request['api']){
  // Methods
case "inventory":
  switch($request['method']){
    //Get Branch Data
  case "getLatestScan":

    /* $curl = curl_init(); */
    /* curl_setopt_array($curl, */
    /*                   array( */
    /*                         CURLOPT_RETURNTRANSFER => 1, */
    /*                         CURLOPT_URL => 'curl -X POST http://localhost:5000/v1/ocr -d \'{"image_url": "http://jeremygooch.com/ocr/20160226_200114.jpg"}\' -H "Content-Type: application/json"' */
    /*                         )); */

    /* $resp = curl_exec($curl); */
    /* curl_close($curl); */

    
    $curl = curl_init();
    /* curl -X POST http://localhost:5000/v1/ocr -d '{"image_url": "http://jeremygooch.com/ocr/20160226_200114.jpg"}' -H "Content-Type: application/json" */
    curl_setopt_array($curl, array(
                                   CURLOPT_URL => ' http\:\/\/localhost\:5000\/v1\/ocr',
                                   CURLOPT_POST => 1,
                                   CURLOPT_POSTFIELDS => array('image_url'=>'http://jeremygooch.com/ocr/gmic-out.jpg')
                                   ));
    // Set the content type
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-length: 250'));

    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    if($resp === false) {
      error_log('Curl error: ' . curl_error($curl));
    } else {
      error_log('Operation completed without any errors');
    }

    
    // Close request to clear up some resources
    curl_close($curl);





    


    /* $resp = 'you win life!'; */
    $data = array("code"=>"200", "message"=>"success", "data"=>array($resp));
    
    /* //Load branch() Class */
    /* $branch = new branch(); */
    /* //Get Branch Data */
    /* $data = $branch->get_branch_data($request[data][branch_id]); */


    //Send JSON Response
    header('Content-Type: application/json');
    echo json_encode($data);
    break;
  }
  break;
  //***** END ***** APPLICANT METHODS *****//
default:
  //ERROR! Uncaught Request API
  error_invalid_api($request);
  break;
}




    
/*     //-- NEW -- Get Branch Data */
/*   case "NEW_getBranchData": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Get Branch Data */
/*     $data = $branch->NEW_get_branch_data($request[data][branch_id]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data); */
/*     break; */
/*     //Update Branch Data */
/*   case "updateBranchData": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Update Branch Personnel */
/*     $data = $branch->update_branch_data($request[data]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data);                 */
/*     break;                 */
/*     //Update Branch Personnel */
/*   case "addRemoveBranchPersonnel": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Update Branch Personnel */
/*     $data = $branch->add_remove_branch_personnel($request[data]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data);                 */
/*     break; */
/*     //Update Branch Orientation Location */
/*   case "updateOrientationLocationData": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Update Branch Personnel */
/*     $data = $branch->update_orientation_location_data($request[data]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data);                 */
/*     break; */
/*     //Add/Remove Branch Orientation Location Override Date */
/*   case "addRemoveOrientationLocationOverrideDate": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Update Branch Personnel */
/*     $data = $branch->add_remove_orientation_location_override_date($request[data]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data);                 */
/*     break;                 */
/*     //Get Branch Orientation Days */
/*   case "getBranchOrientationDays": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Get Branch Orientation Days */
/*     $data = $branch->get_branch_orientation_days($request[data][branch_id]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data); */
/*     break; */
/*     //Get Branch Orientation Dates */
/*   case "getBranchOrientationDates": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Get Branch Orientation Dates */
/*     $data = $branch->get_branch_orientation_dates($request[data][branch_id], $request[data][location_id]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data); */
/*     break; */
/*     //Get Branch Addresses */
/*   case "getBranchAddress": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Get Branch Address */
/*     $data = $branch->get_branch_address($request[data][branch_id]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data); */
/*     break; */
/*     //Get Branch Orientation Class Size Report */
/*   case "getOrientationClassSizeReport": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Get Orientation Class Size Report */
/*     $data = $branch->get_orientation_class_size_report($request[data][branch_id]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data); */
/*     break; */
/*     //Get Branch Orientation Locations */
/*   case "getBranchOrientationLocations": */
/*     //Load branch() Class */
/*     $branch = new branch(); */
/*     //Get Branch Orientation Locations */
/*     $data = $branch->get_branch_orientation_locations($request[data][branch_id]); */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($data); */
/*     break; */
/*   default: */
/*     error_invalid_method($request); */
/*     break; */
/*   } */
/*   break; */
/*   //\***** END ***** BRANCH METHODS *****\// */
/*   //\***** START ***** APPLICANT METHODS *****\// */
/* case "applicant": */
/*   // *!!!!!* Validate API Access for "applicant" API Methods *!!!!!* */
/*   validate_api_access($request); */
/*   switch($request[method]){ */
/*     //Authenticate Applicant */
/*   case "authenticateApplicant": */
/*     //Load applicant() Class */
/*     $applicant = new applicant(); */
/*     //Attempt to Authenticate Applicant */
/*     $res = $applicant->authenticate_applicant($request[data][email], $request[data][password]); */
/*     //Prepare JSON Response */
/*     if($res){$json_response = array("code"=>200, "applicant_id"=>$res);} */
/*     else{$json_response = array("code"=>401, "message"=>"Error...Invalid Credentials!");} */
/*     //Send JSON Response */
/*     header('Content-Type: application/json'); */
/*     echo json_encode($json_response); */
/*     break; */
/*   } */
/*   break; */
/*   //\***** END ***** APPLICANT METHODS *****\// */
/* default: */
/*   //ERROR! Uncaught Request API */
/*   error_invalid_api($request); */
/*   break; */
/* } */
/* //----- END ----- API Controller -----// */
/* die; */

/* //----- VALIDATE API ACCESS FUNCTION -----// */
/* /\***** */
/*       This function will check the user's IP against a list of valid IP addresses. This will be used to validate their access to the "api.champ.net" service. */
/* *****\/ */

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
