=== Vetra Dashboard ===
Contributors: vetra
Tags: user dashboard, panel, tickets, sms, otp, profile, wallet, notifications
Requires at least: 6.2
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later

A modern, independent user dashboard and account panel for WordPress. No WooCommerce required.

== Description ==

Vetra Dashboard replaces the default WordPress profile experience with a complete user panel:

* Dashboard overview with statistics and quick shortcuts
* Profile management with custom fields, avatar upload and verification
* Support ticket system with departments, priorities, statuses, ratings and a staff center
* Notifications with per-user read state
* Polls (single and multiple choice)
* Attachments and downloads per user, role or everyone
* Bank card management with approval workflow
* Custom wallet with transactions and withdrawal requests
* Comments overview
* Login, registration and password reset with email and SMS OTP
* IPPanel SMS integration (Edge API and Legacy API) plus a generic webhook provider
* Modern RTL-first UI with automatic dark mode
* Persian RTL administration screens and Jalali date entry/display
* Custom dashboard entries for links, WordPress pages, shortcodes and safe content
* Configurable avatar menu, optional Digits shortcode login and rich email-template editors

== Installation ==

1. Upload the `vetra-dashboard` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Visit `Vetra > Settings` and configure pages, design, SMTP/SMS and modules.

== Shortcodes ==

* `[vetra_dashboard]` - the full user panel
* `[vetra_login]` - login form
* `[vetra_register]` - registration form
* `[vetra_reset_password]` - password reset
* `[vetra_profile_links]` - compact account links widget
* `[vetra_panel_menu]` - navigation links for available dashboard sections
* `[vetra_panel_section id="section-slug"]` - render a registered dashboard section

== Changelog ==

= 1.0.0 =
* Initial release.

= 1.1.0 =
* Redesigned Persian RTL administration settings and management screens.
* Added Jalali calendar date entry and Jalali date formatting for plugin dates.

= 1.2.0 =
* Fixed the admin submenu callbacks that caused WordPress critical errors.
* Added configurable panel menu items, avatar dropdown navigation and panel-content shortcodes.
* Added a Persian translation layer for the user dashboard and messages.
* Added an optional Digits login shortcode integration and visual email template editors with previews.

= 1.3.0 =
* Hardened all list screens and module queries against missing/corrupted plugin tables on PHP 8 (no more fatal errors).
* Added friendly "no records" empty states to every admin management table.
* Minor PHP 8 compatibility fixes for poll statistics and dashboard stats.
