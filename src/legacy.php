<?php

/**
 * Backwards-compatible class names for sites built before the Uldin rename.
 *
 * Keep these aliases until all consuming sites use the Uldin namespace.
 */
$legacyClasses = [
    'OutlawzTeam\\Radicle\\Acf' => \Uldin\Radicle\Acf::class,
    'OutlawzTeam\\Radicle\\Flare' => \Uldin\Radicle\Flare::class,
    'OutlawzTeam\\Radicle\\Console\\MakeAcfCommand' => \Uldin\Radicle\Console\MakeAcfCommand::class,
    'OutlawzTeam\\Radicle\\Console\\MigrateToUldinCommand' => \Uldin\Radicle\Console\MigrateToUldinCommand::class,
    'OutlawzTeam\\Radicle\\Facades\\Acf' => \Uldin\Radicle\Facades\Acf::class,
    'OutlawzTeam\\Radicle\\Facades\\Flare' => \Uldin\Radicle\Facades\Flare::class,
    'OutlawzTeam\\Radicle\\Facades\\Login' => \Uldin\Radicle\Facades\Login::class,
    'OutlawzTeam\\Radicle\\Providers\\AcfServiceProvider' => \Uldin\Radicle\Providers\AcfServiceProvider::class,
    'OutlawzTeam\\Radicle\\Providers\\FlareServiceProvider' => \Uldin\Radicle\Providers\FlareServiceProvider::class,
    'OutlawzTeam\\Radicle\\Providers\\LoginServiceProvider' => \Uldin\Radicle\Providers\LoginServiceProvider::class,
    'OutlawzTeam\\Radicle\\Support\\Acf' => \Uldin\Radicle\Support\Acf::class,
    'OutlawzTeam\\Radicle\\Support\\JetformsFieldGenerator' => \Uldin\Radicle\Support\JetformsFieldGenerator::class,
];

spl_autoload_register(static function (string $class) use ($legacyClasses): void {
    if (!isset($legacyClasses[$class]) || class_exists($class, false)) {
        return;
    }

    class_alias($legacyClasses[$class], $class);
});
