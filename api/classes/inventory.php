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
    $output = array('meat'=>array(), 'produce'=>array(), 'dairy'=>array(), 'pantry'=>array(), 'other'=>array());

    if ($res) {
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

    }
    return $this->utilities->prep_response($output);
  }


  public function delete_item($id){
    $query = "DELETE FROM inventory WHERE inventory_id = '$id';";
    $res = $this->gdao->queryRow($query);

    return $this->utilities->prep_response("$id successfully deleted.");
  }

  public function delete_items($items){
    foreach ($items as $item) {
      $this->delete_item($item['inventory_id']);
    }
    return $this->utilities->prep_response("Items successfully deleted.");
  }

  
  public function update_item($id, $data){
    $query = '';
    foreach ($data as $key => $value) {
      $query .= "UPDATE inventory SET $key = '$value' WHERE inventory_id = '$id'; ";
    }
    $res = $this->gdao->queryRow($query); 

    return $this->utilities->prep_response("$id successfully updated.");
  }

}