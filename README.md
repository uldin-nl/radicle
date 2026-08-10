# Radicle Package

This repo can be used to scaffold an Acorn package. See the [Acorn Package Development](https://roots.io/acorn/docs/package-development/) docs for further information.

## Installation

You can install this package with Composer:

```bash
composer require uldin/radicle
```

## Migrating existing installations

After updating an existing installation, migrate users from the legacy customer
role to the Uldin role with:

```shell
wp acorn uldin:migrate
```

The command is idempotent and keeps the legacy role for backwards compatibility.
Once no older code depends on that role, it can also be removed:

```shell
wp acorn uldin:migrate --remove-legacy-role
```

You can publish the config file with:

Flare config file

```shell
$ wp acorn vendor:publish --provider="Uldin\Radicle\Providers\FlareServiceProvider"
```

ACF config file

```shell
$ wp acorn vendor:publish --provider="Uldin\Radicle\Providers\AcfServiceProvider"
```
