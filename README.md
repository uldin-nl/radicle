# Radicle Package

This repo can be used to scaffold an Acorn package. See the [Acorn Package Development](https://roots.io/acorn/docs/package-development/) docs for further information.

## Installation

You can install this package with Composer:

```bash
composer require uldin/radicle
```

You can publish the config file with:

Flare config file

```shell
$ wp acorn vendor:publish --provider="uldin\Radicle\Providers\FlareServiceProvider"
```

ACF config file

```shell
$ wp acorn vendor:publish --provider="uldin\Radicle\Providers\AcfServiceProvider"
```
