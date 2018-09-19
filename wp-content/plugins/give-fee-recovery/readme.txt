=== Give - Fee Recovery ===
Contributors: givewp, wordimpress
Donate link: https://givewp.com
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, fees
Requires at least: 4.8
Tested up to: 4.9
Stable tag: 1.7.1
Requires Give: 2.2.0
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

Keep more of your donations by asking donor's to take care of the fees.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds a fee recovery functionality to your donation forms.

== Installation ==

For instructions installing Give add-ons please see: https://givewp.com/documentation/core/how-to-install-and-activate-give-add-ons/

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.7.1: July 30th, 2018 =
* Important: Added compatibility with Give 2.2.0+. Please update Give Core to 2.2.0+ prior to this update.
* Fix: Resolve "give_global_vars is not defined" error in Chrome developer tools' console log.
* Fix: Added auto width CSS to the fee recovery checkbox to improve compatibility with some theme styles for input checkboxes.
* Fix: Unescaped characters warning was displaying in Chrome developer tools' console log.

= 1.7.0: June 18th, 2018 =
* New: Added a new tool to recalculate Fees for all forms. This is helpful if for some reason your database gets corrupted or the data incorrectly altered.
* New: Added support for Fee Recovery to Give Core's new donation payment CSV exporter.
* New: The donation amount breakdown now displays for renewal payments.
* Fix: A JavaScript error would sometimes occur on the admin donation detail page when updating the donation's fee amount.
* Fix: The add-on now sends the correct information to PayPal's IPN regarding a donation's fee.

= 1.6.1: June 6th, 2018 =
* New: Added support for Fee Recovery to Give Core's new exporter.
* Tweak: The add-on is now using Give Core's format/unformat functionality rather than its own.
* Tweak: The formatted amount value is now passed via a data attribute in JS.
* Fix: The fee recovery option was displaying incorrectly when "Button" mode was enabled and the modal window was opened and closed.
* Fix: A JS error would occur when updating the fee amount on the donation details page in wp-admin.
* Fix: The add-on now passes the correct fee information to PayPal IPN.
* Fix: The fee breakdown now displays correctly for renewal donations.

= 1.6: May 2nd, 2018 =
* New: Added compatibility with Give Core 2.1's new donations exporter.
* Tweak: Performance improvements to ensure fee calculcations work from a meta key rather than calculating on the fly which can cause slowness.
* Tweak: Added compatibility with Give Core version 2.1+.
* Fix: Data attribute conflict with the Razorpay gateway.
* Fix: Wrong calculation of fee amount if decimal separator is set to comma and thousand separator is set to a decimal point.

= 1.5: February 27th, 2018 =
* New: If the currency has no decimals, fee recovery will not alter the appearance on donation forms. This means your donation forms are more readable and should help increase conversion rates.
* Fix: The fees would incorrectly calculate if the currency decimal separator was set to a comma and the thousands separator was set to a decimal.

= 1.4: January 17th, 2018 =
* New: 2.0 compatibility. Please update to the latest version of Give today!
* Tweak: Removed repetious code within JavaScript to better optimize plugin.
* Tweak: Removed qTip references for better Give 2.0+ compatiblity.

= 1.3.8: January 12th, 2018 =
* New: Added Fee Recovery support for Manual Donations add-on. You can now add fees to donations that you add manually!
* Fix: Conflict with PDF receipts causing a "Fatal error: Call to undefined function __give_20_bc_str_type_email_tag_param()" error.

= 1.3.7: January 3rd, 2018 =
* Fix: Per gateways fees were no calculating properly due to jQuery conflict in the 1.3.6 release. This has been fixed.
* Fix: Improved JS with jQuery no conflict mode. This should help prevent conflicts with other plugins.
* Tweak: Fee recovery now enqueues admin scripts using give core function to check whether current page is a give one.

= 1.3.6: December 28th, 2017 =
* Fix: There was a JS conflict when with jQuery that multiple users reported which has now been fixed.

= 1.3.5: December 19th, 2017 =
* Fix: We discovered another bug with the way fees were being deducted from goal amounts and resolved it. All goal amounts should now properly reflect the amount raised minus any fees given by donors.

= 1.3.4: December 18th, 2017 =
* Fix: Fees were incorrectly being added to goals when the donation form had more than 20 donations accepted. Also the fee calculation function has been optimized for better performance.
* Fix: Bug where if admin had never adjusted the fee amounts and updated it would default to 0.00.
* Tweak: Changed how the fee amount is stored for the upcoming 2.0 release of Give core.

= 1.3.3: November 30th, 2017 =
* Fix: Conflict with the thousands separator that lead to miscalculated fee amount for donations more than $1,000.
* Tweak: Added compatibility for the future swap from qTip to hint CSS tooltips.

= 1.3.2: November 30th, 2017 =
* Fix: Hotfix to resolve issue with calculating fees when custom amount is enabled.

= 1.3.1: November 29th, 2017 =
* New: Fees are now calculated using client-side JS rather than server-side via AJAX. What does this mean? Much faster fee calculations and a better donor experience.
* Fix: When changing payment gateways in modal display mode the fee recovery checkbox field would incorrectly appear at the top of the modal. This is now fixed.

= 1.3: November 3rd, 2017 =
* New: Added an additional option to display a fee breakdown below the final amount field.
* Tweak: Optimized the plugin's use of admin-ajax.php calls to lessen the server load for high traffic environments.
* Tweak: Optimized the plugin's coding structure to be less repetitive and more effecient.
* Fix: When fee recovery was active you couldn't properly edit a donation's total payment field. Now you can.
* Fix: The [give_goal] shortcode showed a different amount than the goal appearing in the [give_form] shortcode.
* Fix: Styling issue when using a set donation recurring form. There would be a float issue with some themes.
* Fix: Optimized the plugin's settings so that the content doesn't flash on page load and the icon appears nicer for retina screens.

= 1.2.4 =
* Tweak: Reordered the fee recovery rows on the donation receipt so that it's more clear the donation breakdown if the donor opts in to give the fee.
* Fix: There was a bug where donor's who chose not to Give the fee would incorrectly be opted-in to the fee coverage. This has been resolved.
* Fix: Currencies formats with formats other than the Give's default would have the fee amount incorrectly calculated.
* Fix: When providing a flat fee but not percentage or visa-versa the fee calculation would be slightly off.

= 1.2.3 =
* Fix: A bug introduced the previous version prevented proper fee calculation output for forced opt-in was enabled.

= 1.2.2 =
* Fix: JS and CSS are now only loaded on the necessary admin pages.
* Fix: The fee recovery checkbox label was bumped to another line incorrectly with some themes.
* Fix: JS warning "jQuery.parseJSON requires a valid JSON string".

= 1.2.1 =
* Fix: When no flat fee is entered and only a percentage or visa versa then the fee amount would be incorrectly calculated. This is now fixed and you can now properly add just a flat fee or percentage for the fee amount.

= 1.2 =
* New: Plugin architecture reorganized for better hook navigation.
* Tweak: The tab icon has been updated to better reflect fee recovery within the donation form edit screen.
* Fix: Editing a donation of a donor who rejected to give a fee would result in an incorrect error appearing preventing the update.

= 1.1 =
* New: Improved reports now display fee information much clearer within separate reports.
* New: The fee recovery checkbox location is now easily movable with a new option to change where it appears within the donation form.
* New: Email receipts are now transparent with the fees. Now the {amount} template tag will display a breakdown of the fees collected compared to the actual donation automatically to better inform your donors.
* Fix: Math for the fee calculation was slightly off. Thanks @Benunc for jumping on it with your math wizardry.
* Fix: The plugin will deactivate itself if Give core is deactivated.
* Fix: Admins couldn't update the fee amount within wp-admin for individual payments. Now they can.
* Fix: Fees were counting towards goals and reflected in income reports. Now we exclude the fee amount from goals and reports so you can see the original donation total.

= 1.0 =
* Initial plugin release. Yippee!



