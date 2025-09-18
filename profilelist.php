<?php
include "./config.php";

include $instantiate_config["auth"]["provider"]["core"];


if (isset($username) and $_SESSION["authid"] == "dropauth") { // Check to see if the user is logged in.
    $instantiate_database = load_database();
    if (isset($instantiate_database[$username]) == false) { // Check to see if the current user does not yet exist in the instantiate database.
        // Initialize this user in the database.
        $instantiate_database[$username] = array();
        $instantiate_database[$username]["following"] = array();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class="navbar">
            <div class="left">
                <a class="button" href="./">Back</a>
            </div>
            <div class="right">
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>List Profiles</h2>
            </div>
            <div>
                <a href="https://v0lttech.com"><img src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com"><img src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <hr>
        <?php
        if (in_array($username, $instantiate_config["auth"]["access"]["admin"]) == false) {
            if ($instantiate_config["auth"]["access"]["mode"] == "whitelist") {
                if (in_array($username, $instantiate_config["auth"]["access"]["whitelist"]) == false) { // Check to make sure this user is not in blacklist.
                    echo "<p>You are not authorized to view this page.</p>";
                    echo "<a class=\"button\" href=\"" . $instantiate_config["auth"]["provider"]["signin"] . "\">Sign In</a>";
                    exit();
                }
            } else if ($instantiate_config["auth"]["access"]["mode"] == "blacklist") {
                if (in_array($username, $instantiate_config["auth"]["access"]["blacklist"]) == true) { // Check to make sure this user is not in blacklist.
                    echo "<p>You are not permitted to access this utility.</p>";
                    exit();
                }
            } else {
                echo "<p>The configured access mode is invalid.</p>";
                exit();
            }
        }


        $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
        foreach ($profiles as $profile) {
            $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $profile;
            if (is_dir($profile_file_path) and is_readable($profile_file_path)) { // Only continue if this file-path is a directory.
                $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));

                $profile_photo_data = ""; // Set the profile photo data to a blank placeholder.
                foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
                    if (strpos($profile_file, "profile_pic")) { // Check to see if this file is the profile photo.
                        $profile_photo_filepath = $instantiate_config["archive"]["path"] . "/" . $profile . "/" . $profile_file; // Set the profile photo filepath to this file.
                        if (substr($profile_photo_filepath, 0, 2) == "./") { // Check to see if this image path is relative to the webpage.
                            $profile_photo_data = $profile_photo_filepath;
                        } else { // Otherwise, assume this image path is an absolute path outside of the webpage directory.
                            $profile_photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($profile_photo_filepath));
                        }
                    }
                }
                if ($profile_photo_data == "") {
                    $profile_photo_data = "./assets/img/icons/avatar.svg";
                }
                echo "<a href='./profileview.php?profile=$profile'><div class='profile_card'>";
                echo "    <h3><img src='" . $profile_photo_data . "'>" . $profile . "</h3>";
                echo "</div></a>";
            }
        }
        ?>
    </body>
</html>
