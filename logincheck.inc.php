<?php
require('conf/variables.php');

// Check if the user is logged in using the session, if not display the login screen for them and quit.
if (!isset($_SESSION['real-username'])) {
    require('top.php');
    echo "<h1>Access denied.</h1><br><p>Please <a href=\"index.php\">log in</a> to use this function. " .
    "Only members of the ladder can access this page. <a href=\"join.php\">Become one</a> and compete today!</p>";
    require('bottom.php');
    exit;
}

?>
