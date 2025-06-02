<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/preloader.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/page/tambahAdmin.css') }}" />
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
            <div class="container-fluid">
                <div class="pagetitle mt-2 mt-sm-3 mt-md-3 mt-lg-4 mb-2 mb-sm-3 mb-md-3 mb-lg-4">
                    <h1>Tambah Admin</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="/admin">Kelola Admin</a></li>
                            <li class="breadcrumb-item">Tambah Admin</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-stretch" style="background-color: #ffffff; border-radius: 20px;">
                    <form id="tambahForm">
                        <div class="crow">
                            <label for="">Nama Lengkap</label>
                            <input type="text" id="inpNama">
                        </div>
                        <div class="crow">
                            <div style="width: 20%">
                                <label for="">Jenis Kelamin</label>
                                <select aria-label="Default select example" id="inpJenisKelamin">
                                    <option value="" selected>Pilih Kelamin</option>
                                    <option value="laki-laki">Laki-Laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div style="width: 25%">
                                <label>Role</label>
                                <select name="role" aria-label="Default select example" id="inpRole">
                                    <option value="" disabled selected>Pilih Role</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="admin_chat">Admin Chat</option>
                                    <option value="admin_pemesanan">Admin Pemesanan</option>
                                </select>
                            </div>
                            <div style="flex: 1">
                                <label for="">Nomer Telepon</label>
                                <input type="text" id="inpNomerTelepon">
                            </div>
                        </div>
                        <div class="crow">
                            <div>
                                <label for="">Email</label>
                                <input type="text" id="inpEmail">
                            </div>
                            <div>
                                <label for="">Password</label>
                                <div style="position: relative">
                                    <input type="password" id="inpPassword" style="padding-right: 45px;" oninput="showEyePass()">
                                    <div id="iconPass" onclick="showPass()" style="display: none;">
                                        <img src="{{ asset($tPath.'assets2/icon/eye-slash.svg') }}" alt="" id="passClose">
                                        <img src="{{ asset($tPath.'assets2/icon/eye.svg') }}" alt="" id="passShow" style="display: none">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="img" onclick="handleFileClick()" ondragover="handleDragOver(event)"
                            ondrop="handleDrop(event)">
                            <img src="{{ asset($tPath.'assets2/icon/upload.svg') }}" alt="" id="icon">
                            <span>Pilih File atau Jatuhkan File</span>
                            <input type="file" id="inpFoto" hidden onchange="handleFileChange(event)">
                            <img src="" alt="" id="file" style="display:none">
                        </div>
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
    <script src="{{ asset($tPath.'assets2/js/page/tambahAdmin.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>
</html>