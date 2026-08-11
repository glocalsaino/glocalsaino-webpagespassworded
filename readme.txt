=== WebPagesPassworded ===
Contributors: rafamm-glocalsaino
Tags: password, protected pages, child pages, access control, shortcode
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 4.3.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Single password form for groups of password-protected child pages. Enter the password and get redirected to the right child page automatically.

== Description ==

**WebPagesPassworded** creates a single access page that acts as a gateway for a group of password-protected child pages. Place the `[wppw]` shortcode on a parent page: the plugin renders a password form and, on a correct match, redirects the visitor directly to the matching child page.

= How it works =

1. In WordPress, create a parent page (e.g. "Private Area") and set the pages you want to protect as its children.
2. Assign each child page its own password via **Publish → Visibility → Password protected** in the editor.
3. Insert the `[wppw]` shortcode on the parent page.
4. When a visitor arrives, they see the plugin's password form. If the password matches a child page, they are redirected to it automatically.

= Features =

* **Single entry point** — one page with the shortcode gives access to all protected child pages.
* **Automatic redirect** — the visitor lands directly on the right page without extra steps.
* **Session cookie** — the password is stored in a cookie for 10 days so the visitor does not need to re-enter it on subsequent visits.
* **Security nonce** — the form includes a WordPress nonce to guard submissions against CSRF attacks.
* **Clear error message** — if the password is wrong, an error is shown inline in the same form.
* **Brute-force protection** — after 5 failed attempts from the same IP, access is locked for 15 minutes. The lockout uses WordPress transients, with no extra database tables.
* **Secure cookie** — the session cookie is set with `HttpOnly` and `SameSite=Strict` flags to block JavaScript access and cross-site submission.
* **No external dependencies** — the plugin loads no external libraries or remote resources.

= Usage =

Insert the shortcode on the parent page that will act as the access form:

`[wppw]`

Optional shortcode parameters:

* `label` — submit button text. Default: `Enter`.
* `id` — `id` attribute of the `<form>` element. Default: `wppwLogin`.
* `parent` — ID of the parent page whose children will be searched. Default: the current page ID.

Example with parameters:

`[wppw label="Log in" id="my-form"]`

= Requirements =

* WordPress 5.0 or higher.
* PHP 7.4 or higher.
* Child pages must be published and have a password configured from the WordPress editor.

== Screenshots ==

1. Password form on the front-end, with a custom icon and styles applied via the premium version.
2. Settings panel: general settings section (button text and shortcode reference) and customisable error messages (premium).
3. Settings panel: form design section with colour pickers, font size controls, and a Font Awesome icon picker (premium).
4. Settings panel: magic links section with the creation form and a table of existing links showing status and actions (premium).

== Installation ==

1. Upload the `webpagespassworded` folder to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins → Installed Plugins**.
3. Create a parent page and add child pages with their passwords via **Publish → Visibility → Password protected**.
4. Insert `[wppw]` on the parent page.

== Frequently Asked Questions ==

= Can I have several parent pages each with their own group of child pages? =

Yes. The `[wppw]` shortcode placed on each parent page searches only among its direct child pages. Each group is independent.

= What happens if two child pages share the same password? =

The visitor is redirected to the most recently published child page that matches the entered password.

= How long does the access cookie last? =

10 days. After that, or if the visitor clears their browser cookies, they will need to enter the password again.

= Does it work with HTTPS? =

Yes. If the site uses HTTPS the cookie is automatically marked as `Secure`.

= I entered the correct password but I see the lockout message. What should I do? =

The lockout lasts 15 minutes and is tied to the IP address. If you are the administrator and need to clear it early, delete the transients with the prefix `wppw_lock_` and `wppw_fail_` from the database or with a transient management plugin.

= Is it compatible with page-exclusion plugins? =

Yes. If the site has a plugin that exposes `pause_exclude_pages()` / `resume_exclude_pages()` (a common pattern in menu and listing exclusion plugins), WebPagesPassworded calls them around its query to ensure protected pages are always found.

= The password is correct but the visitor is not redirected. What could be wrong? =

Check that:

* The child page is published (not a draft).
* The child page is a direct child of the page containing the shortcode, not a grandchild.
* The password in WordPress is saved without leading or trailing spaces.

== Privacy Policy ==

WebPagesPassworded does not collect, store, or transmit any personal data.

* The plugin reads the password entered in the form only to compare it against the passwords of the child pages stored in the WordPress database. That comparison happens entirely on the server and the data is not saved or sent to any third party.
* A cookie (`wp-postpass_*`) is stored in the visitor's browser to maintain access for 10 days. This is a standard WordPress cookie and contains only the hash of the password, never the password in plain text.
* No external connections of any kind are made.

== Upgrade Notice ==

= 4.3.0 =
The login links feature has been moved to the standalone WP Login Links plugin. No migration needed in WebPagesPassworded.

= 4.1.0 =
New premium feature: magic links for direct access without a password. No migration required.

= 4.0.0 =
Major version with Freemius integration and premium features. Backwards compatible: the `[wppw]` shortcode continues to work as before.

= 3.1.0 =
Recommended security update. Adds brute-force protection and improves session cookie flags.

= 3.0.0 =
The shortcode has changed from `[smartpwpages]` to `[wppw]`. Replace the shortcode on all pages where it is inserted.

== Changelog ==

= 4.3.0 =
* The login links feature has been extracted to the standalone WP Login Links plugin.

= 4.2.0 =
* New premium feature: login links. Generates signed links that authenticate a WordPress user directly, without entering a password.
* The login link token is a 256-bit random value; it never contains or exposes user credentials.
* Configurable expiry and maximum uses (default: 1 day, 1 use).
* Configurable post-login redirect URL per link.
* Admin panel for creating, listing, and revoking login links, with one-click clipboard copy.

= 4.1.0 =
* New premium feature: magic links. Generates links that grant direct access to a protected page without asking for the password.
* Magic links use a 256-bit random token (not the real password), with configurable expiry and maximum uses.
* Admin panel for creating, listing, and revoking magic links, with one-click clipboard copy.
* Fixed Font Awesome enqueue: it was loading too late inside wp_head and never printing.
* Replaced has_shortcode() detection (which failed with Elementor and other page builders) with a check based on is_singular().
* Added !important to generated CSS values to prevent the active theme from overriding the configured design.

= 4.0.0 =
* Freemius integration for licence management and premium version.
* New panel in Settings → WebPagesPassworded with free and premium sections.
* Premium: customisable button text from the settings panel.
* Premium: customisable error messages (wrong password and lockout).
* Premium: configurable form design (input background, text and border colours; button colour, size and font).
* Premium: customisable button icon, selected from a Font Awesome icon grid.
* Premium: configurable spacing between the password field and the button.
* Code refactored into separate classes.
* Button changed from `<input type="submit">` to `<button type="submit">` to allow HTML content.
* CSS injected only on pages that contain the `[wppw]` shortcode.

= 3.1.0 =
* Added brute-force protection: 15-minute lockout after 5 failed attempts per IP.
* Session cookie set with `HttpOnly` and `SameSite=Strict` flags.
* Added direct file access guard.
* Fixed incorrect password comparison in form processing.

= 3.0.0 =
* Full refactor for PHP 7.4+ and WordPress 5.0+ compatibility.
* Removed use of `extract()`.
* Added explicit visibility to all class methods.
* Added return type hints to main methods.
* Improved sanitisation of POST data.
* New shortcode and form field names with `wppw` prefix.
