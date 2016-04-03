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
}