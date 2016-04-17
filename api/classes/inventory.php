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
      $output = array('meat'=>array(), 'produce'=>array(), 'dairy'=>array(), 'pantry'=>array(), 'other'=>array());
      for ($i=0; $i< count($res); $i++) {
        // Break the date apart
        $expires = strtotime($res[$i]['expires']);
        $month = date("m",$expires);
        $day = date("d",$expires);
        $year = date("Y",$expires);
        $res[$i]['exp'] = array('month'=>$month,'day'=>$day,'year'=>$year);



        // See if anything is expiring soon
        $curDate = time();
        $timeDiff = ceil(($expires - $curDate) / (60*60*24));
        if ($timeDiff > 31) {
          $res[$i]['daysLeft'] = ceil($timeDiff / 31) . ' mo';
        } else {
          $plural = $timeDiff > 1 ? 's' : '';
          $res[$i]['daysLeft'] = $timeDiff . ' day' . $plural;
        }

        if ($timeDiff < 2) {
          $res[$i]['expFlag'] = 'danger';
        } else if ($timeDiff < 7) {
          $res[$i]['expFlag'] = 'warning';
        } else {
          $res[$i]['expFlag'] = 'none';
        }

        // Categorize this item accordingly
        array_push($output[$res[$i]['category']], $res[$i]);
      }

      return $this->utilities->prep_response($output);
    } else {
      return $this->utilities->prep_response("The inventory could not be accessed at this time.", 401);
    }
  }
}