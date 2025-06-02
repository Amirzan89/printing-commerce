<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Metode Pembayaran | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/preloader.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/page/editAdmin.css') }}" />
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
    const reff = '/metode-pembayaran';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    var users = {!! json_encode($metodePembayaranData) !!};
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'metode-pembayaran';
        @endphp
        @include('components.admin.sidebar')
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper" style="background-color: #efefef;">
            <!--  Header Start -->
            @include('components.admin.header')
            <!--  Header End -->
            <div class="container-fluid">
                <div class="pagetitle mt-2 mt-sm-3 mt-md-3 mt-lg-4 mb-2 mb-sm-3 mb-md-3 mb-lg-4">
                    <h1>Edit Metode Pembayaran</h1>
                </div>
                <div class="d-flex align-items-stretch" style="background-color: #ffffff; border-radius: 20px;">
                    <form id="editForm">
                        <div class="crow">
                            <label for="">Nama Metode Pembayaran</label>
                            <input type="text" id="inpNama" value="{{ $metodePembayaranData['nama_metode_pembayaran']}}">
                        </div>
                        <div class="crow">
                            <label for="">No Metode Pembayaran</label>
                            <input type="text" id="inpNoMetodePembayaran" value="{{ $metodePembayaranData['no_metode_pembayaran']}}">
                        <div class="crow">
                            <label>Deskripsi 1</label>
                            <textarea name="deskripsi_1" id="inpDeskripsi" placeholder="Masukkan Deskripsi 1" class="" style="height:120px">{{ $metodePembayaranData['deskripsi_1'] ?? '' }}</textarea>
                        </div>
                        <div class="crow">
                            <label>Deskripsi 2</label>
                            <textarea name="deskripsi_2" id="inpDeskripsi" placeholder="Masukkan Deskripsi 2" class="" style="height:120px">{{ $metodePembayaranData['deskripsi_2'] ?? '' }}</textarea>
                        </div>
                        <div class="crow">
                            <label>Thumbnail</label>
                            <img src="{{ asset($tPath.'assets3/img/metode-pembayaran/'.$metodePembayaranData['thumbnail']) }}" alt="" id="file" class="thumbnail" onerror="imgError('file')">
                        </div>
                        <div class="crow">
                            <label>Icon</label>
                            <img src="{{ asset($tPath.'assets3/img/metode-pembayaran/'.$metodePembayaranData['icon']) }}" alt="" id="file" class="icon" onerror="imgError('file')">
                        </div>
                        <div class="crow">
                            <a href="/metode-pembayaran" class="btn btn-danger">Kembali</a>
                            <button type="submit" class="btn btn-success">
                                <img src="{{ asset($tPath.'img/icon/edit.svg') }}" alt="">
                                <span>Edit</span>
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
    <script src="{{ asset($tPath.'assets2/js/page/editAdmin.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>

</html>