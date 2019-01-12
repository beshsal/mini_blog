<?php
// This processes the entry submitted from the search form via AJAX.

require_once("db.inc.php");
include("util_funcs.inc.php");
session_start();

if (isset($_POST["searchterm"]) && !empty($_POST["searchterm"])) {
    // Remove any whitespace from the beginning and end of the entry.
    $trmdSt = trim($_POST["searchterm"]);
    // Format the entry for the URL, and send it back in the response.
    echo formatUrlStr($trmdSt);
}
?>