<?php
function fetch_posts($profile_file_path) {
    $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));
    asort($profile_files);
    $posts = array();
    foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
        $should_skip = false;
        $ignore_files = array("name.txt", "oldnames.txt", "bio.txt", "follow", "sex.txt", "race.txt", "religion.txt", "profile_pic.jpg"); // Ignore metadata files.
        foreach ($ignore_files as $string) {
            if (str_contains($profile_file, $string)) {
                $should_skip = true;
                break;
            }
        }
        if ($should_skip == true) { continue; }
        unset($should_skip);

        $profile_file_cleaned = str_replace("_UTC", "", $profile_file);
        $file_timestamp_human = str_replace("-", "", str_replace("_", " ", substr($profile_file_cleaned, 0, 19)));
        $file_timestamp_unix = strtotime($file_timestamp_human);


        if ($file_timestamp_unix > 0) {
            if (isset($posts[$file_timestamp_unix]["is_story"]) == false) {
                $posts[$file_timestamp_unix]["is_story"] = true; // Assume this post is a story until we find information that suggests otherwise.
            }
            $file_extension = strtolower(pathinfo($profile_file, PATHINFO_EXTENSION));
            if (str_contains($profile_file, "_link.txt") == true) {
                $posts[$file_timestamp_unix]["link"] = file_get_contents($profile_file_path . "/" . $profile_file);
            } else if (str_contains($profile_file, "_location.txt") == true) {
                $posts[$file_timestamp_unix]["location"] = file_get_contents($profile_file_path . "/" . $profile_file);
            } else if (str_ends_with($profile_file, "UTC.txt")) {
                $posts[$file_timestamp_unix]["description"] = file_get_contents($profile_file_path . "/" . $profile_file);
                $posts[$file_timestamp_unix]["is_story"] = false; // This post can't be a story because it has an associated text file.
            } else if (strtolower(pathinfo($profile_file)["extension"]) == "txt" and str_ends_with($profile_file, "UTC_location.txt")) {
                $posts[$file_timestamp_unix]["location"] = file_get_contents($profile_file_path . "/" . $profile_file);
            } else if (in_array($file_extension, array("jpg", "jpeg", "webp", "png", "m4v", "mp4", "webm"))) {
                $slide_id = intval(end(explode("_", pathinfo($profile_file, PATHINFO_FILENAME))));
                if ($slide_id > 0) { // Check to see if this slide part of a post (i.e. it has a slide number).
                    $posts[$file_timestamp_unix]["is_story"] = false; 
                }
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
    return $posts;
}


function get_profile_metadata($profile_file_path) {
    $profile_metadata = array(
        "name" => array(
            "real" => null,
            "nick" => null,
            "set" => null
        ),
        "sex" => "", // Single character indicating sex.
        "birthday" => array(
            "date" => null, // Birthdate in YYYY-MM-DD
            "age" => null, // Age in years
            "precision" => null, // The precision with which the birthday is known (+-N)
            "distance" => null // How long ago/until the birthday (whichever is closer)
        ),
        "religion" => array(
            "name" => null,
            "symbol" => null
        ),
        "account" => array(
            "bio" => null,
            "friends" => array(
                "following" => null,
                "followers" => null
            )
        )
    );

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
            $profile_metadata["name"]["nick"] = file_get_contents($profile_nickname_file);
        } else if (file_exists($profile_realname_file)) {
            $profile_metadata["name"]["real"] = file_get_contents($profile_realname_file);
        } else if (file_exists($profile_setname_file)) {
            $profile_metadata["name"]["set"] = file_get_contents($profile_setname_file);
        }


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

            $profile_metadata["birthday"]["date"] = array_filter(explode("\n", file_get_contents($profile_birthday_file)))[0];
            $profile_metadata["birthday"]["age"] = $years_old;
            $profile_metadata["birthday"]["precision"] = $birthday_precision;
            $profile_metadata["birthday"]["distance"] = $distance_from_birthday;
        }
        if (file_exists($profile_sex_file)) {
            $profile_metadata["sex"] = strtoupper(trim(file_get_contents($profile_sex_file)));
        }
        if (file_exists($profile_religion_file)) {
            if (trim(strtolower(file_get_contents($profile_religion_file))) == "christianity") {
                $profile_metadata["religion"]["name"] = "Christian";
                $profile_metadata["religion"]["symbol"] = "✝";
            } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "islam") {
                $profile_metadata["religion"]["name"] = "Muslim";
                $profile_metadata["religion"]["symbol"] = "☪︎";
            } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "judaism") {
                $profile_metadata["religion"]["name"] = "Jewish";
                $profile_metadata["religion"]["symbol"] = "✡";
            } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "buddhism") {
                $profile_metadata["religion"]["name"] = "Buddhist";
                $profile_metadata["religion"]["symbol"] = "☯︎";
            } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "hinduism") {
                $profile_metadata["religion"]["name"] = "Hindu";
                $profile_metadata["religion"]["symbol"] = "࿗";
            } else if (trim(strtolower(file_get_contents($profile_religion_file))) == "atheism") {
                $profile_metadata["religion"]["name"] = "Athiest";
                $profile_metadata["religion"]["symbol"] = "⚛︎";
            }
        }
        if (file_exists($profile_bio_file)) {
            $profile_metadata["account"]["bio"] = nl2br(file_get_contents($profile_bio_file));
        }
        if (isset($profile_instaloader_data)) {
            $profile_metadata["account"]["friends"]["following"] = $profile_instaloader_data["node"]["edge_follow"]["count"];
            $profile_metadata["account"]["friends"]["followers"] = $profile_instaloader_data["node"]["edge_followed_by"]["count"];
        }
    }

    return $profile_metadata;
}

?>
