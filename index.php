<?php
include "./config.php";

include $instantiate_config["auth"]["provider"]["core"];

if (in_array($username, $instantiate_config["auth"]["access"]["admin"]) == false and $username != "") {
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
                    if (in_array($username, $instantiate_config["auth"]["access"]["admin"]) == true) {
                        echo "<a class=\"button\" href=\"./configure.php\">Configure</a>";
                    }
                    echo "<a class=\"button\" href=\"./account.php\">Account</a>";
                    echo "<a class=\"button\" href=\"" . $instantiate_config["auth"]["provider"]["signout"] . "\">Sign Out</a>";
                } else { // Otherwise, the user is not signed in.
                    echo "<a class=\"button\" href=\"" . $instantiate_config["auth"]["provider"]["signin"] . "\">Sign In</a>";
                }
                ?>
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
            </div>
            <div>
                <a href="https://v0lttech.com"><img src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com"><img src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <hr>
        <?php
        $profile_photos = array();
        if (isset($username) and $_SESSION["authid"] == "dropauth") { // Check to see if the user is logged in.
            $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
            if (sizeof($instantiate_database[$username]["following"]) > 0) { // Check to see if this user is following at least one profile.
                foreach (array_keys($instantiate_database[$username]["following"]) as $profile) { // Iterate over each profile this user is following.
                    if (in_array($profile, $profiles)) { // Check to see if this profile is in the list of valid profiles.
                        $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $profile;
                        if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                            $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));
                            asort($profile_files);

                            $profile_photo_data = ""; // Set the profile photo data to a blank placeholder.
                            foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
                                if (strpos($profile_file, "profile_pic")) { // Check to see if this file is the profile photo.
                                    $profile_photo_filepath = $instantiate_config["archive"]["path"] . "/" . $profile . "/" . $profile_file; // Set the profile photo filepath to this file.
                                    if (substr($profile_photo_filepath, 0, 2) == "./" or substr($profile_photo_filepath, 0, 3) == "../") { // Check to see if this image path is relative to the webpage.
                                        $profile_photo_data = $profile_photo_filepath;
                                    } else { // Otherwise, assume this image path is an absolute path outside of the webpage directory.
                                        $profile_photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($profile_photo_filepath));
                                    }
                                }

                                $profile_file_cleaned = str_replace("_UTC", "", $profile_file);
                                $file_timestamp_human = str_replace("-", "", str_replace("_", " ", substr($profile_file_cleaned, 0, 19)));
                                $file_timestamp_unix = strtotime($file_timestamp_human);

                                if ($file_timestamp_unix > 0) {
                                    if (isset($posts[$file_timestamp_unix][$profile]["is_story"]) == false) {
                                        $posts[$file_timestamp_unix][$profile]["is_story"] = true; // Assume this post is a story until we find information that suggests otherwise.
                                    }
                                    $file_extension = strtolower(pathinfo($profile_file, PATHINFO_EXTENSION));
                                    if (strtolower(pathinfo($profile_file)["extension"]) == "txt") {
                                        $posts[$file_timestamp_unix][$profile]["description"] = file_get_contents($profile_file_path . "/" . $profile_file);
                                        $posts[$file_timestamp_unix][$profile]["is_story"] = false; // This post can not be a story because it has an associated text file.
                                    } else if (in_array($file_extension, array("jpg", "jpeg", "webp", "png", "m4v", "mp4", "webm"))) {
                                        $slide_id = intval(end(explode("_", pathinfo($profile_file, PATHINFO_FILENAME))));
                                        if ($slide_id > 0) { // Check to see if this post has slides.
                                            $posts[$file_timestamp_unix][$profile]["is_story"] = false; // This can not be a story.
                                        }
                                        if (isset($posts[$file_timestamp_unix][$profile]["images"]) and in_array($slide_id, array_keys($posts[$file_timestamp_unix][$profile]["images"]))) { // Check to see if there is already an entry for this slide in the post data.
                                            if (in_array($file_extension, array("m4v", "mp4", "webm"))) { // Only overwrite the existing file for this slide if this file is a video.
                                                $posts[$file_timestamp_unix][$profile]["images"][$slide_id] = $profile_file_path . "/" . $profile_file;
                                            }
                                        } else { // Otherwise, this slide needs to be created.
                                            $posts[$file_timestamp_unix][$profile]["images"][$slide_id] = $profile_file_path . "/" . $profile_file;
                                        }
                                    }
                                }
                            }
                            if ($profile_photo_data == "") { $profile_photo_data = "./assets/img/icons/avatar.svg"; }
                            $profile_photos[$profile] = $profile_photo_data;
                        }
                    }
                }


                // Remove stories from the list of posts (if configured to do so).
                if ($instantiate_config["behavior"]["show_stories"] == false) {
                    foreach (array_keys($posts) as $timestamp) {
                        foreach (array_keys($posts[$timestamp]) as $profile) { // Iterate over each user associated with this timestamp (usually just 1).
                            if ($posts[$timestamp][$profile]["is_story"] == true) { // Check to see if this post is a story.
                                unset($posts[$timestamp][$profile]); // Remove this post.
                                if (sizeof($posts[$timestamp]) == 0) { // Check to see if this timestamp is now empty.
                                    unset($posts[$timestamp]); // Remove this timestamp.
                                }
                            }
                        }
                    }
                }


                ksort($posts); // Sort the posts in chronological order.
                $posts = array_reverse($posts, true); // Reverse the order of the posts so that the most recent posts are first.

                $page_number = max([1, intval($_GET["pg"])]);
                $starting_post = ($page_number-1) * $instantiate_config["behavior"]["posts_per_page"]; // This is the index of the first post that will be displayed.
                $ending_post = $starting_post + $instantiate_config["behavior"]["posts_per_page"]; // This determines the index of the last post that will be displayed.
                $displayed_posts = 0; // This will count the post indexes.
                if ($starting_post > 0) {
                    echo "<a class=\"button\" href=\"?pg=" . $page_number - 1 . "\">Previous Page</a>";
                } else {
                    echo "<a class=\"button disabled\">Previous Page</a>";
                }
                if (sizeof($posts) > $ending_post) {
                    echo "<a class=\"button\" href=\"?pg=" . $page_number + 1 . "\">Next Page</a>";
                } else {
                    echo "<a class=\"button disabled\">Next Page</a>";
                }
                foreach (array_keys($posts) as $timestamp) { // Iterate over each post timestamp in the array.
                    foreach (array_keys($posts[$timestamp]) as $profile) { // Iterate over each user associated with this timestamp (usually just 1).
                        if ($displayed_posts >= $starting_post and $displayed_posts < $ending_post) { // Check to see if this post is in the expected range.
                            echo "<div class='post_card'>";
                            echo "    <a href=\"./profileview.php?profile=" . $profile . "\"><div class='post_header'>";
                            echo "         <img class=\"avatar\" src='" . $profile_photos[$profile] . "'><h4>" . $profile . "</h4>";
                            echo "    </div>";
                            echo "    <hr>";
                            echo "    <div>";
                            foreach ($posts[$timestamp][$profile]["images"] as $image) {
                                if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), array("jpg", "jpeg", "webp", "png"))) {
                                    if (substr($image, 0, 2) == "./" or substr($image, 0, 3) == "../") { // Check to see if this image path is relative to the webpage.
                                        $photo_data = $image;
                                    } else { // Otherwise, assume this image path is an absolute path outside of the webpage directory.
                                        $photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($image));
                                    }
                                    echo "<a href='" . $photo_data . "' target='_blank'><img src='" . $photo_data . "'></a>";
                                } else if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), array("mp4", "m4v", "webm"))) {
                                    if (filesize($image) < 10**7) { // Check to see if this file is less than 10MB.
                                        if (substr($image, 0, 2) == "./" or substr($image, 0, 3) == "../") { // Check to see if this image path is relative to the webpage.
                                            $photo_data = $image;
                                        } else {
                                            $photo_data = "data:video/mp4;base64, " . base64_encode(file_get_contents($image));
                                        }
                                        echo "<a href='" . $photo_data . "' target='_blank'><video autoplay loop muted src='" . $photo_data . "'></a>";
                                    } else {
                                        echo "<span><i>Excessive file size</i></span>";
                                    }
                                }
                            }
                            echo "    </div>";
                            echo "    <p>" . nl2br($posts[$timestamp][$profile]["description"]) . "</p>";
                            echo "    <p><i>" . date("Y-m-d H:i:s", $timestamp + $instantiate_config["region"]["timezone_offset"]*3600) . "</i></p>";
                            echo "</div>";
                        }
                        $displayed_posts += 1;
                    }
                }
                if ($ending_post < sizeof($posts)) {
                    if ($starting_post > 0) {
                        echo "<a class=\"button\" href=\"?pg=" . $page_number - 1 . "\">Previous Page</a>";
                    } else {
                        echo "<a class=\"button disabled\">Previous Page</a>";
                    }
                    if (sizeof($posts) > $ending_post) {
                        echo "<a class=\"button\" href=\"?pg=" . $page_number + 1 . "\">Next Page</a>";
                    } else {
                        echo "<a class=\"button disabled\">Next Page</a>";
                    }
                } else {
                    echo "<p>There are no more posts to display.</p>";
                }
            } else {
                echo "<p>You are not currently following any profiles.</p>";
                echo "<a class=\"button\" href=\"./profilelist.php\">Explore</a>";
            }
        } else {
            echo "<p>You are not currently signed in.</p>";
            echo "<p>Sign in to view your feed.</p>";
            echo "<a class=\"button\" href=\"" . $instantiate_config["auth"]["provider"]["signin"] . "?redirect=/instantiate/\">Sign In</a>";
        }
        ?>
    </body>
</html>
