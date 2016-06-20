<?php
  // -----
  // Type: Class [authenticate]
  // Filename: authenticate.php
  // Author: Jeremy Gooch
  //
  // The authenticate class is used for authenticating users
  //
  // -----

class authenticate {
  function __construct(){
    // Generic DAO
    $this->gdao = new genericDAO();
    // Utilities
    $this->utilities = new utilities();
  }

  public function sign_in($user, $pass){
    //Verify User's Credentials
    $res = $this->verify_credentials($user, $pass);

    //Prepare Response and setup session data
    if($res){
      setcookie('loggedIn', true, time()+60*60*24*365, '/', $_SERVER['SERVER_NAME']);
      $res = array('code' => '200', 'message' => 'Success');
    }else{$res = array('code' => '401', 'message' => 'Unauthorized');}
    error_log(print_r($res,1));
    return $this->utilities->prep_response($res);
  }


  //Verify User Credentials
  //**** This function will only verify the username and password credentials. ****
  private function verify_credentials($user, $pass){
    $query = "SELECT COUNT(*) FROM users WHERE user = '$user' AND password = sha1('$pass')";
    $res = $this->gdao->queryOne($query);
    return $res;
  }


}