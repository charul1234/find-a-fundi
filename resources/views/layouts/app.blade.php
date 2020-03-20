<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ config('app.name', 'Laravel') }}</title>
    
    @php $favicon = getSetting('favicon'); @endphp
    @if(isset($favicon) && $favicon!=''  && \Storage::exists(config('constants.SETTING_IMAGE_URL').$favicon))
    <link rel="shortcut icon" href="{{ \Storage::url(config('constants.SETTING_IMAGE_URL').$favicon) }}">
    @endif

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('styles')

    <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 93vh;
                margin: 0;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }
        </style>
</head>
<body>    
    @if (Route::has('login'))
        <div class="top-right links">
                <a href="{{ route('home') }}">Home</a>
            @auth
                <a href="{{ route('profile.index') }}">Profile</a>
                <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                    Logout
                </a>
            @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </div>
    @endif
    <div class="mt-5">
        <!-- Begin Page Content -->
        <div class="container">
            @if($flash = (session('error') ?: session('danger')))            
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                {{ $flash }}
            </div>
            @endif
            @if($flash = session('success'))      
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                {{ $flash }}
            </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{asset('js/jquery/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-validation/dist/additional-methods.min.js') }}"></script>
    <script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formValidationOptions = {
        errorElement: 'strong', //default input error message container
        errorClass: 'help-block', // default input error message class
        focusInvalid: true, // do not focus the last invalid input
        ignore: "",
        errorPlacement: function (error, element) { // render error placement for each input type
            if (element.attr("data-error-container")) { 
                error.appendTo(element.attr("data-error-container"));
            }else{
                error.insertAfter(element); // for other inputs, just perform default behavior
            }
        },
        highlight: function (element) { // hightlight error inputs
            jQuery(element)
                .closest('.form-group')
                    .css({'color':'red'})
                    .addClass('{{ config("constants.ERROR_FORM_GROUP_CLASS") }}')
                    .removeClass('has-success'); // set error class to the control group
        },
        unhighlight: function (element) { // revert the change done by hightlight
            jQuery(element)
                .closest('.form-group')
                    .css({'color':''})
                    .removeClass('{{ config("constants.ERROR_FORM_GROUP_CLASS") }}'); // set error class to the control group
        },
        success: function (label) {
            label
            .closest('.form-group').removeClass('{{ config("constants.ERROR_FORM_GROUP_CLASS") }}'); // set success class to the control group
        }
    };

    jQuery('.autoFillOff').attr('readonly', true);
    setTimeout(function(){
        jQuery('.autoFillOff').attr('readonly', false)
    }, 1000);
    jQuery(document).ready(function(){
        if(jQuery.validator)
            jQuery.validator.setDefaults(formValidationOptions);
    });
    </script>
    @yield('scripts')
</body>
</html>
