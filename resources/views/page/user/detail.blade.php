<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User | TATA</title>
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
    const reff = '/editor';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    var users = {!! json_encode($userData) !!};
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'editor';
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
                    <h1>Edit Editor</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="/editor">Kelola Editor</a></li>
                            <li class="breadcrumb-item">Edit Editor</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-stretch" style="background-color: #ffffff; border-radius: 20px;">
                    <form id="editForm">
                        <div class="crow">
                            <label for="">Nama Lengkap</label>
                            <input type="text" id="inpNama" value="{{ $userData['nama_user']}}">
                        </div>
                        <div class="crow">
                            <div style="width: 20%">
                                <label for="">Jenis Kelamin</label>
                                <select class="" aria-label="Default select example" id="inpJenisKelamin">
                                    <option value="laki-laki"
                                        {{ ($userData['jenis_kelamin'] == 'laki-laki') ? 'selected' : ''}}>Laki-Laki
                                    </option>
                                    <option value="perempuan"
                                        {{ ($userData['jenis_kelamin'] == 'perempuan') ? 'selected' : ''}}>Perempuan
                                    </option>
                                </select>
                            </div>
                            <div style="flex: 1">
                                <label for="">Nomer Telepon</label>
                                <input type="text" id="inpNomerTelepon" value="{{ $userData['no_telpon']}}">
                            </div>
                        </div>
                        <img src="{{ asset($tPath.'assets3/img/user/'.$userData['foto']) }}" alt="" id="file" class="foto_user" onerror="imgError('file')">
                        <div class="crow">
                            <a href="/user" class="btn btn-danger">Kembali</a>
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
    <script src="{{ asset($tPath.'assets/js/dashboard.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/page/editAdmin.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>

</html>