@extends('chronos::guard')

@section('content')
    {!! Form::open(['route' => 'chronos.auth.password_reset_form_post', 'method' => 'POST']) !!}
        {!! Form::hidden('token', $token) !!}

        <div class="form-group @if ($errors->has('email')) has-error @endif" id="email">
            {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => trans('chronos::forms.Email'), 'autocomplete' => 'off']) !!}
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Incorrect email') !!}</strong>
                    {{ $errors->first('email') }}
                </span>
            @endif
        </div>

        <div class="form-group @if ($errors->has('password')) has-error @endif" id="password">
            {!! Form::password('password', ['class' => 'form-control', 'placeholder' => trans('chronos::forms.New password'), 'autocomplete' => 'off']) !!}
            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Password error') !!}</strong>
                    {{ $errors->first('password') }}
                </span>
            @endif
        </div>

        <div class="form-group @if ($errors->has('password_confirmation')) has-error @endif" id="password-confirmation">
            {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => trans('chronos::forms.Confirm new password'), 'autocomplete' => 'off']) !!}
            @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong>{!! trans('chronos::alerts.Password error') !!}</strong>
                    {{ $errors->first('password_confirmation') }}
                </span>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">{!! trans('chronos::forms.Change password') !!}</button>
    {!! Form::close() !!}
@endsection