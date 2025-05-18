<?php 
if(app()->environment('local')){
    $tPath = '';
}else{
    $tPath = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicons -->
    <link href="{{ asset($tPath.'assets3/img/logo.png') }}" rel="icon">
    <link href="{{ asset($tPath.'assets3/img/logo.png') }}" rel="apple-touch-icon">
    <title>Login | TATA</title>
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}">
    <link rel="stylesheet" href="{{ asset($tPath.'assets3/css/popup.css') }}">
    <link rel="stylesheet" href="{{ asset($tPath.'assets3/css/preloader.css') }}" />
    <link href="{{ asset($tPath.'assets3/css/page/login.css') }}" rel="stylesheet">
    <style>
        html{
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Poppins', sans-serif;
            user-select: none;
            background-color: #CCCCCC;
        }
        body img{
            pointer-events: none;
        }
    </style>
</head>
<body>
    @if(app()->environment('local'))
    <script>
    var tPath = '';
    </script>
    @else
    <script>
    var tPath = '';
    </script>
    @endif
    <script>
    var csrfToken = "{{ csrf_token() }}";
    @if(isset($logout))
    var logoutt = "{{$logout}}";
    showPopup(logoutt);
    @endif
    </script>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <divc class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <main class="card-body">
                                <div class="relative">
                                    <svg width="616" height="840" viewBox="0 0 616 840" fill="none" xmlns="http://www.w3.org/2000/svg" class="relative z-0 left-0 top-0">
                                        <path d="M579 187.5C598.2 137.5 507.5 41.5 461 0C376.667 0 94.9002 0.500018 48.5002 0.500008C13.5002 0.5 -0.065952 10.5 0.000240942 40C0.500241 262.833 0.500275 731.187 0.500275 786C0.500275 818 3.50027 839.5 39.0003 839.5C160.334 839.5 514.7 839 541.5 839C575 839 590 688.5 579 634C568 579.5 579 568.5 608 513.5C637 458.5 579 363 556.5 335.5C534 308 555 250 579 187.5Z" fill="url(#paint0_linear_232_2910)"/>
                                        <defs>
                                        <linearGradient id="paint0_linear_232_2910" x1="465.5" y1="14.4999" x2="31.0004" y2="820.5" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#53C879"/>
                                        <stop offset="1" stop-color="#239849"/>
                                        </linearGradient>
                                        </defs>
                                    </svg>
                                    <div class="absolute z-10">
                                        <img src="" alt="">
                                        <p>Solusi Cerdas Design Cepat</p>
                                    </div>
                                    <div class="absolute w-1/2 -translate-x-1/2 z-10">
                                        <h2>Selamat Datang!</h2>
                                        <p>Masukkan User anda dan Password untuk akses</p>
                                    </div>
                                </div>
                                <div>
                                    <div>
                                        <h3>Login Admin</h3>
                                    </div>
                                    <form action="" id="loginForm">
                                        <div class="col-12">
                                            <label for="inpEmail">Email</label>
                                            <input type="text" id="inpEmail" class="rounded-2xl">
                                        </div>
                                        <div class="relative">
                                            <input id="inpPassword" type="password" class="form-control" id="exampleInputPassword1" style="padding-right: 45px;" oninput="showEyePass()">
                                            <div id="iconPass" onclick="showPass()" style="display: none;">
                                                <img src="{{ asset($tPath.'assets3/icon/eye-slash.svg') }}" alt="" id="passClose">
                                                <img src="{{ asset($tPath.'assets3/icon/eye.svg') }}" alt="" id="passShow" style="display: none">
                                            </div>
                                        </div>
                                        <input type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2" value="Masuk">
                                    </form>
                                </div>
                            </main>
                        </div>
                    </divc>
                </div>
            </div>
        </div>
    </div>
    @include('components.preloader')
    <div id="greenPopup" style="display:none"></div>
    <div id="redPopup" style="display:none"></div>
    <script src="{{ asset($tPath.'assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets3/js/page/login.js') }}"></script>
</body>
</html>