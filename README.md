# EasyDB (Caching)

[![Build Status](https://github.com/paragonie/easydb-cache/actions/workflows/ci.yml/badge.svg)](https://github.com/paragonie/easydb-cache/actions)
[![Latest Stable Version](https://poser.pugx.org/paragonie/easydb-cache/v/stable)](https://packagist.org/packages/paragonie/easydb-cache)
[![Latest Unstable Version](https://poser.pugx.org/paragonie/easydb-cache/v/unstable)](https://packagist.org/packages/paragonie/easydb-cache)
[![License](https://poser.pugx.org/paragonie/easydb-cache/license)](https://packagist.org/packages/paragonie/easydb-cache)
[![Downloads](https://img.shields.io/packagist/dt/paragonie/easydb-cache.svg)](https://packagist.org/packages/paragonie/easydb-cache)

Extends [EasyDB](https://github.com/paragonie/easydb), caches Prepared Statements
to reduce the number of database round trips. **Requires PHP 8.0 or newer.**

## Installing

```terminal
composer require paragonie/easydb-cache
```

## Usage

To use EasyDB with prepared statement caching, you can either change the class you're importing
in your code, or update your code to use `EasyDBCache` instead. Alternatively, you can use the
named constructor with your existing object.

Afterwards, the EasyDB API is exactly the same as EasyDBCache.

### Updating Import Statements

```diff
- use ParagonIE\EasyDB\EasyDB;
+ use ParagonIE\EasyDB\EasyDBCache;
```

### Updating Your Code

```diff
use ParagonIE\EasyDB\EasyDB;
+ use ParagonIE\EasyDB\EasyDBCache;

- $db = new EasyDB(
+ $db = new EasyDBCache(
```

### Named Constructor

```diff
+ use ParagonIE\EasyDB\EasyDBCache;

- $db = new EasyDB(/* ... */);
+ $db = EasyDBCache::fromEasyDB(new EasyDB(/* ... */));
```

## Support Contracts

If your company uses this library in their products or services, you may be
interested in [purchasing a support contract from Paragon Initiative Enterprises](https://paragonie.com/enterprise).
