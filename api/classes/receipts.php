<?php
  // -----
  // Type: Class [receipts]
  // Filename: receipts.php
  // Author: Jeremy Gooch
  //
  // The receipts class is used for retrieving the scanned receipts
  //
  // -----
class receipts {
  function __construct(){
    // Generic DAO
    $this->gdao = new genericDAO();
    // Utilities
    $this->utilities = new utilities();
  }

  public function get_new_receipts(){
    $query = "SELECT COUNT(*) AS new_receipt_count FROM receipts WHERE processed = 0";
    $res = $this->gdao->queryRow($query);

    return $this->utilities->prep_response($res);
  }

  public function get_all_scans(){
    $out = array();
    $out['new'] = array();
    $out['old'] = array();
    
    $query = "SELECT * FROM receipts;";
    $res = $this->gdao->queryAll($query);

    foreach ($res as $receipt => $value) {
      if ($value['processed'] == 0) {
        // NEW RECEIPT

        // Since this is a new receipt, lets get it's various items for reviewing
        $rid = $value['receipt_id'];
        $indQuery = "SELECT *, r.id FROM receipt_items_ref AS r LEFT JOIN inventory_items AS i ON r.inventory_item_id=i.id WHERE r.receipt_id = $rid;";
        $indRes = $this->gdao->queryAll($indQuery);

        // Break the expiration information apart
        for ($i=0; $i < count($indRes); $i++) {
          $month = date("m",strtotime($indRes[$i]['expires']));
          $day = date("d",strtotime($indRes[$i]['expires']));
          $year = date("Y",strtotime($indRes[$i]['expires']));
          
          $indRes[$i]['exp'] = array('month'=>$month,'day'=>$day,'year'=>$year);
        }
        
        $value['receipt_data'] = $indRes;

        // Add the item as a new receipt
        array_push($out['new'], $value);
      } else {
        // OLD RECEIPT
        
        // Format the date
        $scan_date = date_create($value['scan_date']);
        $value['scan_date'] = date_format($scan_date, "M d, Y");
        
        // Add the item as an old receipt
        array_push($out['old'], $value);
      }
    }
    
    return $this->utilities->prep_response($out);
  }

  public function delete_item($id){
    $query = "DELETE FROM receipt_items_ref WHERE id = '$id';";
    $res = $this->gdao->queryRow($query);

    return $this->utilities->prep_response("$id successfully deleted.");
  }


  public function save_item($id, $inventory_item_id, $quantity, $units = null, $expires, $category, $freezer){
    // See if we already have one of these items in the inventory
    $query = "SELECT * FROM inventory WHERE inventory_item_id = '$inventory_item_id';";
    $res = $this->gdao->queryAll($query);

    
    if (count($res) > 0) {
      foreach($res as $item) { 
        // Update the first match we find in the inventory
        if ($item['units'] == $units && $item['freezer'] == $freezer) {
          $updateQry = "UPDATE inventory SET quantity = '" . ($item['quantity'] + $quantity) . "', expires = '$expires'";
          $updateRes = $this->gdao->queryExec($updateQry);
          break;
        }
      }
    } else {
      // No existing items were found
      $expiresDate = new DateTime($expires);
      $updateQry = "INSERT INTO inventory (receipt_id, inventory_item_id, quantity, units, purchase_date, expires, cooked, expired, category, freezer)
       SELECT receipt_id, '$inventory_item_id', '$quantity', '$units', CURDATE(), '" . $expiresDate->format('Y-m-d') . "', 0, 0, '$category', '$freezer' FROM receipt_items_ref
       WHERE id = $id;";
      $updateRes = $this->gdao->queryExec($updateQry);
    }
    
    if($updateRes) {
      $this->delete_item($id);
      
      return $this->utilities->prep_response("$id successfully saved.");
    } else {
      return $this->utilities->prep_response("$id could not be saved at this time.", 401);
    }

  }

  public function archive_receipt($id) {
    $query = "UPDATE receipts SET processed = '1' WHERE id = '$id';";
    $res = $this->gdao->queryAll($query);

    return $this->utilities->prep_response("$id successfully archived.");
  }
}