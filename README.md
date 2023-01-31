# URNames

Mediawiki extension.

## Description

Version 1.1.2

Extension adds users' real name (only for logged in users) to following pages:

* History pages
* Recentchanges
* Listusers
* Activeusers
* BlockList

## Installation

* Make sure you have MediaWiki 1.39+ installed.
* Download and place the extension to your /extensions/ folder.
* Add the following code to your LocalSettings.php: `wfLoadExtension( 'URNames' )`;

## Release Notes

### 1.1.1
* Static SpecialPageFactory deprecated. Fix for MW 1.36.

### 1.1.2
* `$user->isLoggedIn()` deprecated. Replaced with `$user->isRegistered()`. Fix for MW 1.39.

## Authors and license

* [Josef Martiňák](https://www.wikiskripta.eu/w/User:Josmart)
* MIT License, Copyright (c) 2022 First Faculty of Medicine, Charles University