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
                        break; // Exit the loop, since the profile photo was found.
                    }
                }
                if ($profile_photo_data == "") {
                    $profile_photo_data = "./assets/img/icons/avatar.svg";
                }
                echo "<div class='profile_card'>";
                echo "    <h3><img src='" . $profile_photo_data . "'><a href='./profileview.php?profile=$profile'>" . $profile . "</a></h3>";
                echo "</div>";
            }
        }
        ?>
    </body>
</html>
