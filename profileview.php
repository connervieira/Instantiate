<?php
include "./config.php";
include $instantiate_config["auth"]["provider"]["core"];
include "./utils.php";

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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <?php
                $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
                if (in_array($selected_profile, $profiles)) {
                    $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $selected_profile;
                    if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                        $profile_id = trim(file_get_contents($profile_file_path . "/id"));
                        $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));
                        asort($profile_files);
                        foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
                            if (str_ends_with($profile_file, $profile_id . ".json.xz")) {
                                
                                $profile_instaloader_data = json_decode(shell_exec("xzcat \"" . $profile_file_path . "/" . $profile_file . "\""), true);
                            } else if (str_ends_with($profile_file, "profile_pic.jpg")) {
                                $profile_avatar_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "setname.txt")) {
                                $profile_setname_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "nickname.txt")) {
                                $profile_nickname_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "name.txt")) {
                                $profile_realname_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "bio.txt")) {
                                $profile_bio_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "birthday.txt")) {
                                $profile_birthday_file = $profile_file_path . "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "sex.txt")) {
                                $profile_sex_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "race.txt")) {
                                $profile_race_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "religion.txt")) {
                                $profile_religion_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "followers.txt")) {
                                $profile_followers_file = $profile_file_path .  "/" . $profile_file;
                            } else if (str_ends_with($profile_file, "following.txt")) {
                                $profile_following_file = $profile_file_path .  "/" . $profile_file;
                            }
                        }

                        if (file_exists($profile_nickname_file)) {
                            $profile_name_file = $profile_nickname_file;
                        } else if (file_exists($profile_realname_file)) {
                            $profile_name_file = $profile_realname_file;
                        } else if (file_exists($profile_setname_file)) {
                            $profile_name_file = $profile_setname_file;
                        }


                        echo "<div style=\"height:100px\">";
                        if (file_exists($profile_avatar_file)) {
                            if (substr($profile_avatar_file, 0, 2) == "./") { // Check to see if this image path is relative to the webpage.
                                $profile_photo_data = $profile_avatar_file;
                            } else { // Otherwise, assume this image path is an absolute path outside of the webpage directory.
                                $profile_photo_data = "data:image/jpeg;base64, " . base64_encode(file_get_contents($profile_photo_filepath));
                            }
                            echo "<img class=\"avatar\" style=\"float:left;\" src=\"" . $profile_photo_data . "\">";
                        }
                        echo "<p";
                        if (file_exists($profile_nickname_file) and file_exists($profile_realname_file)) {
                            echo " title=\"" . file_get_contents($profile_realname_file) . "\"";
                        }
                        echo ">";
                        if (file_exists($profile_name_file)) {
                            echo "<b>" . file_get_contents($profile_name_file) . "</b>";
                        }
                        if (file_exists($profile_sex_file)) {
                            echo " (";
                            if (file_exists($profile_birthday_file)) {
                                $birthdate = array_filter(explode("\n", file_get_contents($profile_birthday_file)));
                                $dt = new DateTime($birthdate[0]);
                                $birthdate_timestamp = $dt->getTimestamp();

                                if (sizeof($birthdate) > 1) {
                                    $birthday_precision = intval(trim($birthdate[1], "+- "));
                                } else {
                                    $birthday_precision = 0;
                                }

                                $years_old = floor((time() - $birthdate_timestamp) / 31557600);
                                $days_left_over = (((time() - $birthdate_timestamp)/31557600) - $years_old)*365; // This measures the days past the birthday (remainder).
                                $days_until = abs((((time() - $birthdate_timestamp)/31557600) - $years_old-1)*365); // This measures the days until the next birthday.
                                $distance_from_birthday = min($days_left_over, $days_until); // Pick whichever direction is closer to the birthday.
                                echo "<span";
                                if ($birthday_precision > $distance_from_birthday) {
                                    echo " title=\"" . $birthdate[0] . " " . $birthdate[1] . " days\"";
                                } else if ($birthday_precision == 0) {
                                    echo " title=\"" . $birthdate[0] . "\"";
                                }
                                echo ">";
                                if ($birthday_precision > $distance_from_birthday) {
                                    echo "~";
                                }
                                echo $years_old . "</span>";
                            }
                            echo trim(file_get_contents($profile_sex_file));
                            if (file_exists($profile_religion_file)) {
                                if (trim(strtolower(file_get_contents($profile_religion_file))) == "christianity") {
                                    echo " <span title=\"Christian\">✝</span>";
                                } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "islam") {
                                    echo " <span title=\"Muslim\">☪︎</span>";
                                } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "judaism") {
                                    echo " <span title=\"Jewish\">✡</span>";
                                } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "buddhism") {
                                    echo " <span title=\"Buddist\">☯︎</span>";
                                } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "hinduism") {
                                    echo " <span title=\"Hindu\">࿗</span>";
                                } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "atheism") {
                                    echo " <span title=\"Athiest\">⚛︎</span>";
                                }
                            }
                            echo ")";
                        }
                        echo "</p>";
                        if (file_exists($profile_bio_file)) {
                            echo "<p class=\"profile_bio\">" . nl2br(file_get_contents($profile_bio_file)) . "</p>";
                        }
                        echo "</div>";
                        if (isset($profile_instaloader_data)) {
                            echo "<p><a href=\"profilefriends.php?profile=$selected_profile&type=followers\"><b>" . $profile_instaloader_data["node"]["edge_followed_by"]["count"] . "</b> followers</a> / <a href=\"profilefriends.php?profile=$selected_profile&type=following\"><b>" . $profile_instaloader_data["node"]["edge_follow"]["count"] . "</b> following</a></p>";
                        }
                        echo "<br><a class=\"button\" href=\"profilestories.php?profile=$selected_profile\">View Stories</a><br><br>";
                    }
                }
                ?>
            </div>
            <div>
                <a href="https://v0lttech.com"><img class="logo" src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com/<?php echo $selected_profile; ?>"><img class="logo" src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <hr>
        <?php
        if (in_array($selected_profile, $profiles)) {
            if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                $posts = fetch_posts($profile_file_path);

                // Remove stories from the list of posts.
                foreach (array_keys($posts) as $timestamp) {
                    if ($posts[$timestamp]["is_story"] == true) { // Check to see if this post is a story.
                        unset($posts[$timestamp]); // Remove this post.
                    }
                }


                $posts = array_reverse($posts, true); // Reverse the array, such that newer posts appear first.

                $page_number = max([1, intval($_GET["pg"])]);
                $starting_post = ($page_number-1) * $instantiate_config["behavior"]["posts_per_page"]; // This is the index of the first post that will be displayed.
                $ending_post = $starting_post + $instantiate_config["behavior"]["posts_per_page"]; // This determines the index of the last post that will be displayed.
                $displayed_posts = 0; // This will count the post indexes.
                if ($starting_post > 0) {
                    echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number - 1 . "\">Previous Page</a>";
                } else {
                    echo "<a class=\"button disabled\">Previous Page</a>";
                }
                if (sizeof($posts) > $ending_post) {
                    echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number + 1 . "\">Next Page</a>";
                } else {
                    echo "<a class=\"button disabled\">Next Page</a>";
                }
                foreach (array_keys($posts) as $timestamp) {
                    if ($displayed_posts >= $starting_post and $displayed_posts < $ending_post) { // Check to see if this post is in the expected range.
                        echo "<div class='post_card'>";
                        echo "    <div>";
                        foreach ($posts[$timestamp]["images"] as $image) {
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
                        echo "    <p style=\"margin-bottom:-15px;opacity:0.5;\"><i>" . $posts[$timestamp]["location"] . "</i></p>";
                        echo "    <p><i>" . date("Y-m-d H:i:s", $timestamp + $instantiate_config["review"]["timezone_offset"]*3600);
                        if (file_exists($profile_birthday_file)) {
                            $years_old = floor(($timestamp - $birthdate_timestamp) / 31557600);
                            $days_left_over = ((($timestamp - $birthdate_timestamp)/31557600) - $years_old)*365; // This measures the days past the birthday (remainder).
                            $days_until = abs(((($timestamp - $birthdate_timestamp)/31557600) - $years_old-1)*365); // This measures the days until the next birthday.
                            $distance_from_birthday = min($days_left_over, $days_until); // Pick whichever direction is closer to the birthday.
                            if ($birthday_precision > $distance_from_birthday) {
                                echo " <span style=\"opacity:0.5;\" title=\"$selected_profile was approximately $years_old years old on this date\">~" . $years_old . "yo</span>";
                            } else {
                                echo " <span style=\"opacity:0.5;\" title=\"$selected_profile was $years_old years old on this date\">" . $years_old . "yo</span>";
                            }
                        }
                        echo "</i></p>";
                        echo "</div>";
                    }
                    $displayed_posts += 1;
                }
                if ($ending_post < sizeof($posts)) {
                    if ($starting_post > 0) {
                        echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number - 1 . "\">Previous Page</a>";
                    } else {
                        echo "<a class=\"button disabled\">Previous Page</a>";
                    }
                    if (sizeof($posts) > $ending_post) {
                        echo "<a class=\"button\" href=\"?profile=" . $selected_profile . "&pg=" . $page_number + 1 . "\">Next Page</a>";
                    } else {
                        echo "<a class=\"button disabled\">Next Page</a>";
                    }
                } else {
                    echo "<p>There are no more posts to display.</p>";
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
