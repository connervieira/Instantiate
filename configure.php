<?php
include "./config.php";

$force_login_redirect = true;
include $instantiate_config["auth"]["provider"]["core"];

if (in_array($username, $instantiate_config["auth"]["access"]["admin"]) == false) {
    echo "<p>You are not permitted to access this utility.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?> - Configure</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class="navbar">
            <div class="left">
                <a class="button" href="./index.php">Back</a>
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>Configure</h2>
            </div>
        </div>
        <hr>
        <div>
            <?php
            if ($_POST["submit"] == "Submit") { // Check to see if the form has been submitted.
                echo "<div class=\"center\">";

                $valid = true; // Assume the configuration is valid until we find an invalid value.

                $instantiate_config["archive"]["path"] = $_POST["archive>path"];
                if (is_dir($instantiate_config["archive"]["path"]) == false) {
                    echo "<p class=\"error\">The specified archive path does not appear to exist.</p>";
                    $valid = false;
                }

                $instantiate_config["behavior"]["posts_per_page"] = intval($_POST["behavior>posts_per_page"]);
                if ($instantiate_config["behavior"]["posts_per_page"] <= 0 or $instantiate_config["behavior"]["posts_per_page"] >= 1000) {
                    echo "<p class=\"error\">The posts-per-page is outside of the expected range.</p>";
                    $valid = false;
                }

                $instantiate_config["region"]["timezone_offset"] = intval($_POST["region>timezone_offset"]);
                if ($instantiate_config["region"]["timezone_offset"] < -12 or $instantiate_config["region"]["timezone_offset"] > 12) {
                    echo "<p class=\"error\">The timezone-offset is outside of the expected range.</p>";
                    $valid = false;
                }

                $whitelist_string = $_POST["auth>access>whitelist"];
                $instantiate_config["auth"]["access"]["whitelist"] = array();
                foreach (explode(",", $whitelist_string) as $user) {
                    $user = trim($user, " ");
                    if ($user == preg_replace("/[^a-zA-Z0-9]/", "", $user)) { // Check to see if this user contains only allowed characters.
                        if (strlen($user) > 0) {
                            $instantiate_config["auth"]["access"]["whitelist"][] = $user;
                        }
                    } else {
                        echo "<p class=\"error\">The \"" . htmlspecialchars($user) . "\" in the whitelist contains disallowed characters.</p>";
                        $user = preg_replace("/[^a-zA-Z0-9]/", '', $user);
                        $valid = false;
                    }
                }
                $blacklist_string = $_POST["auth>access>blacklist"];
                $instantiate_config["auth"]["access"]["blacklist"] = array();
                foreach (explode(",", $blacklist_string) as $user) {
                    $user = trim($user, " ");
                    if ($user == preg_replace("/[^a-zA-Z0-9]/", "", $user)) { // Check to see if this user contains only allowed characters.
                        if (strlen($user) > 0) {
                            $instantiate_config["auth"]["access"]["blacklist"][] = $user;
                        }
                    } else {
                        echo "<p class=\"error\">The \"" . htmlspecialchars($user) . "\" in the blacklist contains disallowed characters.</p>";
                        $user = preg_replace("/[^a-zA-Z0-9]/", '', $user);
                        $valid = false;
                    }
                }
                $adminlist_string = $_POST["auth>access>admin"];
                $contains_current_user = false;
                $instantiate_config["auth"]["access"]["admin"] = array();
                foreach (explode(",", $adminlist_string) as $user) {
                    $user = trim($user, " ");
                    if ($user == preg_replace("/[^a-zA-Z0-9]/", "", $user)) { // Check to see if this user contains only allowed characters.
                        if ($user == $username) { $contains_current_user = true; } // Check to see if this user matches the current user.
                        if (strlen($user) > 0) {
                            $instantiate_config["auth"]["access"]["admin"][] = $user;
                        }
                    } else {
                        echo "<p class=\"error\">The \"" . htmlspecialchars($user) . "\" in the admin list contains disallowed characters.</p>";
                        $user = preg_replace("/[^a-zA-Z0-9]/", '', $user);
                        $valid = false;
                    }
                }
                if ($contains_current_user == false) {
                    echo "<p class=\"error\">The updated list of administrators does not contain your username.</p>";
                    $valid = false;
                }

                if (in_array($_POST["auth>access>mode"], array("whitelist", "blacklist"))) {
                    $instantiate_config["auth"]["access"]["mode"] = $_POST["auth>access>mode"];
                } else {
                    echo "<p class=\"error\">The selected access mode is invalid.</p>";
                    $valid = false;
                }

                if (is_dir($_POST["auth>provider"])) {
                    $_POST["auth>provider"] = rtrim($_POST["auth>provider"], "/"); $_POST["auth>provider"] .= "/"; // Ensure the provider path ends with exactly one forward slash.
                    $instantiate_config["auth"]["provider"]["core"] = $_POST["auth>provider"] . "authentication.php";
                    $instantiate_config["auth"]["provider"]["signin"] = $_POST["auth>provider"] . "signin.php";
                    $instantiate_config["auth"]["provider"]["signout"] = $_POST["auth>provider"] . "signout.php";
                    $instantiate_config["auth"]["provider"]["signup"] = $_POST["auth>provider"] . "signup.php";
                    if (is_file($instantiate_config["auth"]["provider"]["core"]) == false) {
                        echo "<p class=\"error\">The specified DropAuth directory does not contain the core authentication library.</p>";
                        $valid = false;
                    }
                    if (is_file($instantiate_config["auth"]["provider"]["signin"]) == false) {
                        echo "<p class=\"error\">The specified DropAuth directory does not contain the sign-in file.</p>";
                        $valid = false;
                    }
                    if (is_file($instantiate_config["auth"]["provider"]["signout"]) == false) {
                        echo "<p class=\"error\">The specified DropAuth directory does not contain the sign-out file.</p>";
                        $valid = false;
                    }
                    if (is_file($instantiate_config["auth"]["provider"]["signup"]) == false) {
                        echo "<p class=\"error\">The specified DropAuth directory does not contain the sign-up file.</p>";
                        $valid = false;
                    }
                } else {
                    echo "<p class=\"error\">The specified DropAuth directory does not exist.</p>";
                    $valid = false;
                }

                if ($valid == True) {
                    save_config($instantiate_config);
                    echo "<p>Successfully updated configuration</p>";
                } else {
                    echo "<p>The configuration file was not updated.</p>";
                }
                echo "</div>";
            }

            $csv_whitelist = "";
            foreach ($instantiate_config["auth"]["access"]["whitelist"] as $user) {
                $csv_whitelist .= $user . ",";
            }
            $csv_whitelist = rtrim($csv_whitelist, ",");

            $csv_blacklist = "";
            foreach ($instantiate_config["auth"]["access"]["blacklist"] as $user) {
                $csv_blacklist .= $user . ",";
            }
            $csv_blacklist = rtrim($csv_blacklist, ",");

            $csv_adminlist = "";
            foreach ($instantiate_config["auth"]["access"]["admin"] as $user) {
                $csv_adminlist .= $user . ",";
            }
            $csv_adminlist = rtrim($csv_adminlist, ",");

            $dropauth_location = dirname($instantiate_config["auth"]["provider"]["core"]);
            $dropauth_location = rtrim($dropauth_location, "/");
            $dropauth_location .= "/";
            ?>
            <form method="POST">
                <h3>Archive</h3>
                <label for="archive>path">Directory Path:</label> <input name="archive>path" id="archive>path" type="text" value="<?php echo $instantiate_config["archive"]["path"]; ?>"><br>
                <h3>Behavior</h3>
                <label for="behavior>posts_per_page">Posts Per Page:</label> <input name="behavior>posts_per_page" id="behavior>posts_per_page" type="number" step="1" min="1" max="100" value="<?php echo $instantiate_config["behavior"]["posts_per_page"]; ?>"><br>

                <h3>Region</h3>
                <label for="region>timezone_offset">UTC Offset:</label> <input name="region>timezone_offset" id="region>timezone_offset" type="number" step="1" min="-12" max="12" value="<?php echo $instantiate_config["region"]["timezone_offset"]; ?>"><br>
                <h3>Authentication</h3>
                <h4>Access</h4>
                <label for="auth>access>mode">Access Mode:</label> <select name="auth>access>mode" id="auth>access>mode">
                    <option value="blacklist" <?php if ($instantiate_config["auth"]["access"]["mode"] == "blacklist") { echo "selected"; } ?>>Blacklist</option>
                    <option value="whitelist" <?php if ($instantiate_config["auth"]["access"]["mode"] == "whitelist") { echo "selected"; } ?>>Whitelist</option>
                </select><br>
                <label for="auth>access>whitelist">Whitelist:</label> <input name="auth>access>whitelist" id="auth>access>whitelist" type="text" value="<?php echo $csv_whitelist; ?>" placeholder="user1,user2,user3"><br>
                <label for="auth>access>blacklist">Blacklist:</label> <input name="auth>access>blacklist" id="auth>access>blacklist" type="text" value="<?php echo $csv_blacklist; ?>" placeholder="user1,user2,user3"><br>
                <label for="auth>access>admin">Administrators:</label> <input name="auth>access>admin" id="auth>access>admin" type="text" value="<?php echo $csv_adminlist; ?>" placeholder="admin1,admin2,admin3"><br>
                <h4>Provider</h4>
                <label for="auth>provider">DropAuth Location:</label> <input name="auth>provider" id="auth>provider" type="text" value="<?php echo $dropauth_location; ?>" placeholder="../dropauth/"><br>

                <br><input class="button" name="submit" id="submit" type="submit" value="Submit">
            </form>
        </div>
    </body>
</html>
