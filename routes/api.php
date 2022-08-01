<?php

/* CONTENT */
Route::get('content/manage/{type}', ['uses' => 'Content\ContentController@index', 'as' => 'api.content']);
Route::get('content/manage/{type}/export', ['uses' => 'Content\ContentController@export', 'as' => 'api.content.export']);
Route::delete('content/manage/{type}/{content}', ['uses' => 'Content\ContentController@destroy', 'as' => 'api.content.destroy']);
Route::delete('content/manage/{type}', ['uses' => 'Content\ContentController@destroy_bulk', 'as' => 'api.content.destroy_bulk']);
Route::patch('content/manage/{type}/{content}/fieldset', ['uses' => 'Content\ContentController@fieldset', 'as' => 'api.content.fieldset']);
Route::get('content/manage/{type}/{content}', ['uses' => 'Content\ContentController@show', 'as' => 'api.content.show']);
Route::get('content/manage/{type}/{content}/translate', ['uses' => 'Content\ContentController@translate', 'as' => 'api.content.translate']);
Route::post('content/manage/{type}', ['uses' => 'Content\ContentController@store', 'as' => 'api.content.store']);
Route::patch('content/manage/{type}/{content}', ['uses' => 'Content\ContentController@update', 'as' => 'api.content.update']);

/* CONTENT TYPES */
Route::get('content/types/export', ['uses' => 'Content\ContentTypesController@export', 'as' => 'api.content.types.export']);
Route::resource('content/types', 'Content\ContentTypesController', ['except' => ['create', 'edit'], 'names' => [
    'index' => 'api.content.types',
    'destroy' => 'api.content.types.destroy',
    'show' => 'api.content.types.show',
    'store' => 'api.content.types.store',
    'update' => 'api.content.types.update'
]]);
Route::delete('content/types', ['uses' => 'Content\ContentTypesController@destroy_bulk', 'as' => 'api.content.types.destroy_bulk']);
Route::delete('content/types/field/{field}', ['uses' => 'Content\ContentTypesController@destroy_field', 'as' => 'api.content.types.destroy_field']);
Route::delete('content/types/fieldset/{fieldset}', ['uses' => 'Content\ContentTypesController@destroy_fieldset', 'as' => 'api.content.types.destroy_fieldset']);
Route::patch('content/types/{type}/fieldset', ['uses' => 'Content\ContentTypesController@fieldset', 'as' => 'api.content.types.fieldset']);

/* MEDIA */
Route::get('content/media', ['uses' => 'Content\MediaController@index', 'as' => 'api.content.media']);
Route::delete('content/media', ['uses' => 'Content\MediaController@destroy_bulk', 'as' => 'api.content.media.destroy_bulk']);
Route::delete('content/media/{media}', ['uses' => 'Content\MediaController@destroy', 'as' => 'api.content.media.destroy']);
Route::get('content/media/{media}', ['uses' => 'Content\MediaController@show', 'as' => 'api.content.media.show']);
Route::post('content/media', ['uses' => 'Content\MediaController@store', 'as' => 'api.content.media.store']);
Route::patch('content/media/{media}', ['uses' => 'Content\MediaController@update', 'as' => 'api.content.media.update']);

/* PERMISSIONS */
Route::patch('users/permissions', ['as' => 'api.users.permissions.update', 'uses' => 'Users\RolesController@permissions_update']);

/* ROLES */
Route::get('users/roles', ['uses' => 'Users\RolesController@index', 'as' => 'api.users.roles']);
Route::post('users/roles', ['uses' => 'Users\RolesController@store', 'as' => 'api.users.roles.store']);
Route::get('users/roles/{role}/users', ['uses' => 'Users\RolesController@users','as' => 'api.users.roles.users']);
Route::patch('users/roles/{role}', ['uses' => 'Users\RolesController@update','as' => 'api.users.roles.update']);
Route::delete('users/roles/{role}', ['uses' => 'Users\RolesController@destroy','as' => 'api.users.roles.destroy']);

/* SETTINGS */
Route::get('settings/access-tokens', ['uses' => 'Settings\AccessTokensController@index', 'as' => 'api.settings.access_tokens']);
Route::delete('settings/access-tokens/{token}', ['uses' => 'Settings\AccessTokensController@destroy', 'as' => 'api.settings.access_tokens.destroy']);
Route::post('settings/access-tokens', ['uses' => 'Settings\AccessTokensController@store', 'as' => 'api.settings.access_tokens.store', ]);

Route::get('settings/image-styles', ['uses' => 'Settings\ImageStylesController@index', 'as' => 'api.settings.image_styles']);
Route::delete('settings/image-styles/{style}', ['uses' => 'Settings\ImageStylesController@destroy', 'as' => 'api.settings.image_styles.destroy']);
Route::delete('settings/image-styles/destroy_styles/{style}', ['uses' => 'Settings\ImageStylesController@destroy_styles', 'as' => 'api.settings.image_styles.destroy_styles']);
Route::get('settings/image-styles/{style}', ['uses' => 'Settings\ImageStylesController@show', 'as' => 'api.settings.image_styles.show']);
Route::post('settings/image-styles', ['uses' => 'Settings\ImageStylesController@store', 'as' => 'api.settings.image_styles.store']);
Route::patch('settings/image-styles/{style}', ['uses' => 'Settings\ImageStylesController@update', 'as' => 'api.settings.image_styles.update']);

Route::get('settings/languages', ['uses' => 'Settings\LanguagesController@index', 'as' => 'api.settings.languages']);
Route::get('settings/languages/all', ['uses' => 'Settings\LanguagesController@all', 'as' => 'api.settings.languages.all']);
Route::get('settings/languages/{language}/activate', ['uses' => 'Settings\LanguagesController@activate', 'as' => 'api.settings.languages.activate']);
Route::get('settings/languages/{language}/deactivate', ['uses' => 'Settings\LanguagesController@deactivate', 'as' => 'api.settings.languages.deactivate']);
Route::post('settings/languages', ['uses' => 'Settings\LanguagesController@store', 'as' => 'api.settings.languages.store']);

/* USERS */
Route::get('users', ['uses' => 'Users\UsersController@index', 'as' => 'api.users']);