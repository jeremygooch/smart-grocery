<?php
class utilities {
  
  function __construct(){
    //Generic DAO
    $this->gdao = new genericDAO();
  }

  public function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
      if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
        return true;
      }
    }
    return false;
  }

  public function prep_response($data = null, $code = 200) {
    // This function preps the responses for returning to the frontend
    // in a uniform way
    // $data[obj]: The data object to wrap
    // $type[str]: Success if success (code 200),
    //

    switch($code) {
    case 401:
      $out = array("code"=>$code, "message"=> "Error", "data"=>$data);
      break;
    case 200:
      $out = array("code"=>$code, "message"=> "Success", "data"=>$data);
      break;
    default:
      $out = array("Invalid type!");
      break;
    }
    
    
    return json_encode($out);
    
  }

}