<?php

namespace Uldin\Radicle\Console;

use Illuminate\Console\Command;

class MigrateToUldinCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'uldin:migrate {--remove-legacy-role : Remove the legacy role after all users have been migrated}';

    /**
     * @var string
     */
    protected $description = 'Migrate Radicle data from the legacy branding to Uldin';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $legacyRoleName = 'outlawz_klant';
        $uldinRoleName = 'uldin_klant';
        $legacyRole = get_role($legacyRoleName);

        if (!get_role($uldinRoleName)) {
            $capabilities = $legacyRole
                ? $legacyRole->capabilities
                : $this->customerCapabilities();

            if ($capabilities === null) {
                $this->error('The Uldin role could not be created because the administrator role is unavailable.');

                return self::FAILURE;
            }

            add_role($uldinRoleName, 'Uldin klant', $capabilities);
            $this->info('Created the Uldin customer role.');
        }

        $count = 0;

        do {
            $users = get_users([
                'role' => $legacyRoleName,
                'fields' => 'all',
                'number' => 100,
            ]);

            foreach ($users as $user) {
                $user->add_role($uldinRoleName);
                $user->remove_role($legacyRoleName);
                $count++;
            }
        } while (count($users) === 100);

        $this->info(sprintf('Migrated %d customer%s to the Uldin role.', $count, $count === 1 ? '' : 's'));

        if ($this->option('remove-legacy-role') && get_role($legacyRoleName)) {
            remove_role($legacyRoleName);
            $this->info('Removed the legacy customer role.');
        } elseif (get_role($legacyRoleName)) {
            $this->line('Kept the legacy customer role for backwards compatibility.');
        }

        return self::SUCCESS;
    }

    /**
     * Build the default capabilities for the Uldin customer role.
     *
     * @return array<string, bool>|null
     */
    private function customerCapabilities()
    {
        $administrator = get_role('administrator');

        if (!$administrator) {
            return null;
        }

        $capabilities = $administrator->capabilities;

        foreach ([
            'update_core',
            'activate_plugins',
            'delete_plugins',
            'install_plugins',
            'update_plugins',
            'edit_themes',
            'switch_themes',
            'update_themes',
            'delete_themes',
            'install_themes',
            'promote_users',
        ] as $capability) {
            $capabilities[$capability] = false;
        }

        return $capabilities;
    }
}
