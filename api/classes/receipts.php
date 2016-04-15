<?php
  // -----
  // Type: Class [receiptsDAO (Database Abstraction Object)]
  // Filename: receiptsDAO.php
  // Author: Jeremy Gooch
  //
  // The receiptsDAO class is used for retrieving the scanned receipts
  //
  // -----
  //Load Config File
  //include_once("../../config/hr_config.php");
class receiptsDAO {
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


  public function save_item($id, $inventory_item_id, $quantity, $units = null, $expires){
    $query = "INSERT INTO inventory (receipt_id, inventory_item_id, quantity, units, purchase_date, expires, cooked, expired)
       SELECT receipt_id, '$inventory_item_id', '$quantity', '$units', CURDATE(), '$expires', 0, 0 FROM receipt_items_ref 
       WHERE id = $id;";
    $res = $this->gdao->queryExec($query);


    if($res) {
      $this->delete_item($id);
    }

    return $this->utilities->prep_response("$id successfully saved.");
  }
}