<?php

namespace Chronos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Content extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content';

    /**
     * The attributes that are appended to the model's JSON form
     *
     * @var array
     */
    protected $appends = ['admin_urls', 'endpoints', 'translation_codes'];

    /**
     * The dynamic content values that are appended to the model
     *
     * @var array
     */
    protected $custom_attributes = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['status_scheduled'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'slug', 'title', 'author_id', 'language', 'translation_id', 'parent_id', 'order', 'status', 'status_scheduled', 'lock_delete'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['author', 'children'];



    /**
     * Generates cache key for model
     *
     * As we include the updated_at timestamp in the cache key,
     * it will be invalidated whenever model gets updated
     *
     * @return string
     */
    private function cacheKey($model = null)
    {
        if (!$model) {
            $model = $this;
        }

        return sprintf(
            "%s/%s-%s",
            $model->getTable(),
            $model->getKey(),
            $model->updated_at->timestamp
        );
    }



    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->custom_attributes[$key]))
            return $this->custom_attributes[$key];

        return $this->getAttribute($key);
    }

    /**
     * Determine if a dynamic attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        if (isset($this->custom_attributes[$key]))
            return true;

        return ! is_null($this->getAttribute($key));
    }



    /**
     * Override newFromBuilder() to add custom field values to our model.
     * These will be accessed via __get magic method.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes);

        $custom_attributes = Cache::remember($this->cacheKey($model) . ':custom_attributes:v2', 60 * 60 * 24 * 7, function () use ($model) {
            return $this->buildCustomAttributes($model);
        });

        $model->custom_attributes = $this->restoreCustomAttributes($custom_attributes);

        return $model;
    }

    /**
     * Build dynamic field values in a cache-safe array shape.
     *
     * @param \Chronos\Models\Content $model
     * @return array
     */
    private function buildCustomAttributes($model)
    {
        $custom_attributes = [];

        $values = ContentFieldData::where('content_id', $model->attributes['id'])->get();
        foreach ($values as $value) {
            $field = $value->field;
            $fieldset = $value->field->fieldset;
            $field_value = $this->normalizeCacheValue($this->parseStoredFieldValue($value->value));
            unset($filtered_value);

            if ($field->type == 'text' && $field->widget == 'wysiwyg') {
                $filtered_value = filter_wysiwyg($field_value);
            }

            if ($fieldset->repeatable) {
                if (!isset($custom_attributes[$fieldset->machine_name][$value->fieldset_repetition_key]))
                    $custom_attributes[$fieldset->machine_name][$value->fieldset_repetition_key] = [];

                if ($field->repeatable) {
                    $custom_attributes[$fieldset->machine_name][$value->fieldset_repetition_key][$field->machine_name][$value->field_repetition_key] = $field_value;
                    if (isset($filtered_value))
                        $custom_attributes[$fieldset->machine_name][$value->fieldset_repetition_key][$field->machine_name . '_filtered'][$value->field_repetition_key] = $filtered_value;
                } else {
                    $custom_attributes[$fieldset->machine_name][$value->fieldset_repetition_key][$field->machine_name] = $field_value;
                    if (isset($filtered_value))
                        $custom_attributes[$fieldset->machine_name][$value->fieldset_repetition_key][$field->machine_name . '_filtered'] = $filtered_value;
                }
            } else {
                if (!isset($custom_attributes[$fieldset->machine_name]))
                    $custom_attributes[$fieldset->machine_name] = [];

                if ($field->repeatable) {
                    $custom_attributes[$fieldset->machine_name][$field->machine_name][$value->field_repetition_key] = $field_value;
                    if (isset($filtered_value))
                        $custom_attributes[$fieldset->machine_name][$field->machine_name . '_filtered'][$value->field_repetition_key] = $filtered_value;
                } else {
                    $custom_attributes[$fieldset->machine_name][$field->machine_name] = $field_value;
                    if (isset($filtered_value))
                        $custom_attributes[$fieldset->machine_name][$field->machine_name . '_filtered'] = $filtered_value;
                }
            }
        }

        return $custom_attributes;
    }

    /**
     * Restore cached custom field arrays to their object-like public shape.
     *
     * @param array $custom_attributes
     * @return array
     */
    private function restoreCustomAttributes($custom_attributes)
    {
        if (!is_array($custom_attributes)) {
            return [];
        }

        foreach ($custom_attributes as $key => $value) {
            if ($this->hasOnlyIntegerKeys($value)) {
                foreach ($value as $repetition_key => $repetition_value) {
                    $value[$repetition_key] = $this->arrayToObject($repetition_value);
                }

                $custom_attributes[$key] = $value;
            } else {
                $custom_attributes[$key] = $this->arrayToObject($value);
            }
        }

        return $custom_attributes;
    }

    /**
     * Parse serialized field values when needed.
     *
     * @param mixed $value
     * @return mixed
     */
    private function parseStoredFieldValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        if (@unserialize($value) !== false || $value == 'b:0;') {
            return unserialize($value);
        }

        return $value;
    }

    /**
     * Ensure cached custom values do not contain arbitrary objects.
     *
     * @param mixed $value
     * @return mixed
     */
    private function normalizeCacheValue($value)
    {
        if ($value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        } elseif ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            $value = $value->toArray();
        } elseif (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeCacheValue($item);
            }
        }

        return $value;
    }

    /**
     * Recursively turn associative arrays into stdClass objects.
     *
     * @param mixed $value
     * @return mixed
     */
    private function arrayToObject($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $object = new \stdClass();
        foreach ($value as $key => $item) {
            $object->{$key} = $item;
        }

        return $object;
    }

    /**
     * Determine whether an array only uses integer keys.
     *
     * @param mixed $value
     * @return bool
     */
    private function hasOnlyIntegerKeys($value)
    {
        if (!is_array($value)) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Override toArray() method to include custom field values in our model's array and JSON object.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->custom_attributes ? array_merge(parent::toArray(), $this->custom_attributes) : parent::toArray();
    }



    /**
     * Add admin URLs to model.
     */
    public function getAdminUrlsAttribute()
    {
        $id = $this->attributes['id'];
        $type = $this->attributes['type_id'];

        $urls['edit'] = route('chronos.content.edit', ['type' => $type, 'content' => $id]);
        $urls['edit_fieldsets'] = route('chronos.content.fieldset', ['type' => $type, 'content' => $id]);

        if (settings('is_multilanguage') && ContentType::find($type)->translatable) {
            foreach ($this->translations as $translation) {
                $urls['translations'][$translation->language] = route('chronos.content.edit', ['type' => $translation->type, 'content' => $translation->id]);
            }
        }

        return $urls;
    }

    /**
     * Add fieldsets to model.
     *
     * This is where the magic happens. Basically this function iterates over each fieldset and field,
     * and if there is a value saved it adds it to the field's JSON object. If there are multiple values, repetitions
     * are added, which can be read on the frontend.
     */
    public function getAllFieldsetsAttribute()
    {
        $fieldsets = [];
        $all_fieldsets = [];

        if ($this->type->fieldsets)
            $all_fieldsets = $this->type->fieldsets;
        if ($this->fieldsets)
            $all_fieldsets = $all_fieldsets->merge($this->fieldsets);

        if ($all_fieldsets) {
            foreach ($all_fieldsets as $fieldset) {
                $fieldset_orig = clone $fieldset;
                $fieldset_repetitions = [$fieldset_orig];

                foreach ($fieldset->fields as $field) {
                    $field_orig = clone $field;
                    $field_repetitions = [$field_orig];

                    $field_values = [];
                    $values = ContentFieldData::query()->where('content_id', $this->id)->where('field_id', $field->id)->get();

                    if ($values) {
                        foreach ($values as $valueData) {
                            $valueData->value = $this->parseStoredFieldValue($valueData->value);

                            if (!isset($fieldset_repetitions[$valueData->fieldset_repetition_key]))
                                $fieldset_repetitions[$valueData->fieldset_repetition_key] = clone $fieldset_orig;
                            if (!isset($field_repetitions[$valueData->field_repetition_key]))
                                $field_repetitions[$valueData->field_repetition_key] = clone $field_orig;

                            $field_values[$valueData->fieldset_repetition_key][$valueData->field_repetition_key] = $valueData->value;
                        }
                    }
                    $field->value = $field_values;
                    $field->repetitions = $field_repetitions;
                }

                $fieldset->repetitions = $fieldset_repetitions;
                $fieldsets[] = $fieldset;
            }
        }

        return $fieldsets;
    }

    /**
     * Add endpoints to model.
     */
    public function getEndpointsAttribute()
    {
        $id = $this->attributes['id'];
        $type = $this->attributes['type_id'];
        $endpoints['index'] = route('api.content', ['type' => $type]);
        $endpoints['destroy'] = route('api.content.destroy', ['type' => $type, 'content' => $id]);

        if (settings('is_multilanguage') && ContentType::find($type)->translatable)
            $endpoints['translate'] = route('api.content.translate', ['type' => $type, 'content' => $id]);

        return $endpoints;
    }

    /**
     * Add language to model.
     */
    public function getLanguageNameAttribute()
    {
        return array_search($this->language, array_column(\Config::get('languages.list'), 'code', 'name'));
    }

    /**
     * Get path for item.
     */
    public function getPathAttribute()
    {
        // add type
        $path = $this->type->path;

        // add parents
        $crumbs = $this->crumbs;
        $crumbs_path = '';
        while ($crumbs) {
            $crumbs_path = '/' . $crumbs->slug . $crumbs_path;
            $crumbs = $crumbs->crumbs;
        }
        $path .= $crumbs_path;

        // add slug
        $path .= '/' . $this->slug;

        return $path;
    }

    /**
     * Get content translations.
     */
    public function getTranslationsAttribute()
    {
        if (!settings('is_multilanguage') || !ContentType::find($this->type_id)->translatable)
            return null;

        $parent = $this->translation_id !== null ? Content::where('id', $this->translation_id)->first() : Content::where('id', $this->id)->first();
        $translations = Content::where('translation_id', $parent->id)->get();

        return $translations->merge([$parent]);
    }

    /**
     * Get content translations.
     */
    public function getTranslationCodesAttribute()
    {
        $translations = $this->translations;

        if (!$translations)
            return [];

        return $translations->map(function($content) {
            return $content->language;
        });
    }

    /**
     * Set slug by transliterating.
     *
     * @param $value
     */
    public function setTitleAttribute($value) {
        $this->attributes['title'] = $value;

        if (!$this->slug) {
            $slug = str_transliterate($value);
            $slugCount = self::whereRaw('slug REGEXP "^' . $slug . '(-[0-9]*)?$"')->count();
            if ($slugCount > 0)
                $slug = $slug . '-' . $slugCount;

            $this->attributes['slug'] = $slug;
        }
    }



    /**
     * Scope a query to only include active content.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }



    /**
     * Get content author.
     */
    public function author()
    {
        return $this->belongsTo('\App\Models\User');
    }

    /**
     * Get the children for the item.
     */
    public function children()
    {
        return $this->hasMany('\Chronos\Models\Content', 'parent_id');
    }

    /**
     * Recursively return folders' parents.
     *
     * @return mixed
     */
    public function crumbs() {
        return $this->parent()->with('crumbs');
    }

    /**
     * Get contents own fieldsets.
     */
    public function fieldsets()
    {
        return $this->morphMany('\Chronos\Models\ContentFieldset', 'parent');
    }

    /**
     * Get content type.
     */
    public function parent()
    {
        return $this->belongsTo('\Chronos\Models\Content');
    }

    /**
     * Get content type.
     */
    public function type()
    {
        return $this->belongsTo('\Chronos\Models\ContentType');
    }
}
