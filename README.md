# Flysystem Chroot Adapter

[![Author](http://img.shields.io/badge/author-@fisharebest-blue.svg?style=flat-square)](https://github.com/fisharebest)
[![Latest Stable Version](https://poser.pugx.org/fisharebest/flysystem-chroot-adapter/v/stable.svg)](https://packagist.org/packages/fisharebest/flysystem-chroot-adapter)
[![Build Status](https://travis-ci.org/fisharebest/flysystem-chroot-adapter.svg?branch=main)](https://travis-ci.org/fisharebest/flysystem-chroot-adapter)
[![Coverage Status](https://coveralls.io/repos/github/fisharebest/flysystem-chroot-adapter/badge.svg?branch=main)](https://coveralls.io/github/fisharebest/flysystem-chroot-adapter?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fisharebest/flysystem-chroot-adapter/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/fisharebest/flysystem-chroot-adapter/?branch=main)
[![StyleCI](https://github.styleci.io/repos/166235152/shield?branch=main)](https://github.styleci.io/repos/166235152)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This adapter creates a new filesystem from a sub-folder of an existing filesystem.

----------

*IMPORTANT: Since flysystem 3.3, this functionality is now available from [flysystem directly](https://flysystem.thephpleague.com/docs/adapter/path-prefixing/).*
You should migrate to that package.

In composer.json, replace `fisharebest/flysystem-chroot-adapter` with `league/flysystem-path-prefixing`.

In your code, replace `Fisharebest\Flysystem\Adapter\ChrootAdapter` with `League\Flysystem\PathPrefixing`.

----------

## Installation

```bash
composer require fisharebest/flysystem-chroot-adapter
```

## Usage

```php
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Fisharebest\Flysystem\Adapter\ChrootAdapter

// Write a file to a filesystem.
$filesystem = new Filesystem(new Local(__DIR__));
$filesystem->write('foo/bar/fab/file.txt', 'hello world!');

// Create a chroot filesystem from the foo/bar folder.
$chroot = new Filesystem(new ChrootAdapter($filesystem, 'foo/bar'));

// And read it back from the chroot.
$chroot->read('fab/file.txt'); // 'hello world!'
```
