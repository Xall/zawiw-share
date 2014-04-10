<?php
/*
Plugin Name: ZAWiW Share
Plugin URI:
Description: Einfaches Teilen von Dateien
Version: 1.0
Author: Simon Volpert
Author URI: http://svolpert.eu
License: GPLv2
*/


// Global to share messages after upload;
$zawiw_share_message = "";

// INCLUDES
require_once dirname( __FILE__ ) .'/database.php';
require_once dirname( __FILE__ ) .'/render.php';
require_once dirname( __FILE__ ) .'/upload.php';
// require_once dirname( __FILE__ ) .'/uninstall.php';


?>
