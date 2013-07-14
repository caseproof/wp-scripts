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
echo 'script' >> .gitignore
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
coolio@booyah.caseproof.com:memberpress-importer > echo get_option('home');

http://bevisshanks.com

coolio@booyah.caseproof.com:memberpress-importer > 
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
./script/i18n
```






