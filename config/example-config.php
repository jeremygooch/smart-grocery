<?php
  /*
   * ========================================
   * Example configuration File [Configuration File]
   * Save actual config file as config.php
   *
   * Author: Jeremy Gooch
   * ========================================
   *
   */

define("DB_SERVER", "XXXXXXXX");
define("DB_USER", "XXXXXXXX");
define("DB_PASS", "XXXXXXXX");
define("DB_NAME", "XXXXXXXX");



// ******************************************
// The site url (i.e. localhost)
// NOTE: Do not include a trailing slash
// ******************************************
define("SITE_URL", "XXXXXXXXXX");
define("SITE_RESOURCE_NAME", "XXXXXXXXXX");
define("IMG_ARCHIVE_PATH", "XXXXXXXXXX"); // Absolute Path



include_once("../api/DAO/genericDAO.php");
include_once("../api/classes/monitor.php");
include_once("../api/classes/process-receipts.php");