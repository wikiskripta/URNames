# URNames

Mediawiki extension.

## Description

Version 2.0.0

It shows the user's real name in brackets after the username, if the real name is set.

Supported views:
* page history (`action=history`)
* `Special:RecentChanges`
* `Special:ListUsers`
* `Special:ActiveUsers`
* `Special:BlockList`

Visible only to logged-in users.


## Installation

* Make sure you have MediaWiki 1.45+ installed.
* Download and place the extension to your /extensions/ folder.
* Add the following code to your LocalSettings.php: `wfLoadExtension( 'URNames' )`;

## Configuration
Default values:

```php
$wgURNamesPages = [
    'Recentchanges',
    'Activeusers',
    'BlockList',
    'Listusers',
    'history'
];

$wgURNamesCacheTTL = 86400;
```

## Release Notes

### 1.1.1
* Static SpecialPageFactory deprecated. Fix for MW 1.36.

### 1.1.2
* `$user->isLoggedIn()` deprecated. Replaced with `$user->isRegistered()`. Fix for MW 1.39.

### 2.0.0
* This version uses a deep integration approach for MediaWiki 1.45.
* This build does **not** post-process the final HTML of the page. Instead, it redefines the `UserLinkRenderer` service during MediaWiki service bootstrap and appends real names when MediaWiki renders user links on supported pages.
* That makes it lighter and safer than regex-based output rewriting.


## Authors and license

* [Josef Martiňák](https://www.wikiskripta.eu/w/User:Josmart)
* MIT License, Copyright (c) 2026 First Faculty of Medicine, Charles University







