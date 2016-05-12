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

  public function get_recipes_by_current_inventory($page){
    // Get the current list of inventory items
    $curInvQry = "SELECT i.inventory_item_id, ii.id, ii.brief FROM inventory i, inventory_items ii WHERE i.inventory_item_id = ii.id;";
    $curInv = $this->gdao->queryAll($curInvQry);

    if ($curInv) {
      $f2fURL = "http://food2fork.com/api/search?key=". F2F_KEY ."&q=";
      foreach ($curInv as $itm) {
        $f2fURL .= $itm['brief'] . ',';
        
      }
      if ($page) {
        $f2fURL .= "&page=$page";
      }
      
      $f2fURL .= "&sort='r'";
      // create curl resource 
      $ch = curl_init(); 

      // set url 
      curl_setopt($ch, CURLOPT_URL, $f2fURL);

      //return the transfer as a string 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

      // $output contains the output string 
      $output = curl_exec($ch);

      // close curl resource to free up system resources 
      curl_close($ch);
      return $this->utilities->prep_response($output);
    } else {
      return $this->utilities->prep_response("The inventory is currently inaccessable so the recipes could not be loaded correctly.", 401);
    }
  }
  
  public function get_recipes_by_id($id){
    /* if ($id) { */
    /*   return $id; */
    /* } else { */
    /*   return $this->utilities->prep_response("Missing paramters. No recipe id provided.", 401); */
    /* } */
  }

  
  }