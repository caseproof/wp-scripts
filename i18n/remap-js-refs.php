<?php
/**
 * Rewrite JavaScript source references in a .pot so they point at the BUILT
 * bundle(s) that actually ship, instead of the `src-js/...` sources that are
 * stripped from the distributed plugin.
 *
 * WordPress locates a script's JS translation JSON by hashing the ENQUEUED
 * (built) path, and every translation tool — Loco Translate, `wp i18n
 * make-json`, the WordPress.org language-pack builder — derives the JSON
 * filename from the reference paths in the PO/POT. When make-pot scans the
 * source (which it must: the minified build OOMs the JS parser and drops
 * translator comments), those references are `src-js/...`, so:
 *   - Loco can't map the strings to a real script and generates nothing;
 *   - other tools name files no runtime lookup will ever find.
 * Rewriting the references to the built bundle fixes every tool at once.
 *
 * We can't infer source -> bundle from paths alone (shared modules inline into
 * several bundles; add-ons rename entries), so the mapping is produced by the
 * one tool that knows — webpack — and emitted as a committed JSON manifest:
 *
 *   { "src-js/shared/Header.js": ["assets/js/build/shared.js"],
 *     "pro/src-js/shared/util.js": ["pro/assets/js/build/a.js",
 *                                   "pro/assets/js/build/b.js"] }
 *
 * A source that lands in multiple bundles yields multiple references, so the
 * string is emitted into every bundle's JSON. Unmapped references (PHP source,
 * or JS not in the manifest) are left untouched.
 *
 * Usage: php remap-js-refs.php <pot-path> <manifest-path>
 */

if (!defined('STDIN')) { die("You're unauthorized to view this page."); }

if ($argc < 3) {
	fwrite(STDERR, "Usage: php remap-js-refs.php <pot-path> <manifest-path>\n");
	exit(1);
}

$pot_path      = $argv[1];
$manifest_path = $argv[2];

if (!file_exists($pot_path)) {
	fwrite(STDERR, "[remap-js-refs] POT not found: {$pot_path} — nothing to do\n");
	exit(0);
}

// Missing manifest is a no-op, not a failure: a plugin may set the config
// constant a release before its build starts emitting the manifest, and a
// deploy must not blow up over that.
if (!file_exists($manifest_path)) {
	fwrite(STDERR, "[remap-js-refs] manifest not found: {$manifest_path} — skipping remap\n");
	exit(0);
}

$map = json_decode((string) file_get_contents($manifest_path), true);
if (!is_array($map)) {
	fwrite(STDERR, "[remap-js-refs] manifest is not valid JSON: {$manifest_path} — skipping remap\n");
	exit(0);
}

$lines = file($pot_path, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
	fwrite(STDERR, "[remap-js-refs] cannot read POT: {$pot_path}\n");
	exit(1);
}

$out      = array();
$pending  = array();   // Deduped `#:` lines for the current entry (keys preserve order).
$rewrites = 0;

$flush = function () use (&$pending, &$out) {
	foreach (array_keys($pending) as $line) { $out[] = $line; }
	$pending = array();
};

foreach ($lines as $line) {
	if (strpos($line, '#: ') === 0) {
		// WP-CLI writes one reference per `#:` line, but tolerate several
		// space-separated refs on one line too.
		$refs = preg_split('/\s+/', trim(substr($line, 3)), -1, PREG_SPLIT_NO_EMPTY);
		foreach ($refs as $ref) {
			// Strip a trailing `:<line-number>`; paths never contain a colon.
			$path = preg_replace('/:\d+$/', '', $ref);
			if (isset($map[$path]) && is_array($map[$path])) {
				foreach ($map[$path] as $built) {
					$pending['#: ' . $built] = true;
				}
				$rewrites++;
			} else {
				$pending['#: ' . $ref] = true;
			}
		}
		continue;
	}

	if (!empty($pending)) { $flush(); }
	$out[] = $line;
}
if (!empty($pending)) { $flush(); }

file_put_contents($pot_path, implode("\n", $out) . "\n");
fwrite(STDERR, "[remap-js-refs] rewrote {$rewrites} JS reference(s) in {$pot_path} using {$manifest_path}\n");
