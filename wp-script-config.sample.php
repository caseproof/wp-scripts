<?php
if(!defined('STDIN')) { die("You're unauthorized to view this page."); }

// ===== Required =====
// Primary textdomain — used by mki18n, deploy, etc.
define('WP_SCRIPT_TEXTDOMAIN','');

// ===== Optional: i18n customization =====
// Directory where the .pot is written for the primary textdomain.
// Defaults to 'i18n' (back-compat). Common modern choice: 'languages'.
// If you pass a directory name as the first CLI arg to mki18n, that wins
// over this constant.
// define('WP_SCRIPT_LANGUAGES_DIR', 'languages');

// Comma-separated paths to exclude from the primary scan.
// Mirrors `wp i18n make-pot --exclude=...`.
// Default (when undefined): nothing extra is excluded.
// define('WP_SCRIPT_EXCLUDE', 'pro,vendor,vendor-prefixed,node_modules,assets,tests,docs,bin');

// Comma-separated paths to restrict the primary scan to.
// Mirrors `wp i18n make-pot --include=...`. Usually unnecessary for a
// single-domain plugin; leave undefined to scan everything not excluded.
// define('WP_SCRIPT_INCLUDE', '');

// ===== Optional: secondary textdomain (e.g. Pro add-on bundled in same repo) =====
// Define WP_SCRIPT_TEXTDOMAIN_2 to a non-empty string to make mki18n run a
// second pass with this domain. Each `_2` constant mirrors its primary
// counterpart. If WP_SCRIPT_TEXTDOMAIN_2 is undefined or empty, the second
// pass is skipped — single-domain projects are unaffected.
//
// NOTE: when a secondary domain is configured, the auto-tag step
// (add-textdomain.php) is skipped, since auto-tagging cannot tell which
// domain an untagged string should belong to. Two-domain plugins must
// tag every gettext call explicitly.
// define('WP_SCRIPT_TEXTDOMAIN_2', '');
// define('WP_SCRIPT_LANGUAGES_DIR_2', '');
// define('WP_SCRIPT_EXCLUDE_2', '');
// define('WP_SCRIPT_INCLUDE_2', '');

// ===== Mothership / deploy =====
define('MY_PLUGIN_MAIN_FILE', 'main.php');
define('MOTHERSHIP_URL','');
define('MOTHERSHIP_API_KEY','');
define('MOTHERSHIP_PROJECT_ID','');
