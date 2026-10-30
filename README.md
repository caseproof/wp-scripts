wp-scripts
==========

A set of scripts that can be placed within the root of a plugin to offer rails-style console (thanks to wpshell), i18n stuff and more

## Getting Started

### Go to the root of your wordpress project

```
cd path/to/wp/plugin/or/theme
```
### Clone the repo ... make sure the directory is named 'script'

```
git clone https://github.com/Caseproof/wp-scripts script
```

### Put 'script' into your project's gitignore ... don't want it to be included do we? :)

```
echo 'script/' >> .gitignore
```

### Copy the sample config to the actual config

```
cp ./script/wp-script-config.sample.php ./script/wp-script-config.php
```

### Edit the config file with the specifics for your project

```
vim ./script/wp-script-config.php
```

## BOOM! Now you can do stuff like this:

```
./script/console
coolio@booyah:~/bevisshanks.com/wp-content/plugins/memberpress-importer$ ./script/console
coolio@bevisshanks.com:memberpress-importer > echo get_option('home');

http://bevisshanks.com

coolio@bevisshanks.com:memberpress-importer > 
```

And

```
./script/check_syntax
No syntax errors detected in ./MpimpImporterFactory.php
No syntax errors detected in ./MpimpBaseImporter.php
No syntax errors detected in ./mothership-config.php
No syntax errors detected in ./importers/MpimpTransactionsImporter.php
```

And

```
./script/mki18n
find . -regex "^\.\/[^/]*\.php" -exec /usr/bin/env php ./script/i18n/add-textdomain.php -i 'memberpress' {} \;
find . -regex "^\.\/app.*\.php" -exec /usr/bin/env php ./script/i18n/add-textdomain.php -i 'memberpress' {} \;
/usr/bin/env wp i18n make-pot . ./i18n/memberpress.pot --slug=memberpress --domain=memberpress
```

`wp i18n make-pot` extracts strings from PHP **and** JS/JSX/TSX in one pass,
so JavaScript `__('text', 'my-domain')` calls are picked up automatically.

### Multiple textdomains in one project

For projects that bundle a Pro add-on (or any second textdomain) inside the
same repo, define the optional `_2` constants in `wp-script-config.php`:

```
define('WP_SCRIPT_TEXTDOMAIN', 'my-plugin');
define('WP_SCRIPT_LANGUAGES_DIR', 'languages');
define('WP_SCRIPT_EXCLUDE', 'pro,vendor,node_modules,assets/js/build,tests,docs,bin');

define('WP_SCRIPT_TEXTDOMAIN_2', 'my-plugin-pro');
define('WP_SCRIPT_LANGUAGES_DIR_2', 'pro/languages');
define('WP_SCRIPT_INCLUDE_2', 'pro');
define('WP_SCRIPT_EXCLUDE_2', 'pro/vendor,pro/node_modules,pro/assets/js/build');
```

> **Don't exclude `vendor-prefixed`.** Plugins that use Strauss to bundle
> in-house libraries (`ground-level-*`, etc.) ship real user-facing strings in
> that tree, tagged with the plugin's own textdomain — excluding it silently
> drops them from the `.pot`. For the same reason, exclude built JS output
> (`assets/js/build`) rather than the whole `assets/` directory, since
> `wp i18n make-pot` also extracts translatable strings from JS.

`mki18n` will then run two passes — `./languages/my-plugin.pot` and
`./pro/languages/my-plugin-pro.pot` — each scoped to its own subtree and
filtered to its own textdomain. Single-domain projects that don't define
`WP_SCRIPT_TEXTDOMAIN_2` get the original behavior unchanged.

The auto-tag step (`add-textdomain.php`) is skipped when a secondary domain
is configured, since an untagged call can't be unambiguously assigned to
either domain. Two-domain projects must tag every gettext call explicitly.

### JavaScript translation references (`WP_SCRIPT_JS_BUNDLE_MAP`)

**The problem this solves.** A React/`@wordpress/i18n` plugin won't translate
its JS UI unless you do this, and it fails silently. WordPress locates a
script's JS translation JSON by hashing the **enqueued (built) script path**
(e.g. `assets/js/build/shared.js`) — the file is `<domain>-<locale>-<md5>.json`
where `<md5>` is `md5` of that path. But `wp i18n make-pot` scans **source**, so
the `#:` references it writes are `src-js/...` — and `src-js` is stripped from
the distributed plugin. Every translation tool derives its JSON filename from
those references, so with `src-js` references:

- **Loco Translate generates no JSON at all** — its compiler skips any
  reference whose file isn't present in the shipped bundle.
- **`wp i18n make-json` / WordPress.org** name files after the `src-js` hash,
  which no runtime lookup will ever match.

Either way PHP strings translate and the React UI stays English. You **cannot**
fix this by scanning the built dir instead (`--exclude` the source, extract from
`assets/js/build`): the minified bundles OOM the JS parser and lose translator
comments.

**The fix.** Keep scanning source, then rewrite the JS references to the built
bundle they ship in. `mki18n` does this automatically after `make-pot` when you
point it at a webpack-emitted `source -> built bundle` manifest:

```
define('WP_SCRIPT_JS_BUNDLE_MAP', 'languages/i18n-bundle-map.json');
// and, for a second (Pro) domain:
define('WP_SCRIPT_JS_BUNDLE_MAP_2', 'pro/languages/i18n-bundle-map.json');
```

The manifest is `{ "src-js/Foo.jsx": ["assets/js/build/bundle.js"] }` — values
are arrays because a shared module can inline into several bundles, and each
gets a reference. Only webpack knows this mapping (entry names get renamed,
shared code fans out), so it must be emitted by the build, not guessed from
paths. A missing manifest is a no-op, so it's safe to set the constant before
the build starts emitting one. See `i18n/remap-js-refs.php` for the rewrite and
`WP_SCRIPT_JS_BUNDLE_MAP` in `wp-script-config.sample.php`.

And (if you have a mothership project associated with this git repo)

```
./script/deploy 1.1.1b7
```

### Releasing to wordpress.org (`WP_SCRIPT_WPORG_ONLY`)

`mcdeployall` and `mcdevdeploy` deploy every release to Mothership. A plugin hosted on wordpress.org, deployed by a workflow that runs when a version tag is pushed, opts out in its `wp-script-config.php`:

```
define('WP_SCRIPT_WPORG_ONLY', true);
```

For that plugin `mcdeployall` and `mcdevdeploy`:

- skip the Mothership checks and deploy, so the `MOTHERSHIP_*` constants can be left empty;
- leave `[ci skip]` off the version bump commit. The release tag points at that commit, and GitHub starts no push workflows for a commit whose message contains `[ci skip]`, tag pushes included;
- on `X.Y.Z` releases, set `Stable tag:` in `readme.txt` to the new version in the bump commit, since wordpress.org serves the tag that line names. Prereleases leave it alone.

Only `true` opts in. If `readme.txt` has no `Stable tag:` line, `mcdeployall` stops before releasing any plugin, and `mcdevdeploy` stops before running any git command. Plugins that do not define the constant release through Mothership with `[ci skip]` on the bump commit.





