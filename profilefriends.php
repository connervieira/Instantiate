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
                <a class="button" href="./profileview.php?profile=<?php echo $_GET["profile"]; ?>">Back</a>
            </div>
            <div class="right">
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>Profile Following</h2>
                <?php
                echo "<h3><b>$selected_profile</b>'s Friends</h3>";
                ?>
            </div>
            <div>
                <a href="https://v0lttech.com"><img class="logo" src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com/<?php echo $selected_profile; ?>"><img class="logo" src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <div>
            <?php
            $profiles = array_diff(scandir($instantiate_config["archive"]["path"]), array(".", ".."));
            if (in_array($selected_profile, $profiles)) {
                $profile_file_path = $instantiate_config["archive"]["path"] . "/" . $selected_profile;
                if (is_dir($profile_file_path)) { // Only continue if this file-path is a directory.
                    $profile_id = trim(file_get_contents($profile_file_path . "/id"));
                    $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));
                    asort($profile_files);

                    $followers = array();
                    $following = array();
                    foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
                        if (str_ends_with($profile_file, $profile_id . ".json.xz")) {
                            
                            $profile_instaloader_data = json_decode(shell_exec("xzcat \"" . $profile_file_path . "/" . $profile_file . "\""), true);
                        } else if (str_ends_with($profile_file, "followers.txt")) {
                            $followers = array_filter(explode("\n", file_get_contents($profile_file_path .  "/" . $profile_file)));
                        } else if (str_ends_with($profile_file, "following.txt")) {
                            $following = array_filter(explode("\n", file_get_contents($profile_file_path .  "/" . $profile_file)));
                        }
                    }

                    if (isset($profile_instaloader_data)) {
                        echo "<p><a href=\"profilefriends.php?profile=$selected_profile&type=followers\"><b>" . $profile_instaloader_data["node"]["edge_followed_by"]["count"] . "</b> followers</a> / <a href=\"profilefriends.php?profile=$selected_profile&type=following\"><b>" . $profile_instaloader_data["node"]["edge_follow"]["count"] . "</b> following</a></p>";
                    }
                }
            }
            ?>
            <hr>
            <?php
            if ($_GET["type"] == "followers") {
                echo "<h3>Followers:</h3><br>";
                $list = $followers;
            } else if ($_GET["type"] == "following") {
                echo "<h3>Following:</h3><br>";
                $list = $following;
            } else {
                echo "<p class=\"error\">Invalid friend type selected.</p>";
                exit();
            }
            asort($list);

            if (sizeof($list) == 0) {
                echo "<p><i>No accounts are recorded for " . $selected_profile . ".</i></p>";
            } else {
                $displayed_friends = 0;
                // On the first loop, only show users who are in the archive.
                foreach ($list as $user) {
                    if (in_array($user, $profiles) == true) {
                        echo "<a href=\"profileview.php?profile=$user\">$user</a><br>";
                        $displayed_friends++;
                    }
                }

                // On the first loop, only show users who are *not* in the archive.
                foreach ($list as $user) {
                    if (in_array($user, $profiles) == false) {
                        echo "<span style=\"opacity:0.5;\">$user</span><br>";
                        $displayed_friends++;
                    }
                }
                if (isset($profile_instaloader_data)) {
                    $total = 0;    
                    if ($_GET["type"] == "followers") {
                        $total = $profile_instaloader_data["node"]["edge_followed_by"]["count"];
                    } else if ($_GET["type"] == "following") {
                        $total = $profile_instaloader_data["node"]["edge_follow"]["count"];
                    }
                    if ($total - $displayed_friends > 0) {
                        echo "<p title=\"There are " . $total - $displayed_friends . " more accounts that aren't recorded in the log.\">" . $total - $displayed_friends . " more</p>";
                    }
                }
            }

            ?>
        </div>
    </body>
</html>
