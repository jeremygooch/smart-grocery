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
    $query = "SELECT i.*, ii.description FROM inventory AS i LEFT JOIN inventory_items AS ii ON i.inventory_item_id = ii.id;";
    $res = $this->gdao->queryAll($query);
    $output = array('meat'=>array(), 'produce'=>array(), 'dairy'=>array(), 'pantry'=>array(), 'other'=>array());

    if ($res) {
      for ($i=0; $i< count($res); $i++) {

        // See if anything is expiring soon
        $daysLeft = $this->calc_days_left($res[$i]['expires']);
        $res[$i]['exp'] = $daysLeft['exp'];
        $res[$i]['daysLeft'] = $daysLeft['daysLeft'];
        $res[$i]['expFlag'] = $daysLeft['flag'];

        // Categorize this item accordingly
        array_push($output[$res[$i]['category']], $res[$i]);
      }

    }
    return $this->utilities->prep_response($output);
  }

  public function get_all_inventory_items(){
    $query = "SELECT * FROM inventory_items";
    $res = $this->gdao->queryAll($query);
    $output = array('meat'=>array(), 'produce'=>array(), 'dairy'=>array(), 'instant'=>array(), 'dry'=>array(), 'bread'=>array(), 'other'=>array(), 'can'=>array());

    if ($res) {
      for ($i=0; $i< count($res); $i++) {
        $date = new DateTime();
        $date->modify('+' . $res[$i]['shelf_life'] . ' days');

        
        $res[$i]['expiresOn'] = $date->format('Y-m-d');
        $res[$i]['exp'] = array('month'=>$date->format('m'),'day'=>$date->format('d'),'year'=>$date->format('Y'));
        // Categorize this item accordingly
        array_push($output[$res[$i]['category']], $res[$i]);
      }

    }
    return $this->utilities->prep_response($output);
  }

  private function calc_days_left($exp) {
    $output = array();
    $curDate = time();
    // Break the date apart
    $exp = strtotime($exp);
    $month = date("m",$exp);
    $day = date("d",$exp);
    $year = date("Y",$exp);
    $output['exp'] = array('month'=>$month,'day'=>$day,'year'=>$year);
    $output['timeDiff'] = ceil(($exp - $curDate) / (60*60*24));
    if ($output['timeDiff'] > 31) {
      $output['daysLeft'] = ceil($output['timeDiff'] / 31) . ' mo';
    } else {
      $plural = $output['timeDiff'] > 1 ? 's' : '';
      $output['daysLeft'] = $output['timeDiff'] . ' day' . $plural;
    }

    if ($output['timeDiff'] < 2) {
      $output['flag'] = 'danger';
    } else if ($output['timeDiff'] < 7) {
      $output['flag'] = 'warning';
    } else {
      $output['flag'] = 'none';
    }
    
    return $output;
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
      // If updating expiration, we'll need to do some post processing
      if ($key == 'expires') {
        $ymd = DateTime::createFromFormat('Y-m-d', $value);
        $expires = $ymd->format('Y-m-d');
        $daysLeft = $this->calc_days_left($expires);
      }
    }
    $res = $this->gdao->queryRow($query);
    if ($daysLeft) {
      $output = array(
                      'message' => "$id successfully updated.",
                      'daysLeft' => $daysLeft['daysLeft'],
                      'flag' => $daysLeft['flag'],
                      /* 'expired' => $daysLeft[''], */
                      /* 'expires' => '', */
                      'exp' => $daysLeft['exp']
                      );
    } else {
      $output = "$id successfully updated.";
    }

    return $this->utilities->prep_response($output);
  }

}