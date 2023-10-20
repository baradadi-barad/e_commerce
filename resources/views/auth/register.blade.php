{{-- @extends('layouts.app') --}}
@extends('layouts.master')

@section('content')
<div class="content">
    <div class="container">
        <div class="row justify-content-center">
                    <div class="register_page">
                        <div id="postadd_div">
                            <div class="homeTitleBar">
                                <h1>Register</h1>
                            </div>
                                <div class="form-group">
                                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                                        @csrf

                                        <div class="line">
                                            <label for="first_name" class="col-md-4 col-form-label text-md-end">First Name</label>

                                            <div class="">
                                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" required autocomplete="first_name" autofocus>

                                                @error('first_name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="line">
                                            <label for="last_name" class="col-md-4 col-form-label text-md-end">Last Name</label>

                                            <div class="">
                                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name" autofocus>

                                                @error('last_name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="line">
                                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                                            <div class="">
                                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="line">
                                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                                            <div class="">
                                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="line">
                                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                                            <div class="">
                                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                            </div>
                                        </div>

                                        <div class="line">
                                            <label for="gender" class="col-md-4 col-form-label text-md-end">Gender</label>

                                            <div class=" mt-2">
                                                <input type="radio" name="gender" value="M"> <label for="male">Male</label>
                                                <input type="radio" name="gender" value="F"> <label for="female">Female</label>
                                                <input type="radio" name="gender" value="O"> <label for="other">Rather no say</label>
                                            </div>
                                        </div>

                                        <div class="line">
                                            <div class="col-md-6 offset-md-4">
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('Register') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@endsection
