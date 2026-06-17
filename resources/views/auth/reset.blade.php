@extends('chronos::guard')

@section('content')
    <form method="POST" action="{{ route('chronos.auth.password_reset_form_post') }}">
        @csrf
        <input name="token" type="hidden" value="{{ $token }}" />

        <div class="form-group @if ($errors->has('email')) has-error @endif" id="email">
            <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="{{ trans('chronos::forms.Email') }}" autocomplete="off" />
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Incorrect email') !!}</strong>
                    {{ $errors->first('email') }}
                </span>
            @endif
        </div>

        <div class="form-group @if ($errors->has('password')) has-error @endif" id="password">
            <input class="form-control" name="password" type="password" placeholder="{{ trans('chronos::forms.New password') }}" autocomplete="off" />
            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Password error') !!}</strong>
                    {{ $errors->first('password') }}
                </span>
            @endif
        </div>

        <div class="form-group @if ($errors->has('password_confirmation')) has-error @endif" id="password-confirmation">
            <input class="form-control" name="password_confirmation" type="password" placeholder="{{ trans('chronos::forms.Confirm new password') }}" autocomplete="off" />
            @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Password error') !!}</strong>
                    {{ $errors->first('password_confirmation') }}
                </span>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">{!! trans('chronos::forms.Change password') !!}</button>
    </form>
@endsection
