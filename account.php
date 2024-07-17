<?php
include "./config.php";

$force_login_redirect = true;
include $instantiate_config["auth"]["provider"]["core"];

if (in_array($username, $instantiate_config["auth"]["access"]["admin"]) == false) {
    if ($instantiate_config["auth"]["access"]["mode"] == "whitelist") {
        if (in_array($username, $instantiate_config["auth"]["access"]["whitelist"]) == false) { // Check to make sure this user is not in blacklist.
            echo "<p>You are not permitted to access this utility.</p>";
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
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <div class="left">
                <a class="button" href="./index.php">Back</a>
            </div>
            <div class="right">
                <a class="button" href="./followall.php">Follow All</a>
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>Manage Account</h2>
            </div>
            <div>
                <a href="https://v0lttech.com"><img src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com"><img src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <hr>
        <?php
        $instantiate_database = load_database();
        if (isset($instantiate_database[$username]) == false) { // Check to see if the current user does not yet exist in the instantiate database.
            // Initialize this user in the database.
            $instantiate_database[$username] = array();
            $instantiate_database[$username]["following"] = array();
        }
        ?>


        <h3>Following</h3>
        <?php
        $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
        if (sizeof($instantiate_database[$username]["following"]) > 0) { // Check to see if this user is following at least one profile.
            foreach (array_keys($instantiate_database[$username]["following"]) as $profile) { // Iterate over each profile this user is following.
                if (in_array($profile, $profiles)) { // Check to see if this profile is in the list of valid profiles.
                    $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $profile;
                    if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
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
                } else {
                    echo "<a href='./profileview.php?profile=$profile'><div class='profile_card'>";
                    echo "    <h3><img src='" . $profile_photo_data . "'>" . $profile . " (MISSING)</h3>";
                    echo "</div></a>";
                }
            }
        } else {
            echo "<p>You are not following any profiles.</p>";
            echo "<a class=\"button\" href=\"./profilelist.php\">Explore</a>";
        }
        ?>
    </body>
</html>
