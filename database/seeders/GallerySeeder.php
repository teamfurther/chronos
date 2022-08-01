<?php

namespace Chronos\Database\Seeders;

use Chronos\Models\ContentField;
use Chronos\Models\ContentFieldset;
use Chronos\Models\ContentType;
use Chronos\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder {

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $gallery_type = ContentType::create([
            'name' => 'Gallery',
            'notes' => ''
        ]);

        $images_fieldset = ContentFieldset::create([
            'parent_id' => $gallery_type->id,
            'parent_type' => get_class($gallery_type),
            'name' => 'Images',
            'machine_name' => 'images',
            'description' => '',
            'repeatable' => 1
        ]);

        ContentField::create([
            'fieldset_id' => $images_fieldset->id,
            'name' => 'File',
            'machine_name' => 'file',
            'type' => 'image',
            'widget' => 'media',
            'help_text' => '',
            'rules' => '',
            'data' => 'a:2:{s:10:"enable_alt";b:1;s:12:"enable_title";b:1;}'
        ]);

        //create permissions
        Permission::create(['name' => 'view_content_type_' . $gallery_type->id, 'label' => trans('chronos::permissions.View :name' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
        Permission::create(['name' => 'add_content_type_' . $gallery_type->id, 'label' => trans('chronos::permissions.Add :name' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
        Permission::create(['name' => 'edit_content_type_' . $gallery_type->id, 'label' => trans('chronos::permissions.Edit :name' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
        Permission::create(['name' => 'edit_content_type_fieldsets_' . $gallery_type->id, 'label' => trans('chronos::permissions.Edit :name fieldsets' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
        Permission::create(['name' => 'delete_content_type_' . $gallery_type->id, 'label' => trans('chronos::permissions.Delete :name' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
        Permission::create(['name' => 'export_content_type_' . $gallery_type->id, 'label' => trans('chronos::permissions.Export :name' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
        Permission::create(['name' => 'lock_content_type_delete_' . $gallery_type->id, 'label' => trans('chronos::permissions.Lock :name delete' , ['name' => Str::plural(strtolower($gallery_type->name))]), 'order' => 10]);
    }

}