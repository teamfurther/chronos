@extends('chronos::guard')

@section('content')
    @if (!Session::has('msg_success'))
        {!! Form::open(['route' => 'chronos.auth.password_reset_request_post', 'method' => 'POST']) !!}
            <div class="form-group @if ($errors->has('email')) has-error @endif" id="email">
                {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => trans('Chronosforms.Email'), 'autocomplete' => 'off']) !!}
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{!! trans('chronos::alerts.Incorrect email') !!}</strong>
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">{!! trans('chronos::forms.Recover') !!}</button>
        {!! Form::close() !!}
    @else
        <div class="notification">
            <strong>{!! trans('chronos::alerts.Password recovery successful.') !!}</strong>
            <p>{!! trans('chronos::alerts.We\'ve sent you an email with a reset link, use that to set a new password for your account.') !!}</p>
            <a href="{{ route('chronos.auth.login') }}">{!! trans('chronos::interface.Click here to sign in') !!}</a>
        </div>
    @endif
@endsection