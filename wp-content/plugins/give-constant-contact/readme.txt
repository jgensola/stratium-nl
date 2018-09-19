=== Give - Constant Contact ===
Contributors: wordimpress
Tags: constant contact
Requires at least: 4.2
Tested up to: 4.7
Stable tag: 1.2.1
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

Constant Contact Add-on for the Give Donation Plugin

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds a email marketing integration with Constant Contact.

== Installation ==

= Minimum Requirements =

* WordPress 4.2 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give Constant Contact, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New. Upload the zip file you downloaded from your account page on Givewp.com - this will automatically upload the plugin. Once it's done, click "Activate" and you're all set!

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.2.1 =
* New: The plugin now checks to see if Give is active and up to the minimum version required to run the plugin - https://github.com/WordImpress/Give-Constant-Contact/issues/20

= 1.2 =
* New: Global and per-form option to enable/disable the newsletter opt-in checkbox default option.
* New: Now you can fetch you latest email list data from Constant Contact using the "Refresh Lists" button.
* New: i18n POT file ready for translations.
* Fix: When multiple donation forms are on a page clicking the "Subscribe to our newsletter" checkbox on a form that was further down the page would cause the donor to be scrolled to the first form - https://github.com/WordImpress/Give-Constant-Contact/issues/15
* Fix: PHP warning when no lists for Constant Contact yet created - https://github.com/WordImpress/Give-Constant-Contact/issues/7
* Fix: Moved priority of subscription action to earlier hook to support offsite gateways such as PayPal Standard and Offline Donations.

= 1.1.2 =
* Fix: PHP warnings when subscribing to a single mailing list upon donation
* Fix: PHP Warnings: Undefined Indexes when saving donation form in admin - https://github.com/WordImpress/Give-Constant-Contact/issues/3

= 1.1.1 =
* Fix: Inserting bad API key causes Give Settings to fail due to fatal error within get_lists() method - https://github.com/WordImpress/Give-Constant-Contact/issues/1

= 1.1 =
* Fix: Account for users who elect to NOT subscribe to the newsletter

= 1.0 =
* Initial plugin release. Yippee!

