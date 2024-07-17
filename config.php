<?php
$instantiate_config = array();
$instantiate_config["branding"]["product_name"] = "Instantiate";
$instantiate_config["archive"]["path"] = "./instagram/";

$instantiate_config["locale"]["timezone_offset"] = -4;

$instantiate_config["auth"]["access"]["whitelist"] = [];
$instantiate_config["auth"]["access"]["blacklist"] = [];
$instantiate_config["auth"]["access"]["admin"] = ["admin"];
$instantiate_config["auth"]["access"]["mode"] = "blacklist";
$instantiate_config["auth"]["provider"]["core"] = "../dropauth/authentication.php";
$instantiate_config["auth"]["provider"]["signin"] = "../dropauth/signin.php";
$instantiate_config["auth"]["provider"]["signout"] = "../dropauth/signout.php";
$instantiate_config["auth"]["provider"]["signup"] = "../dropauth/signup.php";


function load_database() {
    global $instantiate_config;
    $database_filepath = $instantiate_config["archive"]["path"] . "/instantiate_data.json";

    if (file_exists($database_filepath) == false) { // Check to see if the database needs to be created.
        file_put_contents($database_filepath, "{}"); // Set the contents of the database file to the placeholder data.
    }

    if (file_exists($database_filepath) == true) { // Check to make sure the database has been created.
        $instantiate_data = json_decode(file_get_contents($database_filepath), true);
        return $instantiate_data ;
    } else {
        echo "<p>Failed to create database file.</p>";
        return false;
    }
}


function save_database($data) {
    global $instantiate_config;
    $database_filepath = $instantiate_config["archive"]["path"] . "/instantiate_data.json";

    $encoded_data = json_encode($data, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    if ($encoded_data == null or !isset($encoded_data) or strval($encoded_data) == "null") {
        echo "<p>Failed to encode data.</p>";
        exit();
    }
    if (!file_put_contents($database_filepath, $encoded_data)) { // Set the contents of the database file to the supplied data.
        echo "<p>Failed to save data.</p>";
        exit();
    }
}
?>
