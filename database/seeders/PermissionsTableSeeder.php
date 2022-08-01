<?php

namespace Chronos\Database\Seeders;

use Chronos\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder {

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        // Scaffolding
        Permission::create([
            'name' => 'view_dashboard',
            'label' => trans('chronos::permissions.View dashboard'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_settings',
            'label' => trans('chronos::permissions.Edit settings'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_access_tokens',
            'label' => trans('chronos::permissions.Edit access tokens'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'delete_access_tokens',
            'label' => trans('chronos::permissions.Delete access tokens'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'view_image_styles',
            'label' => trans('chronos::permissions.View image styles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'add_image_styles',
            'label' => trans('chronos::permissions.Add image styles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_image_styles',
            'label' => trans('chronos::permissions.Edit images styles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'delete_image_styles',
            'label' => trans('chronos::permissions.Delete image styles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'view_roles',
            'label' => trans('chronos::permissions.View roles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'add_roles',
            'label' => trans('chronos::permissions.Add roles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_roles',
            'label' => trans('chronos::permissions.Edit roles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'delete_roles',
            'label' => trans('chronos::permissions.Delete roles'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_permissions',
            'label' => trans('chronos::permissions.Edit permissions'),
            'order' => 10
        ]);

        // Content
        Permission::create([
            'name' => 'view_content_types',
            'label' => trans('chronos::permissions.View content types'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'add_content_types',
            'label' => trans('chronos::permissions.Add content types'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_content_types',
            'label' => trans('chronos::permissions.Edit content types'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_content_type_fieldsets',
            'label' => trans('chronos::permissions.Edit content type fieldsets'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'delete_content_types',
            'label' => trans('chronos::permissions.Delete content types'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'export_content_types',
            'label' => trans('chronos::permissions.Export content types'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'view_media',
            'label' => trans('chronos::permissions.View media'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'upload_media',
            'label' => trans('chronos::permissions.Upload media'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'delete_media',
            'label' => trans('chronos::permissions.Delete media'),
            'order' => 10
        ]);

        Permission::create([
            'name' => 'edit_languages',
            'label' => trans('chronos::permissions.Edit language settings'),
            'order' => 10
        ]);
    }

}