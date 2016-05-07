<?php
  // -----
  // Type: Class [inventory]
  // Filename: inventory.php
  // Author: Jeremy Gooch
  //
  // The inventory class is used for retrieving the current inventory
  //
  // -----

class recipes {
  function __construct(){
    // Generic DAO
    $this->gdao = new genericDAO();
    // Utilities
    $this->utilities = new utilities();
  }

  public function get_recipes_by_current_inventory(){

    // create curl resource 
    $ch = curl_init(); 

    // set url 
    curl_setopt($ch, CURLOPT_URL, "http://food2fork.com/api/search?key=". F2F_KEY ."&q=chicken,goat&cheese,tortilla&chips,cilantro,french&bread,mustard");
    /* curl_setopt($ch, CURLOPT_URL, "http://food2fork.com/api/get?key=". F2F_KEY ."&rId=35382");  */

    //return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch);
    error_log(F2F_KEY);

    // close curl resource to free up system resources 
    curl_close($ch);
    return $this->utilities->prep_response($output);
  }
  }