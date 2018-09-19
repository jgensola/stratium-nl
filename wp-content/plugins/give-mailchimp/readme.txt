=== Give - MailChimp ===
Contributors: wordimpress, dlocc, webdevmattcrom
Tags: mailchimp, mail chimp, email, email marketing
Requires at least: 4.8
Tested up to: 4.9
Stable tag: 1.4.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The official Give MailChimp add-on.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds various integrations with MailChimp.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.4.1: May 2nd, 2018 =
* Tweak: Updated deprecated hooks within Give Core 2.1 - please update to the latest Give version 2.1!

= 1.4 =
* New: Version 2.0+ of Give core is now required to run MailChimp 1.4+. Please update your Give core version before updating to this version.
* New: You now have the ability to send donation data such as the donor's Form Title, Payment Method and more to MailChimp. This information will display under the "Subscriber's Details" section within the lists they subscribed.
* New: Custom form fields created with the Form Field Manager now have the ability to also be sent to MailChimp. The field data will display with the field's title followed by the donor's field submission information. This data will also display under the "Subscriber's Details" section within the lists they subscribed.
* New: The default settings now contains a multi-check box for your default lists that you want donors subscribed to. This removes the limit of a single default list.

= 1.3.6 =
* New: The MailChimp API key now displays as a password field type when added for further security.
* Fix: Restored the lists with groups functionality that was not working properly in the last version.

= 1.3.5 =
* New: Refresh button to fetch latest list data from MailChimp.
* Fix: Prevent redirect loops on certain hosts using object-cache.php like GoDaddy Pro.
* Fix: Double opt-in option when set to false would incorrect return as true.

= 1.3.4 =
* Fix: Added validation to verify that the admin inserted a correct MailChimp API key into the settings field.

= 1.3.3 =
* New: Polish translation added - thanks Emilia!
* New: The plugin now checks to see if Give is active and up to the minimum version required to run the plugin - https://github.com/WordImpress/Give-Constant-Contact/issues/31

= 1.3.2 =
* Fix: When multiple donation forms are on a page clicking the "Subscribe to our newsletter" checkbox on a form that was further down the page would cause the donor to be scrolled to the first form - https://github.com/WordImpress/Give-MailChimp/issues/24

= 1.3.1 =
* Fix: Subscribing issue where no matter what list you choose in the per form options, the global option is used to subscribe the donor.

= 1.3 =
* New: Add new indicator on the admin transaction details page to easily tell whether the donor opted-in to the newsletter.
* New: Global and per-form option to enable/disable the newsletter opt-in checkbox default option.
* New: Improved how the MailChimp API key is saved in the admin settings so it pulls email list data without the need to reload the page and also if there's an API error it will display that for the admin.
* Fix: Moved priority of subscription action to earlier hook to support offsite gateways such as PayPal Standard and Offline Donations.
* Fix: If only a single list is provided to sign-up then support a non-array variable.
* i18n: Upgraded textdomain to match the plugin slug of "give-mailchimp".

= 1.2.1 =
* Fix: Check for $_POST variables when updating options to prevent unnecessary PHP warnings when Display Errors is set turned on.

= 1.2 =
* Tweak: Improved connectivity issues to MailChimp when CA certificate has issues verifying.

= 1.1 =
* Fix: Account for users who elect to NOT subscribe to the newsletter.

= 1.0 =
* Initial plugin release. Yippee!