<?php
class utilities {
  
  function __construct(){
    //Generic DAO
    $this->gdao = new genericDAO();
  }

  public function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
      if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
        return true;
      }
    }

    return false;
  }

}