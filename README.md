# Vetra Dashboard

A modern, fully-featured **user dashboard and account panel for WordPress**, built from scratch and completely **independent from WooCommerce**.

Vetra Dashboard replaces the default WordPress profile experience with a polished, RTL-first control panel including a ticket system, notifications, polls, attachments, banking, a custom wallet, SMS OTP authentication and a complete admin area.

## Highlights

- **Dashboard** with live statistics and configurable quick shortcuts
- **Profile** with custom fields, avatar upload, email/phone verification, password change and attachments
- **Support tickets** with departments, priorities, statuses, satisfaction rating, file attachments and a dedicated support-staff center
- **Notifications** with per-user read state
- **Polls** (single and multiple choice) with live results
- **Attachments / downloads** targeted to all users, a role, or a specific user
- **Bank cards** with an approval workflow
- **Custom wallet** with transactions and withdrawal requests
- **Comments** overview
- **Login / registration / password reset** with password or SMS OTP
- **SMS** integration for IPPanel (Edge API + Legacy API) and a generic webhook provider
- **Templated transactional emails**
- **Modern UI**: RTL-first, automatic dark mode, responsive, full-width panel template
- **Persian admin experience**: redesigned right-to-left settings screens, Persian dashboard navigation and Jalali date entry/display
- **GPL-2.0-or-later** licensed, no encrypted or obfuscated code

## Requirements

- WordPress 6.2+
- PHP 7.4+

Persian date fields are entered and displayed in the Jalali calendar; dates are stored as Gregorian ISO values for WordPress compatibility.

## Installation

1. Copy the `vetra-dashboard` folder to `wp-content/plugins/`.
2. Activate **Vetra Dashboard** from the Plugins screen.
3. Open **Vetra → Settings** and configure pages, design, modules, SMS and email.
4. The pages `vtd-panel`, `vtd-login`, `vtd-register` and `vtd-reset` are created automatically.

## Shortcodes

| Shortcode | Description |
|---|---|
| `[vetra_dashboard]` | Full user panel |
| `[vetra_login]` | Login form |
| `[vetra_register]` | Registration form |
| `[vetra_reset_password]` | Password reset |
| `[vetra_profile_links]` | Compact account links widget |

## SMS (IPPanel)

Vetra uses the official IPPanel Edge API:

- Base URL: `https://edge.ippanel.com/v1`
- Endpoint: `POST /api/send`
- Header: `Authorization: <Access Key>`

Both `webservice` (plain message) and `pattern` (template/OTP) modes are supported, plus the legacy `api2.ippanel.com` endpoints.

## Project structure

```
vetra-dashboard/
├── includes/     # core: plugin bootstrap, installer, options, DB, roles, assets, router, REST
├── modules/      # auth, profile, dashboard, tickets, notifications, polls, attachments, banking, wallet, comments, sms
├── admin/        # admin menu, settings screen and list management
├── templates/    # front-end and email templates
├── assets/       # CSS and JavaScript
└── languages/    # translation files
```

## License

GPL-2.0-or-later. See <https://www.gnu.org/licenses/gpl-2.0.html>.
