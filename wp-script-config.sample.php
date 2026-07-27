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
//
// Do NOT exclude vendor-prefixed: Strauss-bundled in-house libraries
// (ground-level-*, etc.) carry user-facing strings tagged with the plugin's
// own textdomain, so excluding it silently drops them from the .pot. Prefer
// excluding built JS output (assets/js/build) over all of assets/, or you'll
// lose translatable JS strings (make-pot extracts JS too).
// define('WP_SCRIPT_EXCLUDE', 'pro,vendor,node_modules,assets/js/build,tests,docs,bin');

// Comma-separated paths to restrict the primary scan to.
// Mirrors `wp i18n make-pot --include=...`. Usually unnecessary for a
// single-domain plugin; leave undefined to scan everything not excluded.
// define('WP_SCRIPT_INCLUDE', '');

// Path (relative to plugin root) to a webpack-emitted source->built-bundle
// JSON manifest. When set, mki18n rewrites the JS references in the generated
// .pot from `src-js/...` sources to the built bundle(s) they ship in. This is
// what makes JavaScript translations work regardless of tooling: WordPress
// finds a script's JSON by hashing the ENQUEUED (built) path, and Loco /
// `wp i18n make-json` / WordPress.org all name their JSON from the .pot's
// references — so those references must be the built paths, not the stripped
// `src-js/...` sources. Scanning stays on the source (the minified build OOMs
// the JS parser and loses translator comments); only references are rewritten.
// Manifest shape (values are arrays — a shared module can inline into several
// bundles):
//   { "src-js/shared/Header.js": ["assets/js/build/shared.js"] }
// A missing manifest is a no-op, so it's safe to set this before the build
// starts emitting one. See i18n/remap-js-refs.php.
// define('WP_SCRIPT_JS_BUNDLE_MAP', 'languages/i18n-bundle-map.json');

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
// define('WP_SCRIPT_JS_BUNDLE_MAP_2', 'pro/languages/i18n-bundle-map.json');

// ===== Optional: whitespace normalization (rmtabs) =====
// The deploy runs `rmtabs`, which converts indentation tabs -> 2 spaces in
// distributed source (.php, .css, .js by default). If a project's JS (or CSS)
// indentation is owned by Prettier/ESLint and follows the WordPress *tabs*
// standard, list those extensions here so rmtabs leaves them alone — otherwise
// it fights the linter and breaks the lint-js CI gate. Comma-separated, bare
// extensions. Undefined/empty (the default) processes all extensions.
// define('WP_SCRIPT_RMTABS_EXCLUDE_EXTS', 'js');

// ===== Mothership / deploy =====
define('MY_PLUGIN_MAIN_FILE', 'main.php');
define('MOTHERSHIP_URL','');
define('MOTHERSHIP_API_KEY','');
define('MOTHERSHIP_PROJECT_ID','');
