<?php

namespace Chronos\Api\Controllers\Content;

use App\Http\Controllers\Controller;
use Chronos\Models\ContentField;
use Chronos\Models\ContentFieldset;
use Chronos\Models\ContentType;
use Chronos\Traits\FieldsetManagement;
use Chronos\Generators\SeedGenerator;
use Chronos\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentTypesController extends Controller
{
    use FieldsetManagement;

    public function index(Request $request)
    {
        $itemsPerPage = $request->has('perPage')
            ? $request->get('perPage') == 0 ? ContentType::query()->count() : $request->get('perPage')
            : Config::get('chronos.items_per_page');

        $q = ContentType::withCount('items');

        // filter
        if ($request->has('filters')) {
            $filters = $request->get('filters');

            if (isset($filters['search']) && $filters['search'] != '')
                $q->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // sort
        if ($request->has('sortBy') && $request->get('sortBy') != '') {
            $sortBy = $request->get('sortBy');
            $sortOrder = $request->get('sortOrder');

            switch ($sortBy) {
                case 'name':
                case 'items_count':
                    $q->orderBy($sortBy, (isset($sortOrder) && $sortOrder === 'true') ? 'ASC' : 'DESC');
                    break;

                default:
                    return response()->json([
                        'alerts' => [
                            (object) [
                                'type' => 'error',
                                'title' => trans('chronos::alerts.Error.'),
                                'message' => trans('chronos::alerts.Invalid sortBy argument: :arg.', ['arg' => $sortBy]),
                            ]
                        ],
                        'status' => 200
                    ], 200);
            }
        }
        else
            $q->orderBy('name', 'ASC');

        // pagination
        $data = $q->paginate($itemsPerPage);



        return response()->json($data, 200);
    }

    public function destroy(ContentType $type)
    {
        // delete permissions
        $permissions = Permission::whereIn('name', [
            'view_content_type_' . $type->id,
            'add_content_type_' . $type->id,
            'edit_content_type_' . $type->id,
            'edit_content_type_fieldsets_' . $type->id,
            'delete_content_type_' . $type->id,
            'export_content_type_' . $type->id,
            'lock_content_type_delete_' . $type->id
        ])->delete();

        if ($type->delete())
            return response()->json([
                'alerts' => [
                    (object) [
                        'type' => 'success',
                        'title' => trans('chronos::alerts.Success.'),
                        'message' => trans('chronos::alerts.Content type successfully deleted.'),
                    ]
                ],
                'status' => 200
            ], 200);
        else
            return response()->json([
                'alerts' => [
                    (object) [
                        'type' => 'error',
                        'title' => trans('chronos::alerts.Error.'),
                        'message' => trans('chronos::alerts.Content type deletion was unsuccessful.'),
                    ]
                ],
                'status' => 500
            ], 500);
    }

    public function destroy_bulk(Request $request)
    {
        $deleted_types_count = 0;

        if ($request->has('types')) {
            foreach ($request->get('types') as $type_id) {
                $type = ContentType::find($type_id);

                // delete permissions
                Permission::where('name','view_content_type_' . $type->id)->first()->delete();
                Permission::where('name','add_content_type_' . $type->id)->first()->delete();
                Permission::where('name','edit_content_type_' . $type->id)->first()->delete();
                Permission::where('name','edit_content_type_fieldsets_' . $type->id)->first()->delete();
                Permission::where('name','delete_content_type_' . $type->id)->first()->delete();
                Permission::where('name','export_content_type_' . $type->id)->first()->delete();
                Permission::where('name','lock_content_type_delete_' . $type->id)->first()->delete();

                if ($type->delete())
                    $deleted_types_count++;
            }
        }

        if ($deleted_types_count > 0) {
            return response()->json([
                'alerts' => [
                    (object)[
                        'type' => 'success',
                        'title' => trans('chronos::alerts.Success.'),
                        'message' => trans_choice('chronos::alerts.:count types deleted.', $deleted_types_count, ['count' => $deleted_types_count])
                    ]
                ],
                'status' => 200
            ], 200);
        } else {
            return response()->json([
                'alerts' => [
                    (object)[
                        'type' => 'warning',
                        'title' => trans('chronos::alerts.Warning.'),
                        'message' => trans_choice('chronos::alerts.:count types deleted.', $deleted_types_count, ['count' => $deleted_types_count])
                    ]
                ],
                'status' => 200
            ], 200);
        }
    }

    public function destroy_field(ContentField $field)
    {
        if ($field->delete())
            return response()->json([
                'alerts' => [
                    (object) [
                        'type' => 'success',
                        'title' => trans('chronos::alerts.Success.'),
                        'message' => trans('chronos::alerts.Field successfully deleted.'),
                    ]
                ],
                'status' => 200
            ], 200);
        else
            return response(null, 500);
    }

    public function destroy_fieldset(ContentFieldset $fieldset)
    {
        if ($fieldset->delete())
            return response()->json([
                'alerts' => [
                    (object) [
                        'type' => 'success',
                        'title' => trans('chronos::alerts.Success.'),
                        'message' => trans('chronos::alerts.Fieldset successfully deleted.'),
                    ]
                ],
                'status' => 200
            ], 200);
        else
            return response(null, 500);
    }

    public function export(Request $request)
    {
        if (!$request->has('types'))
            $types = ContentType::whereNotIn('name', Config::get('chronos.system_content_types'))->get();
        else
            $types = ContentType::whereIn('id', $request->get('types'))->get();

        $generator = new SeedGenerator();
        $generator->setFilename('ContentTypesTableSeeder.php');
        $generator->setClassName('ContentTypesTableSeeder');

        $generator->addUses('Chronos\Models\ContentField');
        $generator->addUses('Chronos\Models\ContentFieldset');
        $generator->addUses('Chronos\Models\ContentType');
        $generator->addUses('Chronos\Models\Permission');

        $generator->addNewLines();

        foreach ($types as $key => $type) {
            // create content type
            $generator->addIndent(2);
            $generator->addContent('/*');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent(' * ' . $type->name);
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent(' */');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('$type = ContentType::create([');
            $generator->addNewLines();
            foreach ($type->getFillable() as $attribute) {
                $value = is_string($type->{$attribute}) ? '"' . addslashes(normalize_newline($type->{$attribute})) . '"' :
                        (is_null($type->{$attribute}) ? 'null' : $type->{$attribute});
                $generator->addIndent(3);
                $generator->addContent('\'' . $attribute . '\' => ' . $value . ',');
                $generator->addNewLines();
            }

            $generator->addIndent(2);
            $generator->addContent(']);');
            $generator->addNewLines(2);

            // create fieldsets
            $fieldsets = ContentFieldset::where('parent_id', $type->id)->where('parent_type', get_class($type))->get();

            foreach ($fieldsets as $fieldset) {
                $generator->addIndent(2);
                $generator->addContent('$fieldset = ContentFieldset::create([');
                $generator->addNewLines();

                $generator->addIndent(3);
                $generator->addContent('\'parent_id\' => $type->id,');
                $generator->addNewLines();

                $except = ['parent_id'];
                foreach ($fieldset->getFillable() as $attribute) {
                    if (!in_array($attribute, $except)) {
                        $value = is_string($fieldset->{$attribute}) ? '"' . addslashes(normalize_newline($fieldset->{$attribute})) . '"' :
                                (is_null($fieldset->{$attribute}) ? 'null' : $fieldset->{$attribute});
                        $generator->addIndent(3);
                        $generator->addContent('\'' . $attribute . '\' => ' . $value . ',');
                        $generator->addNewLines();
                    }
                }

                $generator->addIndent(2);
                $generator->addContent(']);');
                $generator->addNewLines(2);

                // create fields
                $fields = ContentField::where('fieldset_id', $fieldset->id)->get();

                foreach ($fields as $field) {
                    // check if entity type, and referencing a content type
                    $entity_data = unserialize($field->data);
                    if (
                        $field->type == 'entity' &&
                        isset($entity_data['entity_type']) &&
                        $entity_data['entity_type'] !== '\\' . ltrim(Config::get('auth.providers.users.model'), '\\')
                    ) {
                        list($type_model, $type_id) = explode(':', $entity_data['entity_type']);

                        $type = ContentType::where('id', $type_id)->firstOrFail();

                        $generator->addIndent(2);
                        $generator->addContent('$entity = ContentType::where(\'name\', \'' . $type->name . '\')->first();');
                        $generator->addNewLines();

                        $generator->addIndent(2);
                        $generator->addContent('if ($entity) {');
                        $generator->addNewLines();

                        $generator->addIndent(3);
                        $generator->addContent('$value = [\'entity_type\' => \'' . $type_model . ':\' . $entity->id . \'\'];');
                        $generator->addNewLines();

                        $data = 'serialize($value)';

                        $generator->addIndent(3);
                        $generator->addContent('ContentField::create([');
                        $generator->addNewLines();

                        $generator->addIndent(3);
                        $generator->addContent('\'fieldset_id\' => $fieldset->id,');
                        $generator->addNewLines();

                        $generator->addIndent(3);
                        $generator->addContent('\'data\' => ' . $data . ',');
                        $generator->addNewLines();

                        $except = ['fieldset_id', 'data'];
                        foreach ($field->getFillable() as $attribute) {
                            if (!in_array($attribute, $except)) {
                                $value = is_string($field->{$attribute}) ? '"' . addslashes(normalize_newline($field->{$attribute})) . '"' :
                                    (is_null($field->{$attribute}) ? 'null' : $field->{$attribute});

                                $generator->addIndent(3);
                                $generator->addContent('\'' . $attribute . '\' => ' . $value . ',');
                                $generator->addNewLines();
                            }
                        }

                        $generator->addIndent(3);
                        $generator->addContent(']);');
                        $generator->addNewLines();

                        $generator->addIndent(2);
                        $generator->addContent('}');
                        $generator->addNewLines(2);
                    }
                    // default behaviour
                    else {
                        $generator->addIndent(2);
                        $generator->addContent('ContentField::create([');
                        $generator->addNewLines();

                        $generator->addIndent(3);
                        $generator->addContent('\'fieldset_id\' => $fieldset->id,');
                        $generator->addNewLines();

                        $except = ['fieldset_id'];
                        foreach ($field->getFillable() as $attribute) {
                            if (!in_array($attribute, $except)) {
                                $value = is_string($field->{$attribute}) ? '"' . addslashes(normalize_newline($field->{$attribute})) . '"' :
                                    (is_null($field->{$attribute}) ? 'null' : $field->{$attribute});

                                $generator->addIndent(3);
                                $generator->addContent('\'' . $attribute . '\' => ' . $value . ',');
                                $generator->addNewLines();
                            }
                        }

                        $generator->addIndent(2);
                        $generator->addContent(']);');
                        $generator->addNewLines(2);
                    }
                }
            }

            // create permissions
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'view_content_type_\' . $type->id, \'label\' => trans(\'chronos::permissions.View :name\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'add_content_type_\' . $type->id, \'label\' => trans(\'chronos::permissions.Add :name\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'edit_content_type_\' . $type->id, \'label\' => trans(\'chronos::permissions.Edit :name\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'edit_content_type_fieldsets_\' . $type->id, \'label\' => trans(\'chronos::permissions.Edit :name fieldsets\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'delete_content_type_\' . $type->id, \'label\' => trans(\'chronos::permissions.Delete :name\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'export_content_type_\' . $type->id, \'label\' => trans(\'chronos::permissions.Delete :name\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();
            $generator->addIndent(2);
            $generator->addContent('Permission::create([\'name\' => \'lock_content_type_delete_\' . $type->id, \'label\' => trans(\'chronos::permissions.Lock :name delete\' , [\'name\' => Str::plural(strtolower($type->name))]), \'order\' => 10]);');
            $generator->addNewLines();

            if ($key < count($types) - 1)
                $generator->addNewLines(3);
        }

        $generator->generateFile();
        $generator->downloadFile();
    }

    public function fieldset(Request $request, ContentType $type)
    {
        $this->validateFieldsetRequest($request);

        // let fieldset management handle update
        $this->updateAll($request, $type);

        return response()->json([
            'alerts' => [
                (object) [
                    'type' => 'success',
                    'title' => trans('chronos::alerts.Success.'),
                    'message' => trans('chronos::alerts.Fieldsets successfully updated.'),
                ]
            ],
            'status' => 200
        ], 200);
    }

    public function show(Request $request, ContentType $type)
    {
        if ($request->has('load') && $request->get('load') == 'fieldsets')
            $type->load('fieldsets');

        return response()->json($type, 200);
    }

    public function store(Request $request)
    {
        // validate input
        $this->validate($request, [
            'name' => 'required|unique:content_types',
            'title_label' => 'required'
        ]);

        // create content type
        $type = ContentType::create([
            'name' => $request->get('name'),
            'title_label' => $request->get('title_label'),
            'translatable' => $request->has('translatable'),
            'notes' => $request->get('notes'),
        ]);

        //create permissions
        Permission::create(['name' => 'view_content_type_' . $type->id, 'label' => trans('chronos::permissions.View :name' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);
        Permission::create(['name' => 'add_content_type_' . $type->id, 'label' => trans('chronos::permissions.Add :name' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);
        Permission::create(['name' => 'edit_content_type_' . $type->id, 'label' => trans('chronos::permissions.Edit :name' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);
        Permission::create(['name' => 'edit_content_type_fieldsets_' . $type->id, 'label' => trans('chronos::permissions.Edit :name fieldsets' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);
        Permission::create(['name' => 'delete_content_type_' . $type->id, 'label' => trans('chronos::permissions.Delete :name' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);
        Permission::create(['name' => 'export_content_type_' . $type->id, 'label' => trans('chronos::permissions.Export :name' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);
        Permission::create(['name' => 'lock_content_type_delete_' . $type->id, 'label' => trans('chronos::permissions.Lock :name delete' , ['name' => Str::plural(strtolower($type->name))]), 'order' => 10]);

        return response()->json([
            'alerts' => [
                (object) [
                    'type' => 'success',
                    'title' => trans('chronos::alerts.Success.'),
                    'message' => trans('chronos::alerts.Content type successfully created.'),
                ]
            ],
            'type' => $type,
            'status' => 200
        ], 200);
    }

    public function update(Request $request, ContentType $type)
    {
        // validate input
        $this->validate($request, [
            'name' => [
                'required',
                Rule::unique('content_types')->ignore($type->id)
            ],
            'title_label' => 'required'
        ]);

        // handle update
        $type->update([
            'name' => $request->get('name'),
            'title_label' => $request->get('title_label'),
            'translatable' => $request->has('translatable'),
            'notes' => $request->get('notes')
        ]);

        return response()->json([
            'alerts' => [
                (object) [
                    'type' => 'success',
                    'title' => trans('chronos::alerts.Success.'),
                    'message' => trans('chronos::alerts.Content type successfully updated.'),
                ]
            ],
            'type' => $type,
            'status' => 200
        ], 200);
    }

}
