<?php

namespace Modules\UserPermission\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

class ImportPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import or update permissions from a JSON file';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Load the JSON file
        $json = File::get(base_path('public/permission_acl.json'));
        $modules = json_decode($json, true)['modules'];

        foreach ($modules as $module) {
            $moduleLabel = $module['label'];
            foreach ($module['permissions'] as $permission) {
                $permissionName = $permission['name'];
                $controller = $permission['controller'];
                $label = $permission['Label'];

                $existingPermission = Permission::where('name', $permissionName)
                    ->where('guard_name', 'web')
                    ->first();

                if ($existingPermission) {
                    if ($existingPermission->controller !== $controller ||
                        $existingPermission->module !== $moduleLabel ||
                        $existingPermission->label !== $label) {

                        $existingPermission->update([
                            'controller' => $controller,
                            'module' => $moduleLabel,
                            'label' => $label,
                        ]);

                        $this->info("Permission '{$permissionName}' updated successfully.");
                    } else {
                        $this->info("Permission '{$permissionName}' is already up-to-date.");
                    }
                } else {
                    Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                        'controller' => $controller,
                        'module' => $moduleLabel,
                        'label' => $label,
                    ]);

                    $this->info("Permission '{$permissionName}' created successfully.");
                }
            }
        }

        $this->info('Permissions import and update process completed.');
    }
}
