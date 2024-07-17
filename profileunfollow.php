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

$instantiate_database = load_database();
if (isset($instantiate_database[$username]) == false) { // Check to see if the current user does not yet exist in the instantiate database.
    // Initialize this user in the database.
    $instantiate_database[$username] = array();
    $instantiate_database[$username]["following"] = array();
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
                <a class="button" href="./profileview.php?profile=<?php echo $selected_profile; ?>">Back</a>
            </div>
            <div class="right">
            </div>
        </div>
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($instantiate_config["branding"]["product_name"]); ?></h1>
                <h2>Follow Profile</h2>
            </div>
            <div>
                <a href="https://v0lttech.com"><img src="./assets/img/icons/v0lt.svg"></a>
                <a href="https://instagram.com"><img src="./assets/img/icons/instagram.svg"></a>
            </div>
        </div>
        <hr>
        <?php
        if (in_array($selected_profile, array_keys($instantiate_database[$username]["following"]))) { // Check to see if the user is following this profile.
            if (time() - intval($_GET["confirm"]) < 0) { // Check to see if the confirmation timestamp is in the future.
                echo "<p class=\"warning\">Warning: The confirmation timestamp is in the future. This should never happen. If you clicked an external link to get here, it's possible someone is trying to manipulate you into unfollowing this profile.</p>";
            } else if (time() - intval($_GET["confirm"]) < 10) { // Check to see if the confirmation timestamp is within the last 10 seconds.
                unset($instantiate_database[$username]["following"][$selected_profile]);
                save_database($instantiate_database);
                echo "<p>Successfully unfollowed <b>" . $selected_profile . "</b></p>";
                header("Location: ./profileview.php?profile=" . $selected_profile);
            } else {
                echo "<p>Are you sure you would like to unfollow user <b>" . $selected_profile . "</b>?</p>";
                echo "<a class=\"button\" href=\"./profileunfollow.php?profile=$selected_profile&confirm=" . time() . "\">Unfollow</a>";
            }
        } else {
            echo "<p>You are not following <b>" . $selected_profile . "</b>.</p>";
        }
        ?>
    </body>
</html>
