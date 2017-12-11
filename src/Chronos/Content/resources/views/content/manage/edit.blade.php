@extends('chronos::content')

@section('content')
    <header class="subheader">
        <h1>{{ $content->title }}</h1>
        <ul class="breadcrumbs">
            <li><span class="icon c4icon-pencil-3"></span></li>
            <li><a href="{{ route('chronos.content', ['type' => $type->id]) }}">{{ $type->name }}</a></li>
            <li class="active">{{ $content->title }}</li>
        </ul>
    </header><!--/.subheader -->
    <div class="container">
        <content-editor v-bind:content-id="{{ $content->id }}" v-bind:type-id="{{ $type->id }}" inline-template>
            {!! Form::open(['route' => ['api.content.update', $type, $content], 'method' => 'PATCH', 'v-on:submit.prevent' => 'saveContent($event, 1)', 'novalidate' => 'novalidate']) !!}
            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div id="content-editor-wrapper">
                        <p class="text-center" v-show="dataLoader"><span class="loader-small"></span></p>

                        <div class="panel" v-if="!dataLoader">
                            <h2 class="panel-title">{!! trans('chronos.content::forms.:type info', ['type' => $type->name]) !!}</h2>
                            <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'title') }">
                                <label class="control-label" for="title">{{ $type->title_label }}</label>
                                <input class="form-control input-lg" id="title" name="title" placeholder="{!! trans('chronos.content::interface.Start typing&hellip;') !!}" type="text" v-model="title" v-on:blur="updateSlug" />
                                <span class="help-block" v-html="store.formErrors['title'][0]" v-if="Object.hasKey(store.formErrors, 'title')"></span>
                            </div>
                            @if ($type->name == 'Gallery')
                                <div class="form-group">
                                    ID: <span v-html="contentId"></span> <code>[gallery id="<span v-html="contentId"></span>"]</code>
                                </div>
                            @endif
                            <div class="slug form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'slug') }" v-show="(slug != '' && !slugChanged) || slugChanged || Object.hasKey(store.formErrors, 'slug')">
                                <label class="control-label" for="slug">{!! trans('chronos.content::forms.Slug') !!}</label>
                                <div v-if="!slugChanged && !Object.hasKey(store.formErrors, 'slug')"><strong v-html="'/' + slug"></strong><a v-on:click="changeSlug('slug')">change</a></div>
                                <input class="form-control" id="slug" name="slug" v-on:keyup="changeSlug('slug')" v-show="slugChanged || Object.hasKey(store.formErrors, 'slug')" type="text" v-model="slug" />
                                <span class="help-block" v-html="store.formErrors['slug'][0]" v-if="Object.hasKey(store.formErrors, 'slug')"></span>
                            </div>
                            <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'parent_id') }" v-show="typeHierarchyFlattened && typeHierarchyFlattened.length > 0">
                                <label class="control-label" for="parent_id">{!! trans('chronos.content::forms.Parent :type', ['type' => $type->name]) !!}</label>
                                <select class="form-control" id="parent_id" name="parent_id" v-model="parentId">
                                    <option value="0">{!! trans('chronos.content::forms.(No parent)') !!}</option>
                                    <option v-for="item in typeHierarchyFlattened" v-bind:value="item.id" v-html="Array(item.depth * 3 + 1).join('&#160;') + item.title"></option>
                                </select>
                                <span class="help-block" v-html="store.formErrors['parent_id'][0]" v-if="Object.hasKey(store.formErrors, 'parent_id')"></span>
                            </div>
                            <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'order') }">
                                <label class="control-label" for="status">{!! trans('chronos.content::forms.Order') !!}</label>
                                <input class="form-control" id="order" name="order" type="number" min="0" step="1" v-model="order" />
                                <span class="help-block" v-html="store.formErrors['order'][0]" v-if="Object.hasKey(store.formErrors, 'order')"></span>
                            </div>
                            <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'status') }">
                                <label class="control-label" for="status">{!! trans('chronos.content::forms.:type status', ['type' => $type->name]) !!}</label>
                                <select class="form-control" id="status" name="status" v-on:change="setScheduledStatus(0)" v-model="status">
                                    <option value="1">{!! trans('chronos.content::forms.Active') !!}</option>
                                    <option value="0">{!! trans('chronos.content::forms.Inactive') !!}</option>
                                </select>
                                <span class="help-block" v-html="store.formErrors['status'][0]" v-if="Object.hasKey(store.formErrors, 'status')"></span>
                            </div>
                            @can ('lock_content_type_delete_' . $type->id)
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input id="lock_delete" name="lock_delete" type="checkbox" v-model="lockDelete" />
                                        {!! trans('chronos.content::forms.Lock delete?') !!}
                                    </label>
                                </div>
                            </div>
                            @endcan
                        </div>

                        <div class="fieldset-list" v-if="!dataLoader">
                            <set v-for="fieldset in fieldsets" v-bind:fieldset-data="fieldset"></set>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="content-sidebar" data-spy="affix" data-offset-top="100" v-if="!dataLoader">
                        @if (settings('is_multilanguage') && $type->translatable)
                            <div class="panel">
                                <h2 class="panel-title">{{ trans('chronos.content::interface.Languages') }}</h2>
                                <p class="paddingB15"><strong>{!! trans('chronos.content::interface.The language of this :type is <em>:language</em>.', ['type' => strtolower($type->name), 'language' => $content->languageName]) !!}</strong></p>
                                <table class="table table-condensed">
                                    @foreach($languages as $language)
                                        @if ($content->language != $language->code)
                                            <tr>
                                                <td>{{ $language->name }}</td>
                                                <td class="text-right">
                                                    @if ($content->translation_codes->search($language->code) !== false)
                                                        <a class="icon c4icon-pencil-3 c4icon-lg" href="{{ $content->admin_urls['translations'][$language->code] }}"></a>
                                                    @else
                                                        <a class="icon c4icon-plus-2 c4icon-lg" href="{{ $content->endpoints['translate'] }}?language={{ $language->code }}"></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </div>
                        @endif
                        @if (settings('is_content_schedulable'))
                            <div class="panel">
                                <h2 class="panel-title">{{ trans('chronos.content::interface.Schedule activation') }}</h2>
                                <p>{!! trans('chronos.content::interface.Activate this content on:') !!}</p>
                                <input class="form-control" name="status_scheduled" v-flatpickr="{ defaultDate: statusScheduled, enableTime: true, minDate: new Date(), onValueUpdate: setScheduledStatus(1), time_24hr: true }" v-model="statusScheduled" />
                                <input name="status_scheduled_timezone_offset" type="hidden" v-bind:value="new Date().getTimezoneOffset()" />
                            </div>
                        @endif
                        <div class="panel panel-actions">
                            <input name="author_id" type="hidden" value="{{ Auth::user()->id }}" />
                            <button class="btn btn-primary" name="process" type="submit" value="1">{!! trans('chronos.content::forms.Save') !!}</button>
                            <a class="btn btn-cancel" href="{{ route('chronos.content', ['type' => $type]) }}">{!! trans('chronos.content::forms.Cancel') !!}</a>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </content-editor>
    </div>
@endsection



@push('scripts-components')
@include('chronos::components.content_editor')
@endpush