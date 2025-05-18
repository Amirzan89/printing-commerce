<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'css/preloader.css') }}" />
    <!-- CSS for full calender -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"/>
    <style>
    body img{
        pointer-events: none;
    }
    a:hover{
        text-decoration: none;
    }
    #kotak {
        display: flex;
        flex-direction: column;
        margin-bottom:6%;
        gap: 30px;
    }
    #kotak #carousel{
        /* overflow-x: hidden; */
        overflow-x: auto;
        display: flex;
        gap: 10px;
        -ms-overflow-style: none;
        scrollbar-width: none; 
    }
    #kotak #carousel::-webkit-scrollbar {
        display: none;
    }
    .cardC {
        flex-shrink: 0;
        width: 180px;
        height: 120px;
        /* width: 46.2%; */
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-bottom: 0px;
        background-color: #201658;
        border-radius: 20px;
    }
    .cardC h5,
    .cardC div {
        color:white;
        position: relative;
        display: flex;
        font-weight: 600;
    }
    .cardC h5 {
        font-size: 24px;
        width:max-content;
        height:max-content;
        margin-left: auto;
        margin-right: auto;
    }
    .cardC div {
        margin-left: auto;
        margin-right: auto;
        display: flex;
        align-items: center;
        font-size: 30px;
        gap:30px;
    }
    .cardC:nth-child(3) h5{
        font-size: 24px;
    }
    .cardC:nth-child(4) h5{
        font-size: 19px;
    }
    .cardC:nth-child(5) h5{
        font-size: 19px;
    }
    .cardC img{
        width: 60px;
        height: 60px;
    }
    .cardC span{
        font-size: 30px;
    }
    @media screen and (min-width: 700px) and (max-width: 1100px) {
        #kotak {
            height: 53vh;
        }
        .cardC {
            width: 47%;
            height: 100px;
            margin-bottom: 0px;
        }
        /* .cardC h5 {
            font-size: 21px;
            font-weight: 600;
        } */
        .cardC div {
            gap: 4%;
            font-size: 21px;
        }
        .cardC img{
            width: 37px;
            height: 37px;
        }
    }
    @media screen and (min-width: 500px) and (max-width: 700px) {
        #kotak {
            height: 56vh;
        }
        .cardC {
            width: 47%;
            height: 100px;
            margin-bottom: 0px;
        }
        /* .cardC h5 {
            font-size: 19px;
            font-weight: 600;
        } */
        .cardC div {
            gap: 5%;
            font-size: 19px;
        }
        .cardC img{
            width: 35px;
            height: 35px;
        }
    }
    @media screen and (max-width: 500px) {
        #kotak {
            height: 56vh;
        }
        .cardC {
            width: 47%;
            height: 100px;
            margin-bottom: 0px;
        }
        .cardC h5 {
            font-size: 17px;
            font-weight: 600;
        }
        .cardC div {
            gap: 6%;
            font-size: 17px;
        }
        .cardC img{
            width: 33px;
            height: 33px;
        }
    }
    </style>
</head>

<body style="user-select: none;">
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
    const domain = window.location.protocol + '//' + window.location.hostname + ":" + window.location.port;
    var csrfToken = "{{ csrf_token() }}";
    var role = @json($userAuth);
    console.log('rolee', role)
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
        $nav = 'dashboard';
        @endphp
        @include('components.admin.sidebar')
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            @include('components.admin.header')
            <!--  Header End -->
            <div class="container-fluid" style="background-color: #F6F9FF">
                <div class="pagetitle">
                    <h1>Beranda</h1>
                </div>
                <div id="kotak">
                    <div class="card">
                    </div>
                </div>
                @include('components.admin.footer')
            </div>
        </div>
    </div>
    @include('components.preloader')
    <!-- JS for jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- JS for full calender -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
    <!-- bootstrap css and js -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script>
    </script>
    <script src="{{ asset($tPath.'assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset($tPath.'assets/js/app.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/js/dashboard.js') }}"></script>
</body>

</html>