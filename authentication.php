<?php
session_start();
if ($_SESSION['authid'] == "dropauth") {
	$username = $_SESSION['username'];
} else {
    if ($force_login_redirect == true) {
        header("Location: " . $instantiate_config["auth"]["provider"]["signin"]);
        exit();
    }
}

?>

