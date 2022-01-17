# Add New Users

**INACTIVE NOTICE: This plugin is unsupported by WPMUDEV, we've published it here for those technical types who might want to fork and maintain it for their needs.**

## Translations

Translation files can be found at https://github.com/wpmudev/translations

## Add New Users lets you quickly add multiple users to your site without wearing out your mouse clicking "Add New".

Do you find it frustrating adding new users to your site one-by-one? We do! So we created Add New Users.

![Add New Users](https://premium.wpmudev.org/wp-content/uploads/2009/12/add-new-users.jpg)

 Easily add new users in groups of 15

### Adding new users is simple

This plugin has been so popular with Multisite and BuddyPress site owners that we've made it available for single WordPress installs. Create and add new users in bulk batches of 15. Creating and adding new users to a site is now as simple as entering a username, an email address, a password (if you wish) and select the level of access to grant to each user. All new users will receive an email containing their new username, password and login link.

### Add Users and Sites Faster

Do you need to add existing users in batches of up to 15? Give the[Add Existing Users plugin](http://premium.wpmudev.org/project/add-existing-users/) a try. If you want your new users to have their own site we have a tool for that too – [Blog and User Creator](../project/blog-and-user-creator). And, to create hundreds or thousands of sites and users automatically we created [Batch Create](../project/batch-create).

## Usage

Start by reading [Installing plugins](https://wpmudev.com/docs/using-wordpress/installing-wordpress-plugins/) section in our [comprehensive WordPress and WordPress Multisite Manual](https://premium.wpmudev.org/wpmu-manual/) if you are new to WordPress.

### To install:

1.  Download the plugin file 2.  Unzip the file into a folder on your hard drive 3.  Upload **/add-new-users/** folder to **/wp-content/plugins/** folder on your site 4.  Login to your admin panel for WordPress or Multisite and activate the plugin:

*   For WordPress Multisite installs - visit **Network Admin -> Plugins** and **Network Activate** the plugin.
*   On regular WordPress installs - visit **Plugins** and **Activate** the plugin.

_Note: If you have an older version of the plugin installed in /mu-plugins/ please delete it._ _

![Network activate plugin](https://premium.wpmudev.org/wp-content/uploads/2011/02/addnew61.jpg)

 _ **That's it! No configuration necessary!**

### To use:

A new menu item called **Add New Users** should appear under the **Users** navigation menu.  It is designed for quickly creating and adding new users to a site in batches of up to 15 users. _Please note:_

*   The users are immediately added to the site and automatically listed as users on the**Users** page.
*   They can only access features in the site’s administration panel based on the role they've been assigned
*   Spam filters, especially strict ones for institutional email addresses, often block the emails that the login details.  If unsure use free webmail accounts such as gmail, hotmail that don’t block these invitation emails.
*   Use only lowercase letters and numbers, with no spaces, in the username
*   The username is what they use to sign into the dashboard and is displayed on posts and comments they write. You can’t change a username,  however you can change what [name is displayed](http://help.edublogs.org/2009/08/25/changing-or-deleting-a-username/).
*   It won’t allow you to create several usernames with the same email address because the system resets password based on email address. But you can trick it using the [gmail+ method](http://help.edublogs.org/2009/08/24/2009/08/24/2009/02/27/creating-student-accounts-using-one-gmail-account/)

**To add the new users, blog administrators simply enter:**

1.  The username they would like to give them
2.  Their email address
3.  A password for the user (if they want otherwise a random password will be automatically generated)
4.  Assign their role - you can find out more about different [levels of access here](https://premium.wpmudev.org/wpmu-manual/introduction-to-super-admin-user/).

![Add new users using Add New Users](https://premium.wpmudev.org/wp-content/uploads/2011/02/addnew64.jpg)
