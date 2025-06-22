<!DOCTYPE html>
<html class="h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/images/favicon.png') }}">
    <!-- Custom Stylesheet -->
    <link href="{{ asset('theme/css/style.css') }}" rel="stylesheet">
</head>

<body class="h-100">
    <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
            </svg>
        </div>
    </div>
    
    <div class="login-form-bg h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100">
                <div class="col-xl-6">
                    <div class="form-input-content">
                        <div class="card login-form mb-0">
                            <div class="card-body pt-5">
                                <a class="text-center" href="/"> 
                                    <h4>AIRA Admin Login</h4>
                                </a>

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        {{ session('error') }}
                                    </div>
                                @endif
        
                                <form class="mt-5 mb-5 login-input" method="POST" action="{{ route('admin.login.submit') }}">
                                    @csrf
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                               placeholder="Email" required value="{{ old('email') }}" autofocus>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                               placeholder="Password" required>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="remember" 
                                                   id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="remember">Remember me</label>
                                        </div>
                                    </div>
                                    <button class="btn login-form__btn submit w-100">Sign In</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--**********************************
        Scripts
    ***********************************-->
    <script src="{{ asset('theme/plugins/common/common.min.js') }}"></script>
    <script src="{{ asset('theme/js/custom.min.js') }}"></script>
    <script src="{{ asset('theme/js/settings.js') }}"></script>
    <script src="{{ asset('theme/js/gleek.js') }}"></script>
    <script src="{{ asset('theme/js/styleSwitcher.js') }}"></script>
</body>
</html>
