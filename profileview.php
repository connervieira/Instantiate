<?php
include "./config.php";

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

if (isset($username) and $_SESSION["authid"] == "dropauth") { // Check to see if the user is logged in.
    $instantiate_database = load_database();
    if (isset($instantiate_database[$username]) == false) { // Check to see if the current user does not yet exist in the instantiate database.
        // Initialize this user in the database.
        $instantiate_database[$username] = array();
        $instantiate_database[$username]["following"] = array();
    }
}

$selected_profile = $_GET["profile"];
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
                <a class="button" href="./profilelist.php">Explore</a>
            </div>
            <div class="right">
                <?php
                if (isset($username) and $_SESSION["authid"] == "dropauth") { // Check to see if the user is logged in.
                    if (in_array($selected_profile, array_keys($instantiate_database[$username]["following"]))) { // Check to see if the user is following this profile.
                        echo "<a class=\"button\" href=\"./profileunfollow.php?profile=" . $_GET["profile"] . "\">Unfollow</a>";
                    } else {
                        echo "<a class=\"button\" href=\"./profilefollow.php?profile=" . $_GET["profile"] . "\">Follow</a>";
                    }
                }
                ?>
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>View Profile</h2>
            </div>
            <div>
                <a href="https://v0lttech.com"><img src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com"><img src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <hr>
        <?php
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

                $posts = array_reverse($posts, true); // Reverse the array, such that newer posts appear first.

                $page_number = max([1, intval($_GET["pg"])]);
                $starting_post = ($page_number-1) * $instantiate_config["archive"]["posts_per_page"]; // This is the index of the first post that will be displayed.
                $ending_post = $starting_post + $instantiate_config["archive"]["posts_per_page"]; // This determines the index of the last post that will be displayed.
                $displayed_posts = 0; // This will count the post indexes.
                echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number - 1 . "\">Previous Page</a>";
                echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number + 1 . "\">Next Page</a>";
                foreach (array_keys($posts) as $timestamp) {
                    if ($displayed_posts >= $starting_post and $displayed_posts < $ending_post) { // Check to see if this post is in the expected range.
                        echo "<div class='post_card'>";
                        echo "    <div>";
                        foreach ($posts[$timestamp]["images"] as $image) {
                            if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), array("jpg", "jpeg", "webp", "png"))) {
                                if (substr($image, 0, 2) == "./") { // Check to see if this image path is relative to the webpage.
                                    $photo_data = $image;
                                } else { // Otherwise, assume this image path is an absolute path outside of the webpage directory.
                                    $photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($image));
                                }
                                echo "<a href='" . $photo_data . "' target='_blank'><img src='" . $photo_data . "'></a>";
                            } else if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), array("mp4", "m4v", "webm"))) {
                                if (filesize($image) < 10**7) { // Check to see if this file is less than 10MB.
                                    if (substr($image, 0, 2) == "./") { // Check to see if this image path is relative to the webpage.
                                        $photo_data = $image;
                                    } else { // Otherwise, assume this image path is an absolute path outside of the webpage directory.
                                        $photo_data = "data:video/mp4;base64, " . base64_encode(file_get_contents($image));
                                    }
                                    echo "<a href='" . $photo_data . "' target='_blank'><video autoplay loop muted src='" . $photo_data . "'></a>";
                                } else {
                                    echo "<span><i>Excessive file size</i></span>";
                                }
                            }
                        }
                        echo "    </div>";
                        echo "    <p>" . nl2br($posts[$timestamp]["description"]) . "</p>";
                        echo "    <p><i>" . date("Y-m-d H:i:s", $timestamp + $instantiate_config["locale"]["timezone_offset"]*3600) . "</i></p>";
                        echo "</div>";
                    }
                    $displayed_posts += 1;
                }
                echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number - 1 . "\">Previous Page</a>";
                echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number + 1 . "\">Next Page</a>";
            } else {
                echo "<p>Error: Invalid profile. The specified profile is not a directory.</p>";
            }
        } else {
            echo "<p>Error: Invalid profile. The specified profile is not archived.</p>";
        }
        ?>
    </body>
</html>
