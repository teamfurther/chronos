@extends('chronos::admin')

@section('content')
    <header class="subheader">
        <h1>{{ $style->name }}</h1>
        <ul class="breadcrumbs">
            <li><span class="icon c4icon-pencil-3"></span></li>
            <li><a href="{{ route('chronos.settings.image_styles') }}">{!! trans('chronos::interface.Image styles') !!}</a></li>
            <li class="active">{!! trans('chronos::interface.Create image style') !!}</li>
        </ul>
    </header><!--/.subheader -->
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="panel">
                    <h2 class="panel-title">{!! trans('chronos::interface.Image style info') !!}</h2>
                    <image-style-editor v-bind:style-id="{{ $style->id }}" inline-template>
                        {!! Form::open(['route' => ['api.settings.image_styles.update', $style->id], 'method' => 'PATCH', 'v-on:submit.prevent' => 'saveForm', 'novalidate' => 'novalidate']) !!}
                        <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'name') }">
                            <label class="control-label" for="name">{!! trans('chronos::forms.Name') !!}</label>
                            <input class="form-control" id="name" name="name" type="text" v-model="name" />
                            <span class="help-block" v-html="store.formErrors['name'][0]" v-if="Object.hasKey(store.formErrors, 'name')"></span>
                            <span class="help-block" v-else><span>{!! trans('chronos::forms.The name of the image style must be unique.') !!}</span></span>
                        </div>
                        <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'width') }">
                            <label class="control-label" for="width">{!! trans('chronos::forms.Width') !!}</label>
                            <input class="form-control" id="width" name="width" type="number" v-model="width" />
                            <span class="help-block" v-html="store.formErrors['width'][0]" v-if="Object.hasKey(store.formErrors, 'width')"></span>
                        </div>
                        <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'height') }">
                            <label class="control-label" for="height">{!! trans('chronos::forms.Height') !!}</label>
                            <input class="form-control" id="height" name="height" type="number" v-model="height" />
                            <span class="help-block" v-html="store.formErrors['height'][0]" v-if="Object.hasKey(store.formErrors, 'height')"></span>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input id="upsizing" name="upsizing" type="checkbox" v-model="upsizing" />
                                    {!! trans('chronos::forms.Allow upsizing?') !!}
                                </label>
                            </div>
                        </div>
                        <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'rotate') }">
                            <label class="control-label" for="rotate">{!! trans('chronos::forms.Rotate') !!}</label>
                            <input class="form-control short-control" id="rotate" name="rotate" type="number" min="0" max="360" v-model="rotate" />
                            <span class="help-block" v-html="store.formErrors['rotate'][0]" v-if="Object.hasKey(store.formErrors, 'rotate')"></span>
                        </div>
                        <div class="form-group" v-bind:class="{ 'has-error': Object.hasKey(store.formErrors, 'crop_width') || Object.hasKey(store.formErrors, 'crop_height') }">
                            <label class="control-label" for="crop_width">{!! trans('chronos::forms.Crop factor') !!}</label>
                            <div>
                                <div class="input-group display-inline-block">
                                    <input class="form-control short-control" id="crop_width" name="crop_width" type="number" placeholder="100" min="1" v-model="crop_width" />
                                    <span class="input-group-addon">px</span>
                                </div>
                                :
                                <div class="input-group display-inline-block">
                                    <input class="form-control short-control" id="crop_height" name="crop_height" type="number" placeholder="100" min="1" v-model="crop_height" />
                                    <span class="input-group-addon">px</span>
                                </div>
                            </div>

                            <span class="help-block" v-html="store.formErrors['crop_width'][0]" v-if="Object.hasKey(store.formErrors, 'crop_width')"></span>
                            <span class="help-block" v-html="store.formErrors['crop_height'][0]" v-if="Object.hasKey(store.formErrors, 'crop_height')"></span>
                        </div>
                        <div class="form-group form-inline">
                            <div class="radio marginR15">
                                <label>
                                    <input name="crop_type" type="radio" value="crop" v-model="crop_type" />&nbsp;
                                    {!! trans('chronos::forms.Crop') !!}
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="crop_type" type="radio" value="fit" v-model="crop_type" />&nbsp;
                                    {!! trans('chronos::forms.Fit') !!}
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">{!! trans('chronos::forms.Anchor') !!}</label>
                            <table class="table image-anchor-table">
                                <tbody>
                                <tr class="odd">
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="left-top" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="center-top" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="right-top" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="even">
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="left-middle" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="center-middle" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="right-middle" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="odd">
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="left-bottom" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="center-bottom" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input name="anchor" type="radio" value="right-bottom" v-model="anchor" />
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input id="greyscale" name="greyscale" type="checkbox" v-model="greyscale" />
                                    {!! trans('chronos::forms.Greyscale?') !!}
                                </label>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <a class="btn btn-cancel" href="{{ route('chronos.settings.image_styles') }}">{!! trans('chronos::forms.Cancel') !!}</a>
                            <button class="btn btn-primary" name="process" type="submit" value="1">{!! trans('chronos::forms.Save') !!}</button>
                        </div>
                        {!! Form::close() !!}
                    </image-style-editor>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts-components')
    @include('chronos::components.image_style_editor')
@endpush