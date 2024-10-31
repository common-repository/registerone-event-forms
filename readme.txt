=== RegisterONE - Enterprise Event Management & Ticketing ===
Contributors: registerone
Plugin Name: RegisterONE – Enterprise Event Management & Ticketing
Plugin URI: https://www.registerone.com/wordpress-plugin/
Tags: event management, e-commerce, registration, ticketing, floor mapping, exhibitor, registrant, forms
Author: RegisterONE
Requires at least: 5
Tested up to: 6.5
Version 3.7.6
Stable tag: 3.7.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

RegisterONE is a unified, enterprise level event management platform.

This plugin allows event organizers & producers to build out and embed ticketing, registration, and floor mapping into your Wordpress site. Registrants can select booth locations, purchase assets, amenities and upgrades, upload documents and media files, provide e-signatures and pay for cart items. Create, deploy and manage multiple registration forms easily and efficiently from the RegisterONE platform with support for Stripe & Paypal payment gateways.

Advanced floor mapping gives you the ability to plot booths with different sizes and pricing. Advanced features include dynamic pricing and selectable areas by applicant type. You can also start the registration process with booth selection from your forward facing map.

To use, simply create and customize your forms from RegisterONE. Once completed, your form shortcodes can be found in the **Wordpress > Plugins > RegisterONE > Settings** screen ready to add to a page to generate your forms. Data is securely captured and stored in the cloud, allowing for easy administration of applicant details, media uploads, asset orders and purchases.

You need a <a href="https://www.registerone.com">RegisterONE.com</a> account to use this plugin.

== Features ==

**NEW with v3.7.0** - **Custom booth labels, borders and fill colors added**. Labels and numbers now display over each booth area with custom sizing. Borders can now be added to each area to define each area, with custom colors and width. Area fill colors can be customized based on area status (ie. Available, Reserved, Unavailable, etc). Mapping also supports booth grouping/restrictions by Applicant type (ie. Sponsors). This allows you to create one master form with one map that supports multiple applicant types with different booth areas restrictions. You can also setup dynamic pricing based on applicant type for maximum flexibility.

Use RegisterONE to plan, promote, market, deploy and repeat within one dynamic, integrated system. Solutions include event and marketing management, virtual digital profile builder, virtual digital marketing management, meetings and lead retrieval, ticketing app and asset tracking app for on-site management and e-commerce solutions.

Each solution is based on a series of modules which can be configured to your individual needs & event requirements. Use only what you need to maximize engagement and outreach. Services are fully GDPR and CCPA privacy policy compliant and uses global single sign-on (SSO) for access.

* Complete exhibitor/attendee registration management, from start to finish
* Full GDPR/California privacy policy compliance
* WP Multisite aware for Network Activation
* Native iframes shortcode for form & map embedding
* Booth grouping/restrictions by Applicant type (ie. Sponsors)
* Session/Conference speaker forms to feed Sessions Builder
* Ticketing sales with support for badging & barcodes
* Custom badge designer with barcode/image support
* Mobile Ticketing Check-in App (Android/iOS)
* Manage and review your applicants easily and efficiently
* Block registrant deposit until approved
* Integrates with Chatra & Hubspot chat widgets in registration forms
* Block balance payment until approved
* Flexible discount codes
* Form chat allows you to field questions directly from your registrant forms via Chatra.
* Payment support via Stripe directly to your account
* Auto-calculate and add credit card processor fees into base prices
* Split payments of registrant fees based on percentage ratios and due dates
* Responsive, mobile friendly forms with great UX
* Import your current vendor list via XLS file, including custom fields
* Venue location mapping, selection & pricing (ie. booth spaces)
* Map reservations & placement by organizer with price override/expiration date
* "Earlybird" fee discounts with auto-expiration date
* Amenity discount options, both global and form specific tied to early bird discounts
* Dynamic contract generation with e-signatures
* Supports multiple forms feeding a single event for greater flexibility
* Registrants can go back to registrant, make changes & payments at any time
* Customizable profile fields and sort order
* Application deposit payment support that are subtracted from remaining payments
* Applicant/vendor profiles and custom keywords
* Dynamic document requirements and uploads based on applicant/vendor type
* Streamlined document auditing
* Amenity selection and pricing (ie. tables, tents)
* email and SMS text blasts to individual and groups of vendors
* Form validation
* Easy sorting of form fields and form values (selectors)
* Granular "required" field settings along with asterisk (*) after field label
* and more...

== Automatic Installation ==

1. Go to **Plugins > Add New** in your WordPress dashboard.
1. Search `registerone` to find this plugin, by Vandenberg Media Inc.
1. Click **Install Now** to install it and then activate it after the installation.
1. **IMPORTANT**: Wordpress > Permalinks must be set to anything OTHER THAN "Plain/Default". Also see: <a href="https://codex.wordpress.org/Settings_Permalinks_Screen" target="_blank">Permalinks</a>

== Manual Installation ==

1. Download the plugin from <a href="http://www.registerone.com">RegisterONE.com</a> and follow the instructions on the page.

== Plugin Setup ==

1. Go to **Settings > RegisterONE Forms** or **Plugins > RegisterONE > Settings**
1. Generate an API key in your <a href="http://www.registerone.com">RegisterONE.com</a> account and paste it into the plugin API key tab.
1. From Event Form Shortcodes, select the shortcode you want and add it in a blank page. See the Help tab for more info.
1. **IMPORTANT**: Wordpress > Permalinks must be set to anything OTHER THAN "Plain/Default". Also see: <a href="https://codex.wordpress.org/Settings_Permalinks_Screen" target="_blank">Permalinks</a>

== 3rd Party service integrations ==

This plug-in allows you to enable a "form-chat" feature and allows you to have a text conversation with those who are filling out a form. This function is DISABLED by default and will only appear if you enable it under [RegisterONE > Forms > Settings > Form Chat].

**Supported Services**
Chatra.io <a href="https://chatra.io/" target="_blank">https://chatra.io/</a>
Also see Chatra <a href="https://chatra.io/terms-of-service/" target="_blank">terms of service</a> & <a href="https://chatra.io/privacy-policy/" target="_blank">privacy policy</a>

Hubspot Chat <a href="https://www.hubspot.com/" target="_blank">https://hubspot.com/</a>
If using Hubspot integration, your chat configuration will be pulled in from **js.hs-scripts.com** to ensure that new HubSpot features like messages or pop-up forms are automatically added to your form.
See <a href="https://knowledge.hubspot.com/reports/what-is-the-hs-scripts-embed-code-loading-on-my-website" target="_blank">what-is-the-hs-scripts</a> for more information.
Also see Hubspot <a href="https://legal.hubspot.com/terms-of-service" target="_blank">terms of service</a> & <a href="https://legal.hubspot.com/privacy-policy" target="_blank">privacy policy</a>

== Frequently Asked Questions ==

= What exactly does this plugin do? =

It displays your event vendor registration form. You first need to register on RegisterONE and create your first event. Once you do so and setup your API key, the plugin will display your registration form via event form shortcode. All form details are generated and saved to your <a href="http://www.registerone.com">RegisterONE.com</a> account.

For more faqs, please see our <a href="https://www.registerone.com/faq/">FAQ page</a>.

= What payment gateways do you support? =

We support Stripe & Paypal. All you need to do is authorize RegisterONE to accept payments on your behalf. We recommend Stripe because of the excellent reporting and management Stripe offers.

= What does it cost? =

The plugin is free to use. RegisterONE.com is free to try for your event vendor registration forms. You can setup everything and test with up to 5 applicants without paying a dime. Once you're ready to launch your event, we charge a small event launch fee to process more applicants. See RegisterONE.com for details on the launch fee and service pricing.

== Screenshots ==

1. Get your private API key from RegisterONE.com
2. Add your API key to the RegisterONE API Settings.
3. Access your event form shortcodes on the Shortcode tab.
4. Insert your shortcode into a blank Page (not a Post) and select the RegisterONE template for display.
5. Your vendor registration form will be displayed on your page.
6. Forms are mobile friendly for tablets and smartphones.
7. Document uploads are handled automatically based on vendor type.
8. Registrants can select the venue location or booth they want.
9. Vendor amenities are selectable, including venue setup location.
10. Event terms are displayed for them to sign off on.
11. e-Signatures can be made from desktops, tables or phones.
12. Payments can be made based on your payment preferences.

== Changelog ==

= 3.7.6 =
* Support added for Apple/iPhone HEIC image files uploads
* Updated duplicate badge detection routines

= 3.7.5 =
* Reformatted exhibitor keyword search dropdown
* Expanded exhibitor detail on floor map

= 3.7.4 =
* Added support for comments to map area selector

= 3.7.3 =
* Adjusted format of map area labels

= 3.7.2 =
* Optimized ticketing screen flow

= 3.7.1 =
* CSS mods for SSO login

= 3.7.0 =
* Map areas now display booth number/label over area
* Added ability to customize label size
* Added borders around area with custom controls for width and color
* Added background colors with custom controls based on area status

= 3.6.16 =
* Auto-recall contact fields on cart page
* Moved logo in map exhibitor detail popup
* Fixed width CSS of main map area

= 3.6.15 =
* FIX: Scroll issue on map vendor listing
* UPDATE: Added logo in exhibitor detail popup
* UPDATE: Exhibitor detail popup right justified with scrolling body content

= 3.6.14 =
* UPDATE: Added ACH labels to cart page for direct payments
* UPDATE: Remove bottom labels of cart page

= 3.6.13 =
* NEW: Badge design can be customized based on ticket type purchased. Multiple ticket types can be sold from one form.
* NEW: Badge profiles now warn user if a duplicate email is detected for the same event.

= 3.6.12 =
* NEW: setting to limit or disable keyword selections

= 3.6.11 =
* Added UI tweaks for better button spacing

= 3.6.10 =
* FIX: Correct json warning with mapping.

= 3.6.9 =
* UPDATE: Correct issues with last WP update and plugin.

= 3.6.8 =
* UPDATE: Set Terms and Conditions block as read-only.

= 3.6.7 =
* UPDATE: CC exp date selections

= 3.6.6 =
* NEW: Customizable invoice/ticket labels

= 3.6.5 =
* UPDATE: Chained session topic selectors display side by side

= 3.6.4 =
* UPDATE: Change footer formatting so it doesn't extend beyond main content block
* FIX: Autogrow js libs updated to correct hard width bug in hidden div style

= 3.6.3 =
* UPDATE: Added support for checkbox T&C agreement

= 3.6.2 =
* FIX: warnings for PHP::unset() calls

= 3.6.1 =
* NEW: Conference/Session speaker form type added to feed speaker profiles and session builder.
* NEW: Speaker/session track selectors added for Conference/Session forms.

= 3.6.0 =
* NEW: Map groupings per applicant type. Allows you to setup applicant type specific booths (ie. Sponsors).
* UPDATE: UX improvements for URL form fields
* UPDATE: UX improvements for footer when using custom backgrounds
* UPDATE: Added PPT file upload type

= 3.5.3 =
* UPDATE: Added custom badge fields to badge block in-form

= 3.5.2 =
* UPDATE: Tested compatability with WP v6
* NEW: Added dedicated navigation element for badging form

= 3.5.1 =
* UPDATE: Split payment Cancel button added
* UPDATE: Fixed minimum amount calculation for split payments

= 3.5.0 =
* NEW: External privacy policy link added to profile page
* UPDATE: Add extra notes for iframe setup
* UPDATE: Split payment form mods to clarify action
* UPDATE: Add border to inactive top nav buttons for better UX

= 3.4.15 =
* UPDATE: Add Ticket button to top of form

= 3.4.14 =
* FIX: Booth booking from forward facing map

= 3.4.13 =
* NEW: Split credit card payment option added to cart page.

= 3.4.12 =
* UPDATE: Added merge value for parent company for text blocks.

= 3.4.11 =
* NEW: Multi-form transport dropdown can be added to top of forms

= 3.4.10 =
* UPDATE: Tested WP 5.9 compatability.
* UPDATE: Check php::session state before creating new.
* UPDATE: Added amenity auto-select based on booth type.
* UPDATE: CSS mods for amenity qty block.

= 3.4.9 =
* UPDATE: CSS mods for notice block.
* UPDATE: Catch login condition if new profile without RSVP code.

= 3.4.8 =
* UPDATE: Shifted RSVP tracking to attendee/ticketing form flow.
* UPDATE: Calculate available amenity qty of child form based on parent amenity purchased.
* UPDATE: Corrected checkbox select state error when 0 available to select.

= 3.4.7 =
* UPDATE: Disable new profiles on RSVP form if owner code is missing.
* UPDATE: Added protocol prefix to RSVP URL.

= 3.4.6 =
* UPDATE: Remove Badge form link from non-scannable form blocks.

= 3.4.5 =
* NEW: Referral codes added to cart page for sales tracking.

= 3.4.4.1 =
* FIX: corrected field lock logic

= 3.4.4 =
* UPDATE: Remove contact info from bottom of cart page
* NEW: Added optional read-only profile fields editable by admins only.

= 3.4.3 =
* NEW: Ability to lock/unlock amenity order screen down by applicant after order is placed.
* NEW: Default phone country format can now be customized per form.

= 3.4.2.1 =
* FIX: Malformed variable corrected.

= 3.4.2 =
* NEW: Auto-approve setting for applicants by form.
* UPDATE: Added notice if RSVP tracking var is missing from URL.
* FIX: Added missing last navigation step if it was missing.

= 3.4.1 =
* UPDATE: Correct repo.

= 3.4.0 =
* NEW: RSVP forms added with tracking of signup origin and parent company.
* NEW: Optional "Pay by Check" button to display instructions and remove CC processor fee (if applicable).

= 3.3.19 =
* FIX: Session for forward-facing floor map booking.

= 3.3.18 =
* UPDATE: Tested WP 5.8 compatability.
* FIX: Debug call fixed.

= 3.3.17 =
* UPDATE: Better feedback when registrant limit reached.

= 3.3.16 =
* UPDATE: Better badge details and feedback.
* UPDATE: Partial badge details supported.

= 3.3.15 =
* UPDATE: Repo fix.

= 3.3.14 =
* UPDATE: Added international phone input for badging profiles.

= 3.3.13 =
* NEW: Added min/max quantity setting for amenities.

= 3.3.12 =
* UPDATE: Fixed credit card feedback/UX.

= 3.3.11 =
* UPDATE: Added more translation support for map button labels

= 3.3.10 =
* UPDATE: Tweaked form "closed" return screen

= 3.3.9 =
* NEW: Customize button label text for better multi-lingual support
* FIX: Flag required amenity selection before payment is allowed

= 3.3.8 =
* UPDATE: Calculate init map zoom levels relative to image scale zoom
* UPDATE: CSS tweaks amenity order selection display

= 3.3.7 =
* NEW: Set custom zoom level of front-facing floor map

= 3.3.6 =
* NEW: Ability to customize Booth selector text string
* UPDATE: Enable booth/seat mapping selection for ticketing forms
* UPDATE: Added responsive image CSS attributes for text blocks

= 3.3.5 =
* UPDATE: add new session for Paypal cart

= 3.3.4 =
* UPDATE: Fix sporadic issues with registrant audit sessions

= 3.3.3 =
* UPDATE: Pass deposit override data on cart form

= 3.3.2 =
* FIX: Paypal button didn't always display on cart page

= 3.3.1 =
* UPDATE: Paypal button display
* UPDATE: CSS mod for terms screen

= 3.3.0 =
* UPDATE: ticketing forms changed for better order flow
* UPDATE: UX improved for front-facing floor mapping
* UPDATE: better UX feedback for SSO creation

= 3.2.5 =
* UPDATE: hide split payment box on deposit option
* UPDATE: add international currency symbols for split payments

= 3.2.4 =
* NEW: International phone number formatting support
* NEW: Support for min/max file size for file uploads by type
* UPDATE: Added docs note for iframe bottom padding
* UPDATE: CSS tweaks
* FIX: load jquery as fallback on iframe shortcode page
* FIX: added detect on shortcode page edit

= 3.2.3 =
* UPDATE: Mods for better responsive forms and mobile support
* NEW: Added option for inline text fields instead of the stacked input format
* NEW: Added support for custom class tags for custom field form text blocks
* FIX: Small mods to support PHP 5.6, though PHP 7.x is recommended

= 3.2.2 =
* FIX: Added fallback for spinner id search

= 3.2.1 =
* UPDATE: Added params passthrough to iframe from iframe parent page
* UPDATE: Added spinner as iframe loads, hide when finished

= 3.2.0 =
* NEW: iframe shortcode to embed forms and maps inside WP theme
* UPDATE: Tweak forward-facing map CSS for better iframe support

= 3.1.13 =
* UPDATE: Added custom asset labels for exhibitor areas (ex. booths, tables, etc)
* UPDATE: Added option to toggle public facing map favorite tags on or off
* FIX: Corrected booth selection from front-facing map

= 3.1.12 =
* FIX: malformed tag for radio selectors

= 3.1.11 =
* UPDATE: added UI CSS libs for qty spinner controls
* UPDATE: added widget.min.js missing from WP 5.6
* FIX: corrected js redirect from uploads page

= 3.1.10 =
* UPDATE: updated 3rd party disclosure for Hubspot integration

= 3.1.9 =
* UPDATE: added more wp::filters

= 3.1.8 =
* UPDATE: implement wp::core jquery-ui
* UPDATE: implement wp::esc_* for added security
* UPDATE: check for php::curl before call

= 3.1.7 =
* UPDATE: CSS mods

= 3.1.6 =
* UPDATE: implement wp::esc_* for added security
* UPDATE: cleaned up js libs

= 3.1.5 =
* FIX: Corrected php::setcookie() params

= 3.1.4 =
* NEW: WP Multisite awareness added
* UPDATE: Tested for WP 5.5 compatibility

= 3.1.3 =
* UPDATE: Improved UX for ticketing - redirects to cart page after registration
* UPDATE: Changed button sizes to improve mobile UI

= 3.1.2 =
* UPDATE: Improved UX for applicant short links

= 3.1.1 =
* UPDATE: Changed default root page for ticketing to profile page
* UPDATE: Improved form UX for mobile devices

= 3.1.0 =
* NEW: Ticketing forms now support login/registration from cart page
* UPDATE: Enhanced cookie security
* UPDATE: Hide company field for ticketing form if not included on profile form
* UPDATE: Improved registrant auditing for organizers
* UPDATE: Improved popup notes format

= 3.0.6 =
* UPDATE: Updated select2 libs

= 3.0.5 =
* UPDATE: Add redirect to form root of invalid login key returns 404

= 3.0.4 =
* FIX: correct redirect fallback after login session expiration

= 3.0.3 =
* FIX: login session was not picked on cart page to create Paypal button

= 3.0.2 =
* UPDATE: registrant profiles are accessible in audit mode for organizers

= 3.0.1 =
* FIX: correct WP::DAY_IN_SECONDS for cookie functions
* UPDATE: better feedback for duplicate register emails

= 3.0.0 =
* NEW: Application login process to secure access to profiles
* NEW: GDPR/California compliant privacy policy rollout

== Upgrade Notice ==

= 3.7.6 =
* Support added for Apple/iPhone HEIC image files uploads