<?php
$config_file = "./config.json";

function save_config($config) {
    global $config_file;
    file_put_contents($config_file, json_encode($config, (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)));
}

if (is_file($config_file) == false) {
    $instantiate_config = array();
    $instantiate_config["branding"]["product_name"] = "Instantiate";

    $instantiate_config["archive"]["path"] = "./InstagramPersonal/";
    $instantiate_config["behavior"]["posts_per_page"] = 10;
    $instantiate_config["behavior"]["show_stories"] = true;

    $instantiate_config["region"]["timezone_offset"] = -4;

    $instantiate_config["auth"]["access"]["whitelist"] = [];
    $instantiate_config["auth"]["access"]["blacklist"] = [];
    $instantiate_config["auth"]["access"]["admin"] = ["admin"];
    $instantiate_config["auth"]["access"]["mode"] = "blacklist";
    $instantiate_config["auth"]["provider"]["core"] = "../dropauth/authentication.php";
    $instantiate_config["auth"]["provider"]["signin"] = "../dropauth/signin.php";
    $instantiate_config["auth"]["provider"]["signout"] = "../dropauth/signout.php";
    $instantiate_config["auth"]["provider"]["signup"] = "../dropauth/signup.php";

    save_config($instantiate_config);
}
if (is_file($config_file)) {
    $instantiate_config = json_decode(file_get_contents($config_file), true);
} else {
    echo "<p>Failed to load configuration file.</p>";
    exit();
}



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
