=== Give - Form Field Manager ===
Contributors: wordimpress, dlocc, webdevmattcrom, ramiy
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, paymill, gateway
Requires at least: 4.8
Tested up to: 4.9
Stable tag: 1.3
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

== Description ==

Easily add and control Give's form fields using an easy-to-use interface.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.3: May 2nd, 2018 =
* New: Fields can now be disabled without having to delete them.
* Tweak: Updated hooks for Give Core 2.1 compatibility.
* Fix: Provided better validation for required fields.
* Fix: Multiselect field values are not appearing in the field.
* Fix: Improved the discoverability of the meta email tags feature.
* Fix: Removed raw HTML from tooltips.

= 1.2.8: March 5th, 2018 =
* Fix: The add-on uses the "startsWith" string function, which is unavailable in IE11 (or at least some versions of IE11). We have added a polyfill with the fix for this. Thanks @datesss
* Fix: Floating labels support now returned after last version broke it.

= 1.2.7: February 20th, 2018 =
* New: Additional hooks have been added for developers to add fields to the top and bottom of common form fields within wp-admin. This is useful for plugin developers to extend FFM.
* New: We improved the "Multi Select" checkbox field by allowing for multiple pre-selected default values. Rejoice!
* Fix: A JavaScript conflict with WooCommerce was causing a conflicts on websites running FFM alongside Woo. This has now been resolved and won't reoccur moving forward.
* Fix: PHP notice "Undefined index: give_ajax" if WP_DEBUG is turned on.
* Fix: When you add a backslash \ to a custom HTML field, it is stripped out. Now it's not!
* Fix: Additional tooltip optimizations so they're not cut off at all now :)

= 1.2.6: February 15th, 2018 =
* Fix: Tooltips were getting cut off in Give 2.0+ when viewing the form builder in WP-Admin.
* Fix: When a field returns an error the completed fields are preserved making it much easier for the donor to complete the donation.
* Fix: Local storage could potentially pull from another form if the same donation had been embedded multiple times on a page.
* Fix: The form submit button text would incorrectly revert to english incorrectly if there was a validation error within an FFM field.
* Fix: Give Error alerts does not display in chrome with FFM activated.

= 1.2.5: January 17th, 2018 =
* Tweak: Compatiblity with Give 2.0+
* Tweak: The plugin now respects it's own constants. This is useful for non-traditional WP plugin environments.
* Fix: AJAX validation is now used for required fields so that browsers like Safari don't allow submissions without completed required fields.
* Fix: The Checkbox field type was not showing all the saved options correctly in the donations dashboard.
* Fix: FFM was preventing Stripe checkout from opening properly in IE11/IE Edge.

= 1.2.4: October 30th, 2017 =
* Fix: The donation receipt page would incorrectly output blank fields that were not required. Now only completed field data will be displayed on the donation receipt.
* Fix: Wehn more than one donation form is embedded on a page the custom fields would be duplicated incorrectly.
* Fix: Certain add-ons' fields would display incorrectly when using "Modal" display mode.

= 1.2.3: September 13th, 2017 =
* Fix: Resolved a conflict with the email access submission button and FFM's validation in which it would prevent the email access form from submitting properly.
* Fix: FFM's validation was preventing native browser HTML5 validation from displaying properly.

= 1.2.2: September 5th, 2017 =
* Fix: Resolved issue where if a datepicker was placed within the gateway fieldset that updates via AJAX then the datepicker would lose its functionality after the donor switched payment gateways.

= 1.2.1 =
* Fix: Resolved issue where switching where custom fields could be removed when switching payment gateways if the donor had not previously completed any of the custom fields.
* Fix: Resolved issue with validating checkbox and radio fields marked as required.

= 1.2 =
* New: Added the ability to set a field's width. For instance, "half-width", "one-third", "two-thirds". This will allow you to create much better looking donation forms and tighen up the length.
* New: Added a locking functionality for form fields metakeys that warns admins that changing the metakey can change the visibility of historical data.
* New: When you switch gateways field data is preserved so donors don't have to retype anything when changing their mind about their payment method.
* New: Added a CSS class input to the Section field type to easily adjust CSS.
* New: You can now set a maximum for the number of repeater fields allowed to be created.
* New: Custom fields are now displayed within the donation receipt.
* New: There is now a new "hidden" field type.
* Tweak: Removed the "Size" attribute for "Maxlength" to allow admins to set a maximum number of characters for "Text", "Phone", "Email", "URL" and "Repeater" field types.
* Tweak: Added a min-height to the multiselect field for to improve compatiblity with some themes.
* Tweak: By default new fields added will not have "yes" as a value for whether the field is required or not.
* Tweak: Sections now default to a fieldset legend rather than an H3 tag.
* Tweak: Replaced all usave of get_post_meta() with give_get_meta().
* Tweak: Replaced usage of deprecated actions and filters.
* Fix: The website URL field now uses floating labels when enabled.
* Fix: The upload field no longer will display a bullet icon on some themes incorrectly.
* Fix: When Radio Button fields are Required an no default is set, it doesn't prevent the form from submitting.
* Fix: Custom field metaboxes no longer display incorrectly for renewal payments.
* Fix: When multiple donation forms are embedded on a page via shortcode the custom fields location could be conflicting.
* Fix: The plugin now fails gracefully (no errors/warnings) when the minimum PHP version is not met.

= 1.1.3 =
* Tweak: Updated deprecated Give core hooks in use for version 1.7
* Tweak: New banner will display if Give is not active or minimum version is not met - https://github.com/WordImpress/Give-Form-Field-Manager/issues/123
* Fix: The new email field had a bug preventing the field data to be viewed and updated in the admin - https://github.com/WordImpress/Give-Form-Field-Manager/issues/121

= 1.1.2 =
* New: Phone number field added. - https://github.com/WordImpress/Give-Form-Field-Manager/issues/57
* New: The time within the date picker field now has formatting options for additional flexibility - https://github.com/WordImpress/Give-Form-Field-Manager/issues/50
* New: Option to toggle the datepicker CSS output to better prevent conflicts with themes that style the datepicker - https://github.com/WordImpress/Give-Form-Field-Manager/issues/109
* New: Plugin activation banner with links to documentation and support.
* Fix: Multiple donation forms on a page containing custom form fields cause duplicate fields to appear incorrectly - https://github.com/WordImpress/Give-Form-Field-Manager/issues/108
* Fix: An admin entering the same value for multiple Meta Key fields prevents some data from being saved during a transaction. https://github.com/WordImpress/Give-Form-Field-Manager/issues/88
* Fix: The repeater field doesn't allow entries to be added in the wp-admin "Transaction Details" screen. Now it does. :) https://github.com/WordImpress/Give-Form-Field-Manager/issues/77
* Fix: The email field type is using the same ID as the Give core email field which can lead to issues. https://github.com/WordImpress/Give-Form-Field-Manager/issues/70
* Fix: Grunt now runs uglify properly to prevent infinite loop when developing. https://github.com/WordImpress/Give-Form-Field-Manager/issues/95
* Fix: The email address field is being pre-filled with the logged in users email address incorrectly. https://github.com/WordImpress/Give-Form-Field-Manager/issues/51
* Fix: The timepicker should default to the current time. https://github.com/WordImpress/Give-Form-Field-Manager/issues/49
* Fix: Custom form field metakeys are not sanitizing special characters and length properly. https://github.com/WordImpress/Give-Form-Field-Manager/issues/65
* Fix: Custom field data is not properly being passes to the Give API. https://github.com/WordImpress/Give-Form-Field-Manager/issues/35
* Fix: Issue with the support link not going to the proper URL. https://github.com/WordImpress/Give-Form-Field-Manager/issues/101
* Tweak: Updated the plugin's text domain to 'give-form-field-manager' to match plugin slug - https://github.com/WordImpress/Give-Form-Field-Manager/issues/116

= 1.1.1 =
* Tweak: Moved the transaction's "Custom Form Fields" metabox above "Payment Notes" so it's more easily accessible to admins - https://github.com/WordImpress/Give-Form-Field-Manager/issues/40
* Fix: Compatibility issues with custom form fields and floating labels functionality https://github.com/WordImpress/Give-Form-Field-Manager/issues/66
* Fix: No form fields, set as empty meta so no blank fields leftover
* Fix: PHP7 produces fatal error with WP_DEBUG and SCRIPT_DEBUG set to true - https://github.com/WordImpress/Give-Form-Field-Manager/issues/67

= 1.1 =
* New: Added a new {all_custom_fields} email to to output all custom field data from a donation form submission
* Fix: When a user sets up a donation form with the "Reveal Upon Click" option and wants the Custom Form Fields to display in those hidden fields they were displaying rather than being hidden. https://github.com/WordImpress/Give-Form-Field-Manager/issues/59

= 1.0 =
* Initial plugin release. Yippee!