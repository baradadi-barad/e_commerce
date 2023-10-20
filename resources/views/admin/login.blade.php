<!DOCTYPE html>
<html lang="en">

<head>
    <title>SMS | Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="{{ asset('public/uploads/favicon.png') }}" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/package/bootstrap/css/bootstrap.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('public/package/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/package/animate/animate.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/package/css-hamburgers/hamburgers.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/package/select2/select2.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" href="{{ asset('public/css/util.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/main.css') }}">
    <!-- Datatable -->
    <!--===============================================================================================-->
</head>

<body class="rightclickdisabled">

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="{{ asset('public/images/Barcode-amico.png') }}" alt="IMG">
                </div>


                <form class="login100-form validate-form" action="{{ route('login.attempt') }}" method="post">

                    <span class="login100-form-title">
                        SMS Login
                    </span>
                    @csrf

                    @if ($error = $errors->first('emailpassword'))
                      <div class="alert alert-danger">
                        {{ $error }}
                      </div>
                    @endif

                    <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                        {{-- <input class="input100" type="text" name="email" placeholder="Email"> --}}
                        <input type="text" id="email" class=" input100" name="email"
                            value="{{ old('email') }}" required autofocus placeholder="Email">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <div class="wrap-input100 validate-input" data-validate="Password is required">

                        <input class="input100" type="password" id="password" name="password" required
                            placeholder="Password" autocomplete="current-password">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn btn-info">
                            Login
                        </button>
                    </div>


                </form>
            </div>
        </div>
    </div>




    <!--===============================================================================================-->
    <script src="{{ asset('public/package/jquery/jquery-3.2.1.min.js') }} "></script>
    <!--===============================================================================================-->
    <script src="{{ asset('public/package/bootstrap/js/popper.js') }}"></script>
    <script src="{{ asset('public/package/bootstrap/js/bootstrap.min.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('public/package/select2/select2.min.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('public/package/tilt/tilt.jquery.min.js') }}"></script>
    
    <!--===============================================================================================-->
    <script src="{{ asset('public/js/main.js') }}"></script>
<script>
        $('.js-tilt').tilt({
            scale: 1.1
        })
        $(document).on('keydown', function(e) {
            console.log(e);
            if ((e.ctrlKey || e.metaKey) || (e.key == "p" || e.charCode == 16 || e.charCode == 112 || e.keyCode == 80 || e.keyCode == 123 || e.key == "F12")) {
                e.cancelBubble = true;
                e.preventDefault();
    
                e.stopImmediatePropagation();
            }
        });
        $(document).ready(function() {
            $(".rightclickdisabled").on("contextmenu", function(e) {
                return false;
            });
        });
    </script>
</body>

</html>
