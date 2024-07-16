<?php
include "./config.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
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
        $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
        foreach ($profiles as $profile) {
            $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $profile;
            if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));

                $profile_photo_data = ""; // Set the profile photo data to a blank placeholder.
                foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
                    if (strpos($profile_file, "profile_pic")) { // Check to see if this file is the profile photo.
                        $profile_photo_filepath = $instantiate_config["archive"]["path"] . "/" . $profile . "/" . $profile_file; // Set the profile photo filepath to this file.
                        $profile_photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($profile_photo_filepath));
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
