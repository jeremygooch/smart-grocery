<?php

class processReceipts{
  public function __construct() {
    $this->gdao = new genericDAO();
  }
  public function extractText($image_url) {
    
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


      /* error_log(print_r($ocrData->output, 1)); */
      // Break the results apart
      $itmList = explode("\n", $ocrData->output);
      $clnList = array(); // For final output
      
      for ($i = 0; $i < count($itmList); $i++) {
        $clnList[$i] = array(); // For this row
        $words = explode(" ", $itmList[$i]);
        // Make sure we have more than one word on this row. If there is only
        // one word, this is likely a serial number or gibbirish so we dont 
        // have to wast time looking in the DB for a match
        if (count($words) > 1) {
          for ($x = 0; $x < count($words); $x++) {
            $word = $words[$x];
            $word = trim($word);

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
                }
              }
            }
          }
        }
      }


      // Figure out what we bought
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
          // Try to match this against an inventory item
          $label = $this->gdao->mysql_sanitize($sentence);

          $invItmRefQuery = "SELECT inventory_items_id FROM inventory_item_ref where label = '$label'";
          $getInvItemId = $this->gdao->queryOne($invItmRefQuery);


          if ($getInvItemId) {
            $invItmQuery = "SELECT * FROM inventory_items WHERE id = '$getInvItemId'";
            $invItm = $this->gdao->queryAll($invItmQuery);




            error_log(print_r($invItm));
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

  }