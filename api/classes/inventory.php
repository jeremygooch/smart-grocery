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

  public function get_new_receipts(){
    $query = "SELECT * FROM inventory;";
    $res = $this->gdao->queryRow($query);

    return $this->utilities->prep_response($res);
  }
}