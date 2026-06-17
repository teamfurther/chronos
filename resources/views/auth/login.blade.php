@extends('chronos::guard')

@section('content')
    <form method="POST" action="{{ route('chronos.auth.login_post') }}">
        @csrf
        <div class="form-group @if ($errors->has('email')) has-error @endif" id="email">
            <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="{{ trans('chronos::forms.Email') }}" />
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Incorrect email or password') !!}</strong>
                    {{ $errors->first('email') }}
                </span>
            @endif
        </div>

        <div class="form-group @if ($errors->has('password')) has-error @endif" id="password">
            <input class="form-control" name="password" type="password" placeholder="{{ trans('chronos::forms.Password') }}" />
            <a id="reset-password" href="{{ route('chronos.auth.password_reset_request') }}" title="{!! trans('chronos::forms.Recover password') !!}">?</a>
            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Incorrect password') !!}</strong>
                    {{ $errors->first('password') }}
                </span>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">{!! trans('Log in') !!}</button>
    </form>
@endsection
