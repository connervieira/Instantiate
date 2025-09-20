<?php
function fetch_posts($profile_file_path) {
    $profile_files = array_diff(scandir($profile_file_path), array(".", ".."));
    asort($profile_files);
    $posts = array();
    foreach ($profile_files as $profile_file) { // Iterate through each file in this profile.
        $should_skip = false;
        $ignore_files = array("name.txt", "bio.txt", "follow", "sex.txt", "race.txt", "religion.txt", "profile_pic.jpg"); // Ignore metadata files.
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
            } else if (strtolower(pathinfo($profile_file)["extension"]) == "txt") {
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

?>
