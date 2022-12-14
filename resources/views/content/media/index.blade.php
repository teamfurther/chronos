@extends('chronos::content')

@section('content')
    <header class="subheader">
        <h1>{!! trans('chronos::interface.Media') !!}</h1>
        <ul class="breadcrumbs">
            <li><span class="icon c4icon-pencil-3"></span></li>
            <li class="active">{!! trans('chronos::interface.Media') !!}</li>
        </ul>
        @can ('upload_media')
        <div class="main-action create">
            <ajax-upload action="{{ route('api.content.media.store') }}" v-bind:multiple="true">
                <a data-placement="left" data-tooltip="tooltip" title="{!! trans('chronos::interface.Upload media') !!}">{!! trans('chronos::interface.Upload media') !!}</a>
            </ajax-upload>
        </div>
        @endcan
    </header><!--/.subheader -->

    <media-table></media-table>
@endsection