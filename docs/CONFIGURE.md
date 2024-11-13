# Configuration

This document explains all of the configuration values available for Instantiate.


## Method

There are two different methods that can used to configure Instantiate.

### Web Interface

The primary way to configure Instantiate is to use the web interface found at "/instantiate/configure.php". This page is only accessible to administrators.

The web interface contains common configuration options, but some options are only accessible via the configuration file.

### File

Advanced users can modify the configuration file directly. The configuration file is created the first time Instantiate loads at `instantiate/config.json`.

This method exposes all available configuration options, but is also much more prone to breaking. In the event that the configuration file because corrupted, you can reset it by deleting `config.json` and allowing a fresh file to be generated.


## Values

This section explains all of the available configuration values. Some values may not be accessible from the web interface.

- `branding` contains settings that control branding for Instantiate.
    - `product_name` allows you to rename your Instaniate instance to a custom name.
- `archive` contains settings for configuring your post archive.
    - `path` is the path to your post archive directory (relative to Instantiate).
- `behavior` contains settings that control how the Instantiate interface behaves.
    - `posts_per_page` is a positive integer number that determines how many posts will be displayed on each page.
    - `show_stories` determines whether stories will be displayed in the posts timeline.
        - A file is considered a story if it is only associated with 1 image/video, and is not associated with a text file.
- `region` contains settings related to regional information.
    - `timezone_offset` defines the hourly difference between the UTC timestamps used by posts in the archive and the timezone you want posts to be displayed as.
- `auth` contains configuration values related to authentication.
    - `access` contains settings related to access control.
        - `whitelist` is a list of users who are exclusively allowed access when "whitelist" mode is enabled.
        - `blacklist` is a list of users who are forbidden access when "blacklist" mode is enabled.
        - `admin` is a list of users with adminstrator access on this instance.
            - These users have the ability to change configuration values, and will override the whitelist.
        - `mode` can be set to either "whitelist" or "blacklist" and determines the access mode.
    - `provider` configures the DropAuth authentication provider back-end.
        - `core` points to the `authentication.php` file for your DropAuth instance.
        - `signin` points to the `signin.php` page for your DropAuth instance.
        - `signout` points to the `signout.php` page for your DropAuth instance.
        - `signup` points to the `signup.php` page for your DropAuth instance.
