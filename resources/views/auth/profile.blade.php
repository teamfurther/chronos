@extends('chronos::admin')

@section('content')
    <header class="subheader">
        <h1>{!! trans('chronos::interface.My profile') !!}</h1>
        <ul class="breadcrumbs">
            <li><span class="icon c4icon-user-profile"></span></li>
            <li class="active">{!! trans('chronos::interface.My profile') !!}</li>
        </ul>
    </header><!--/.subheader -->

    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-8">
                <div class="panel">
                    <h2 class="panel-title">{!! trans('chronos::interface.Edit profile') !!}</h2>

                    <form method="POST" action="{{ route('chronos.auth.profile_post') }}">
                        @csrf

                    <div class="form-group @if ($errors->has('firstname')) has-error @endif">
                        <label class="control-label" for="firstname">{!! trans('chronos::forms.Firstname') !!}</label>
                        <input class="form-control" id="firstname" name="firstname" type="text" value="{{ old('firstname', Auth::user()->firstname) }}" />
                        @if ($errors->has('firstname'))
                            <span class="help-block">{{ $errors->first('firstname') }}</span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('lastname')) has-error @endif">
                        <label class="control-label" for="lastname">{!! trans('chronos::forms.Lastname') !!}</label>
                        <input class="form-control" id="lastname" name="lastname" type="text" value="{{ old('lastname', Auth::user()->lastname) }}" />
                        @if ($errors->has('lastname'))
                            <span class="help-block">{{ $errors->first('lastname') }}</span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('email')) has-error @endif">
                        <label class="control-label" for="email">{!! trans('chronos::forms.Email address') !!}</label>
                        <input class="form-control" id="email" name="email" type="text" value="{{ old('email', Auth::user()->email) }}" />
                        @if ($errors->has('email'))
                            <span class="help-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary">{!! trans('chronos::forms.Save') !!}</button>
                    </div>

                    </form>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="panel">
                    <h2 class="panel-title">{!! trans('chronos::interface.Change picture') !!}</h2>

                    <ajax-upload action="{{ route('chronos.auth.update_picture') }}" crop="1:1">
                        <div class="profile-picture edit">
                            @if (Auth::user()->picture != '')
                                <img src="{{ asset('uploads/user-pictures/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}" />
                            @else
                                <img src="{{ asset('chronos/img/no-profile-picture.png') }}" alt="{{ Auth::user()->name }}" />
                            @endif
                        </div>
                    </ajax-upload>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-8">
                <div class="panel">
                    <h2 class="panel-title">{!! trans('chronos::interface.Change password') !!}</h2>
                    <form method="POST" action="{{ route('chronos.auth.update_password') }}">
                        @csrf

                    <div class="form-group @if ($errors->has('current_password')) has-error @endif">
                        <label class="control-label" for="current_password">{!! trans('chronos::forms.Current password') !!}</label>
                        <input class="form-control" id="current_password" name="current_password" type="password" />
                        @if ($errors->has('current_password'))
                            <span class="help-block">{{ $errors->first('current_password') }}</span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('password')) has-error @endif">
                        <label class="control-label" for="password">{!! trans('chronos::forms.New password') !!}</label>
                        <input class="form-control" id="password" name="password" type="password" />
                        @if ($errors->has('password'))
                            <span class="help-block">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('password_confirmation')) has-error @endif">
                        <label class="control-label" for="password_confirmation">{!! trans('chronos::forms.Confirm new password') !!}</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" />
                        @if ($errors->has('password_confirmation'))
                            <span class="help-block">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>

                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary">{!! trans('chronos::forms.Update') !!}</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('content-modals')
    <crop-dialog></crop-dialog>
@endpush



@push('scripts-components')
    @include('chronos::components.crop_dialog')
@endpush



@push('styles')
<link href="{{ asset('chronos/css/cropper.css') }}" rel="stylesheet" />
@endpush

@push('scripts-post')
<script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script>
@endpush
