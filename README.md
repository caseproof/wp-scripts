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
define('WP_SCRIPT_EXCLUDE', 'pro,vendor,vendor-prefixed,node_modules,assets,tests,docs,bin');

define('WP_SCRIPT_TEXTDOMAIN_2', 'my-plugin-pro');
define('WP_SCRIPT_LANGUAGES_DIR_2', 'pro/languages');
define('WP_SCRIPT_INCLUDE_2', 'pro');
define('WP_SCRIPT_EXCLUDE_2', 'pro/vendor,pro/vendor-prefixed,pro/node_modules,pro/assets');
```

`mki18n` will then run two passes — `./languages/my-plugin.pot` and
`./pro/languages/my-plugin-pro.pot` — each scoped to its own subtree and
filtered to its own textdomain. Single-domain projects that don't define
`WP_SCRIPT_TEXTDOMAIN_2` get the original behavior unchanged.

The auto-tag step (`add-textdomain.php`) is skipped when a secondary domain
is configured, since an untagged call can't be unambiguously assigned to
either domain. Two-domain projects must tag every gettext call explicitly.

And (if you have a mothership project associated with this git repo)

```
./script/deploy 1.1.1b7
```





