<?php

namespace Uldin\Radicle\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'radicle:install {--force : Overwrite existing files that differ from the blueprint}';

    protected $description = 'Install the Radicle project blueprint';

    public function handle(Filesystem $files): int
    {
        $sourceRoot = __DIR__.'/stubs/install';
        $targetRoot = $this->laravel->basePath();
        $installed = 0;
        $unchanged = 0;
        $conflicts = [];

        foreach ($files->allFiles($sourceRoot) as $source) {
            $relativePath = $source->getRelativePathname();
            $target = $targetRoot.DIRECTORY_SEPARATOR.$relativePath;
            $targetExists = $files->exists($target);

            if ($targetExists) {
                if (hash_file('sha256', $source->getPathname()) === hash_file('sha256', $target)) {
                    $unchanged++;
                    continue;
                }

                if (! $this->option('force')) {
                    $conflicts[] = $relativePath;
                    continue;
                }
            }

            $files->ensureDirectoryExists(dirname($target));
            $files->copy($source->getPathname(), $target);
            $action = $targetExists ? 'Overwritten' : 'Installed';
            $this->line("<info>{$action}</info> {$relativePath}");
            $installed++;
        }

        if ($conflicts !== []) {
            $this->newLine();
            $this->warn('Skipped files that already exist with different content:');
            foreach ($conflicts as $conflict) {
                $this->line('  - '.$conflict);
            }
            $this->newLine();
            $this->comment('Review them first, then rerun with --force to overwrite them.');
        }

        $this->newLine();
        $this->info("Radicle blueprint ready: {$installed} installed, {$unchanged} unchanged, ".count($conflicts).' skipped.');

        return $conflicts === [] ? self::SUCCESS : self::FAILURE;
    }
}
