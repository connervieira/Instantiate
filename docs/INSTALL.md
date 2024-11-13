# Installation

This document explains how to install Instantiate.

## Note

This guide assumes you are installing Instantiate on a Debian/Ubuntu based Linux distribution. Other platforms may required different steps.

## Installing

### Dependencies

There are a few dependencies that need to be installed for Instantiate to function.

1. Install Apache, or another web-server host.
    - Example: `sudo apt install apache2`
2. Install and enable PHP for your web-server.
    - Example: `sudo apt install php; sudo a2enmod php*`
3. Restart your web-server host.
    - Example: `sudo apache2ctl restart`
4. Install and configure [V0LT DropAuth](https://v0lttech.com/dropauth.php).

### Installation

After the dependencies are installed, copy the Instantiate directory from the source you received it from, to the root of your web-server directory.

For example: `cp ~/Downloads/Instantiate /var/www/html/instantiate`


## Set Up

### Archive

Instantiate needs to be able to access your post archive to function properly.

1. Copy your post archive to a location accessible to your web server.
    - Example: `cp -r ~/Downloads/PostArchive/ /var/www/html/instantiate/archive`
2. Ensure the post archive is readable to the `www-data` user.

Later in the setup process you will need to set the path to your archive in the Instantiate configuration.


### Connecting

After the basic set-up process is complete, you should be able to view the Instantiate interface in a web browser.

1. Open a web browser of your choice.
2. If you haven't already, sign up for a DropAuth account on your local instance, then sign in.
3. Enter the URL for your Instantiate installation.
    - Example: `http://192.168.0.76/instantiate/`
4. Assuming you logged in, you should see a prompt that you are not currently following any profiles.


### Configuration

The default administrator username is "admin". If your DropAuth username is "admin", you can skip to step 3.

1. In the Instantiate directory (ex: `/var/www/html/instantiate/`), open the `config.json` file with your editor of choice.
2. Change the `auth>access>admin` list to contain your username.
    - If you don't intend to use the default "admin" username, then you should remove it here for security purposes.
3. Next, sign in with an admin account using DropAuth (if you haven't already).
4. Navigate to the administration page by clicking the "Configure" button on the main Instantiate page.
5. Make configuration changes as needed.
    - See the [docs/CONFIGURE.md](docs/CONFIGURE.md) document to learn more about each option.
