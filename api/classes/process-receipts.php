<?php

class processReceipts{
  public function __construct() {
    // Generic DAO Class
    $this->gdao = new genericDAO();
    // Utilities Class
    $this->utilitiesCLASS = new utilities();
  }
  public function extractText($image_url, $debug) {

    error_reporting(E_ALL ^ E_NOTICE); // Silence notices
    
    $grocr = 'http://local.grocr.com:5000/v1/ocr';
    $ch = curl_init($grocr);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('image_url'=> SITE_URL . $image_url)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $res = curl_exec($ch);
    curl_close($ch);


    if ($res) {
      $ocrData = json_decode($res);

      if (array_key_exists('error', $ocrData)) {
        header('Content-Type: application/json');
        echo ($res);
        return $res;
      }
      
      // ////////////////////////////////////////////
      // NEED TO ARCHIVE THE IMAGE HERE... 
      // CHECKED FILE PERMISSIONS
      // CHECKED PATH VALIDITY
      // NOT WORKING
      // ////////////////////////////////////////////
      /* error_log(file_exists("/home/jgooch/smart-grocery" . $image_url)); */
      /* rename("/home/jgooch/smart-grocery" . $image_url, IMG_ARCHIVE_PATH); */

      if ($debug) {
        if ($debug['ocr']) {
          error_log(print_r($ocrData->output, 1));
          return;
        }
      }

      // Break the results apart
      $itmList = explode("\n", $ocrData->output);
      $clnList = array(); // For final output
      
      for ($i = 0; $i < count($itmList); $i++) {
        $clnList[$i] = array(); // For this row
        $words = explode(" ", $itmList[$i]);
        // Make sure we have more than one word on this row. If there is only
        // one word, this is likely a serial number or gibbirish so we dont 
        // have to waste time looking in the DB for a match
        if (count($words) > 1) {
          for ($x = 0; $x < count($words); $x++) {
            $word = $words[$x];
            $word = trim($word);
            $preventQuantity = false;

            // Make sure we have at least 2 characters to work with
            if (strlen($word) > 1) {
              // Find matches against words first
              $label = $this->gdao->mysql_sanitize($word);
              $query = "SELECT id FROM spellings WHERE label = '$label'";
              $wordMatch = $this->gdao->queryOne($query);

              if ($wordMatch) {
                $clnList[$i][$x] = $label;
              } else {
                $swapQuery = "SELECT s.label FROM spellings AS s LEFT JOIN spellings_alternatives_ref AS sa ON s.id=sa.spelling_id WHERE sa.alt_spelling = '$label';";
                $swapWord = $this->gdao->queryOne($swapQuery);
                if ($swapWord) {
                  $clnList[$i][$x] = $swapWord;
                } else {
                  // Make sure a quantity doesn't accidentally get stuck to this empty record
                  /* $clnList[$i][$x] = 'DELETED'; */
                }
              }

              // See if we can find any quantities for our items
              $quantity = 1;
              if (is_numeric($words[0])) {
                // This might be a quantity
                if ($words[1] == 'Ea.' || $words[1] == 'Ea') {
                  $quantity = $words[0];
                }
              }
            }
          } // end FOR ($words)
        
          // Lets find who the quantity belongs to... sometimes the previous array was an empty line,
          // so we should also check 2 previous entries. We'll stick the quantity on the end
          if (count($clnList[$i - 1]) > 0) {


            $lastChar = $clnList[$i-1][count($clnList[$i-1]) - 1];
            if ($lastChar == '') { $lastChar = $clnList[$i-1][count($clnList[$i-1]) - 2]; }
            /* error_log("$i), i'm sticking $quantity on $lastChar for the row " . */
            /*           $clnList[$i-1][0] . " " . $clnList[$i-1][1]); */
            

            if (!is_numeric($lastChar)) {
              /* array_push($clnList[$i - 1],$quantity); */
            }

            
          } elseif (count($clnList[$i - 2]) > 0) {

            $lastChar = $clnList[$i-2][count($clnList[$i-2]) - 1];
            if ($lastChar == '') { $lastChar = $clnList[$i-2][count($clnList[$i-2]) - 2]; }

            /* error_log("$i), i'm sticking $quantity on $lastChar for the row " . */
            /*           $clnList[$i-2][0] . " " . $clnList[$i-2][1]); */
            
            if (!is_numeric($lastChar)) {
              /* array_push($clnList[$i - 2],$quantity); */
            }
          }

        } // end IF count($words > 1)
      }

      // Try to locate the store name
      $store = "Unknown";
      if ($this->utilitiesCLASS->in_array_r("HEB", $clnList)) { $store = "HEB"; }

      // Get the next receipt id
      $ridQuery = "(SELECT MAX(receipt_id) FROM receipts)";
      $rid = $this->gdao->queryOne($ridQuery);
      $rid++;
      // Add the new receipt
      $newReceiptQuery = "INSERT INTO receipts (receipt_id, scan_date, location, processed) VALUES ($rid,now(),'$store',0);";
      $newReceipt = $this->gdao->queryOne($newReceiptQuery);
      

      // Figure out what we bought by looking in the inventory_items and ref tables
      $purchasedItems = array();
      for ($i = 0; $i < count($clnList); ++$i) {
        if (count($clnList[$i]) > 0) {
          $row = array_values($clnList[$i]); // Drop empty items
          $sentence = '';
          for ($x = 0; $x < count($row); ++$x) {
            $sentence .= $row[$x];
            if ($x != count($row)) {
              // Add a space
              $sentence .= " ";
            }
          }
          /* error_log($i . ') ' . $sentence); */
          // Try to match this against an inventory item
          $label = $this->gdao->mysql_sanitize($sentence);

          $invItmRefQuery = "SELECT inventory_items_id FROM inventory_item_ref where label = '$label'";
          $getInvItemId = $this->gdao->queryOne($invItmRefQuery);
          
          if ($getInvItemId) {
            $shelfLifeQuery = "SELECT shelf_life FROM inventory_items WHERE id = '$getInvItemId'";
            $shelfLife = $this->gdao->queryOne($shelfLifeQuery);

            if ($shelfLife != NULL) {
              $expDate = "DATE_ADD(now(), INTERVAL $shelfLife DAY)";
            } else {
              $expDate = NULL;
            }
            
            // Insert the item into the inventory
            $addItemQuery = "INSERT INTO receipt_items_ref (receipt_id, expires, freezer, units, inventory_item_id, reviewed) SELECT $rid, $expDate, freezer, units, id, 0 FROM inventory_items WHERE id = '$getInvItemId';";
            $addItem = $this->gdao->queryOne($addItemQuery);
          }
        }
      }


      
      
      unset($gdao);




      
      //Send JSON Response
      /* header('Content-Type: application/json'); */
      /* echo ($res); */
      /* break; */
    }
    
  }


  public function extractQuantity($rcpt) {
    error_log('im going fishing for quantities...');
  }

  

  }