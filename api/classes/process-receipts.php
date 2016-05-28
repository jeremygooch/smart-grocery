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
    
    $grocr = 'http://0.0.0.0:5000/v1/ocr';
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
      
      $this->logging($debug, 'ocr', $ocrData->output);

      // Break the results apart
      $itmList = explode("\n", $ocrData->output);
      $clnList = array(); // For final output

      // Remove empty spaces
      $collapsedList = array();
      for ($i = 0; $i < count($itmList); $i++) {
        if (trim($itmList[$i]) != '') {
          array_push($collapsedList, $itmList[$i]);
        }
      }

      $this->logging($debug, 'ocr', '==========STRING=REPLACEMENT===========');
      for ($i = 0; $i < count($collapsedList); $i++) {
        $clnList[$i] = array(); // For this row

        // See if we can find common groups of letters to form whole words before we
        // drill down to the individual words

        // Select all spelling alternatives with a space
        $multiWrdsQry = "SELECT * FROM spellings_alternatives_ref WHERE alt_spelling LIKE '% %'";
        $multiWrds = $this->gdao->queryAll($multiWrdsQry);

        foreach ($multiWrds as $multiWrd) {
          $altSpelling = $multiWrd['alt_spelling'];
          $match = strpos($collapsedList[$i], $altSpelling);
          if ($match) {
            // Get the correct spelling of the matched word
            $wordId = $multiWrd['spelling_id'];
            $matchedWrdQry = "SELECT label FROM spellings WHERE id = '$wordId';";
            $matchedWrd = $this->gdao->queryOne($matchedWrdQry);
            $collapsedList[$i] = str_replace($altSpelling,$matchedWrd,$collapsedList[$i]);
          }
        }

        
        
        $this->logging($debug, 'ocr', '=======================================');
        $words = explode(" ", $collapsedList[$i]);
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
                $this->logging($debug, 'ocr', $label);
              } else {
                $swapQuery = "SELECT s.label FROM spellings AS s LEFT JOIN spellings_alternatives_ref AS sa ON s.id=sa.spelling_id WHERE sa.alt_spelling = '$label';";
                $swapWord = $this->gdao->queryOne($swapQuery);
                if ($swapWord) {
                  $this->logging($debug, 'ocr', $swapWord);
                  $clnList[$i][$x] = $swapWord;
                } else {
                  /* error_log('I couldnt locate a match for ' . $clnList[$i][$x] . ' in the line ' . $words[0] . $words[1] . $words[2]); */
                  /* // Make sure a quantity doesn't accidentally get stuck to an empty record */
                  /* if ($x == (count($words) -1)) { */
                  /*   if (empty($clnList[$i])) { // See if there were any previous matches */
                  /*     error_log('I found an empty row ' . $words[0] . $words[1] . $words[2]); */
                  /*   } */
                  /* } */
                  
                  /* $clnList[$i][$x] = 'DELETED'; */
                }
              }
            } // End minimum word length

            // See if we can find any quantities for our items
            $quantity = 1;
            if (is_numeric($words[0])) {
              // This might be a quantity
              if ($words[1] == 'Ea.' || $words[1] == 'Ea') {
                $quantity = $words[0];
              }
            }
          } // end FOR ($words)
        
          // Lets find who the quantity belongs to... sometimes the previous array was an empty line,
          // so we should also check 2 previous entries. We'll stick the quantity on the end
          if (count($clnList[$i - 1]) > 0) {
            

            $lastChar = $clnList[$i-1][count($clnList[$i-1]) - 1];
            if ($lastChar == '') { $lastChar = $clnList[$i-1][count($clnList[$i-1]) - 2]; }
            
            if (!is_numeric($lastChar)) {
              array_push($clnList[$i - 1],$quantity);
            }

            
          }
        } // end IF count($words > 1)
      } // For loop on collapsed list
      
      // Try to locate the store name
      $store = "Unknown";
      if ($this->utilitiesCLASS->in_array_r("HEB", $clnList)) { $store = "HEB"; }
      elseif ($this->utilitiesCLASS->in_array_r("LUCERNE", $clnList)) { $store = "Randalls"; }
      elseif ($this->utilitiesCLASS->in_array_r("SIGNATURE", $clnList)) { $store = "Randalls"; }
      elseif ($this->utilitiesCLASS->in_array_r("MARKET PANT", $clnList)) { $store = "Target"; }
      elseif ($this->utilitiesCLASS->in_array_r("MP", $clnList)) { $store = "Target"; } // MP = Market Pantry

      // Add the new receipt
      $newReceiptQuery = "INSERT INTO receipts (scan_date, location, processed) VALUES (now(),'$store',0);";
      $newReceipt = $this->gdao->queryOne($newReceiptQuery);

      // Get the new receipt id
      $ridQuery = "SELECT MAX(id) FROM receipts";
      $rid = $this->gdao->queryOne($ridQuery);
      

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

          // Get the quantity off of the string
          $qty = filter_var($sentence, FILTER_SANITIZE_NUMBER_INT);
          $qty = ($qty < 1) ? $qty * -1 : $qty; // Convert to positive if necessary
          // Take the quantity out of the string
          $sentence = preg_replace('/\d/', '', $sentence );
          
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
            $addItemQuery = "INSERT INTO receipt_items_ref (receipt_id, expires, freezer, quantity, units, inventory_item_id) SELECT $rid, $expDate, freezer, $qty, units, id FROM inventory_items WHERE id = '$getInvItemId';";
            $addItem = $this->gdao->queryOne($addItemQuery);
          }
        }
      }
      unset($gdao);
    }
  }

  public function logging($debug, $type, $itm) {
    if ($debug) {
      if ($debug[$type]) {
        error_log(print_r($itm, 1));
        return;
      }
    }
  }


  public function extractQuantity($rcpt) {
    error_log('im going fishing for quantities...');
  }

  }