<?php
  // -----
  // Type: Class [inventory]
  // Filename: inventory.php
  // Author: Jeremy Gooch
  //
  // The inventory class is used for retrieving the current inventory
  //
  // -----

class inventory {
  function __construct(){
    // Generic DAO
    $this->gdao = new genericDAO();
    // Utilities
    $this->utilities = new utilities();
  }

  public function get_inventory_items(){
    $query = "SELECT * FROM inventory AS i LEFT JOIN inventory_items AS ii ON i.inventory_item_id = ii.id;";
    $res = $this->gdao->queryAll($query);

    if ($res) {
      for ($i=0; $i< count($res); $i++) {
        // Break the date apart
        $expires = strtotime($res[$i]['expires']);
        $month = date("m",$expires);
        $day = date("d",$expires);
        $year = date("Y",$expires);

        // See if anything is expiring soon......
        $expDate = new DateTime($res[i]['expires']);
        $curDate = new DateTime(date());
        $test = date_diff($expDate, $curDate);
        /* $test = $expDate->diff($curDate); */
        error_log(print_r($test,1));
          
        $res[$i]['exp'] = array('month'=>$month,'day'=>$day,'year'=>$year);
      }

      return $this->utilities->prep_response($res);
    } else {
      return $this->utilities->prep_response("The inventory could not be accessed at this time.", 401);
    }
  }
}