<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Metode Pembayaran | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/preloader.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/page/tambahMetodePembayaran.css') }}" />
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
    const domain = window.location.protocol + '//' + window.location.hostname + ":" + window.location.port;
    const reff = '/admin';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'admin';
        @endphp
        @include('components.admin.sidebar')
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper" style="background-color: #efefef;">
            <!--  Header Start -->
            @include('components.admin.header')
            <!--  Header End -->
            <div class="container-fluid" style="background-color: #F6F9FF">
                <div class="pagetitle mt-2 mt-sm-3 mt-md-3 mt-lg-4 mb-2 mb-sm-3 mb-md-3 mb-lg-4">
                    <h1>Tambah Metode Pembayaran</h1>
                </div>
                <div class="d-flex align-items-stretch" style="background-color: #ffffff; border-radius: 20px;">
                    <form id="tambahForm">
                        <div class="crow">
                            <label for="">Nama Rekening</label>
                            <input type="text" id="inpNama">
                        </div>
                        <div class="crow">
                            <label for="">No Rekening</label>
                            <input type="text" id="inpNoRekening">
                        </div>
                        <div class="crow">
                            <label for="">Deskripsi 1</label>
                            <input type="text" id="inpDeskripsi1">
                        </div>
                        <div class="crow">
                            <label for="">Deskripsi 2</label>
                            <input type="text" id="inpDeskripsi2">
                        </div>
                        {{-- <div class="img" onclick="handleFileClick()" ondragover="handleDragOver(event)"
                            ondrop="handleDrop(event)">
                            <img src="{{ asset($tPath.'assets2/icon/upload.svg') }}" alt="" id="icon">
                            <span>Pilih File atau Jatuhkan File</span>
                            <input type="file" id="inpFoto" hidden onchange="handleFileChange(event)">
                            <img src="" alt="" id="file" style="display:none">
                        </div> --}}
                        <div class="crow">
                            <a href="/admin" class="btn btn-danger">Kembali</a>
                            <button type="submit" class="btn btn-success">
                                <img src="{{ asset($tPath.'assets2/icon/tambah.svg') }}" alt="" width="30" height="30">
                                <span>Tambah</span>
                            </button>
                        </div>
                    </form>
                </div>
                @include('components.admin.footer')
            </div>
        </div>
    </div>
    @include('components.preloader')
    <div id="greenPopup" style="display:none"></div>
    <div id="redPopup" style="display:none"></div>
    <script src="{{ asset($tPath.'assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset($tPath.'assets/js/app.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset($tPath.'assets/libs/simplebar/dist/simplebar.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/page/tambahMetodePembayaran.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>
</html>