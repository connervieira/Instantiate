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
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>View Stories</h2>
                <?php
                $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
                if (in_array($selected_profile, $profiles)) {
                    $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $selected_profile;
                    if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                        echo "<h3>" . htmlspecialchars($selected_profile) . "'s stories</h3>";
                        echo "<br><a class=\"button\" href=\"profileview.php?profile=$selected_profile\">View Posts</a><br><br>";
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

                // Remove any post that isn't a story.
                foreach (array_keys($posts) as $timestamp) {
                    if ($posts[$timestamp]["is_story"] == false) { // Check to see if this post is a story.
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
                                if (filesize($image) < 50**7) { // Check to see if this file is less than 50MB.
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
                        echo "    <p><i>" . date("Y-m-d H:i:s", $timestamp + $instantiate_config["review"]["timezone_offset"]*3600) . "</i></p>";
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
