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
        <a class="button" href="./profilelist.php">Back</a>
        <?php
        $selected_profile = $_GET["profile"];

        $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
        if (in_array($selected_profile, $profiles)) {
            $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $selected_profile;
            if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));
                asort($profile_files);


                $posts = array();
                asort($profile_files);
                foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
                    $profile_file_cleaned = str_replace("_UTC", "", $profile_file);
                    $file_timestamp_human = str_replace("-", "", str_replace("_", " ", substr($profile_file_cleaned, 0, 19)));
                    $file_timestamp_unix = strtotime($file_timestamp_human);


                    if ($file_timestamp_unix > 0) {
                        $file_extension = strtolower(pathinfo($profile_file, PATHINFO_EXTENSION));
                        if (strtolower(pathinfo($profile_file)["extension"]) == "txt") {
                            $posts[$file_timestamp_unix]["description"] = file_get_contents($profile_file_path . "/" . $profile_file);
                        } else if (in_array($file_extension, array("jpg", "jpeg", "webp", "png", "m4v", "mp4", "webm"))) {
                            $slide_id = intval(end(explode("_", pathinfo($profile_file, PATHINFO_FILENAME))));
                            if (isset($posts[$file_timestamp_unix]["images"]) and in_array($slide_id, array_keys($posts[$file_timestamp_unix]["images"]))) { // Check to see if there is already an entry for this slide in the post data.
                                if (in_array($file_extension, array("m4v", "mp4", "webm"))) { // Only overwrite the existing file for this slide if this file is a video.
                                    $posts[$file_timestamp_unix]["images"][$slide_id] = $profile_file_path . "/" . $profile_file;
                                }
                            } else { // Otherwise, this slide needs to be created.
                                $posts[$file_timestamp_unix]["images"][$slide_id] = $profile_file_path . "/" . $profile_file;
                            }
                        }
                    }
                }

                $posts = array_reverse($posts, true);
                foreach (array_keys($posts) as $timestamp) {
                    echo "<div class='post_card'>";
                    echo "    <div>";
                    foreach ($posts[$timestamp]["images"] as $image) {
                        if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), array("jpg", "jpeg", "webp", "png"))) {
                            $photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($image));
                            echo "<a href='" . $photo_data . "' target='_blank'><img src='" . $photo_data . "'></a>";
                        } else if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), array("mp4", "m4v", "webm"))) {
                            if (filesize($image) < 10**7) { // Check to see if this file is less than 10MB.
                                $photo_data = "data:video/mp4;base64, " . base64_encode(file_get_contents($image));
                                echo "<a href='" . $photo_data . "' target='_blank'><video autoplay loop muted src='" . $photo_data . "'></a>";
                            } else {
                                echo "<span><i>Excessive file size</i></span>";
                            }
                        }
                    }
                    echo "    </div>";
                    echo "    <p>" . nl2br($posts[$timestamp]["description"]) . "</p>";
                    echo "    <p><i>" . date("Y-m-d H:i:s", $timestamp) . "</i></p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Error: Invalid profile. The specified profile is not a directory.</p>";
            }
        } else {
            echo "<p>Error: Invalid profile. The specified profile is not archived.</p>";
        }
        ?>
    </body>
</html>
