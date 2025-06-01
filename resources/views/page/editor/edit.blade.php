<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin | TATA</title>
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
    const reff = '/admin';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    var users = {!! json_encode($adminData) !!};
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
                <div class="pagetitle">
                    <h1>Edit Admin</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="/admin">Kelola Admin</a></li>
                            <li class="breadcrumb-item">Edit Admin</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-stretch" style="background-color: #ffffff; border-radius: 20px;">
                    <form id="editForm">
                        <div class="crow">
                            <label for="">Nama Lengkap</label>
                            <input type="text" id="inpNama" value="{{ $adminData['nama_lengkap']}}">
                        </div>
                        <div class="crow">
                            <div style="width: 20%">
                                <label for="">Jenis Kelamin</label>
                                <select class="" aria-label="Default select example" id="inpJenisKelamin">
                                    <option value="laki-laki"
                                        {{ ($adminData['jenis_kelamin'] == 'laki-laki') ? 'selected' : ''}}>Laki-Laki
                                    </option>
                                    <option value="perempuan"
                                        {{ ($adminData['jenis_kelamin'] == 'perempuan') ? 'selected' : ''}}>Perempuan
                                    </option>
                                </select>
                            </div>
                            <div style="width: 25%">
                                <label>Role</label>
                                <select name="role" aria-label="Default select example" id="inpRole">
                                    <option value="admin disi" {{ ($adminData['role'] == 'admin disi') ? 'selected' : ''}}>Admin Disi</option>
                                    <option value="admin emotal" {{ ($adminData['role'] == 'admin emotal') ? 'selected' : ''}}>Admin Emotal</option>
                                    <option value="admin nutrisi" {{ ($adminData['role'] == 'admin nutrisi') ? 'selected' : ''}}>Admin Nutrisi</option>
                                    <option value="admin pengasuhan" {{ ($adminData['role'] == 'admin pengasuhan') ? 'selected' : ''}}>Admin Pengasuhan</option>
                                </select>
                            </div>
                            <div style="flex: 1">
                                <label for="">Nomer Telepon</label>
                                <input type="text" id="inpNomerTelepon" value="{{ $adminData['no_telpon']}}">
                            </div>
                        </div>
                        <div class="crow">
                            <div>
                                <label for="">Email</label>
                                <input type="text" id="inpEmail" value="{{ $adminData['email']}}">
                            </div>
                            <div>
                                <label for="">Password</label>
                                <div style="position: relative">
                                    <input type="password" id="inpPassword" style="padding-right: 45px;" oninput="showEyePass()">
                                    <div id="iconPass" onclick="showPass()" style="display: none;">
                                        <img src="{{ asset($tPath.'img/icon/eye-slash.svg') }}" alt="" id="passClose">
                                        <img src="{{ asset($tPath.'img/icon/eye.svg') }}" alt="" id="passShow" style="display: none">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="img" onclick="handleFileClick()" ondragover="handleDragOver(event)"
                            ondrop="handleDrop(event)"
                            style="{{ $adminData['foto'] ? '' : 'border: 4px dashed #b1b1b1;'}}">
                            <img src="{{ asset($tPath.'img/icon/upload.svg') }}" alt="" id="icon">
                            <span>Pilih File atau Jatuhkan File</span>
                            <input type="file" id="inpFoto" hidden onchange="handleFileChange(event)">
                            <img src="{{ route('download.foto.admin', ['id'=>$adminData['uuid']]) }}" alt="" id="file" class="foto_admin" onerror="imgError('file')">
                        </div>
                        <div class="crow">
                            <a href="/admin" class="btn btn-danger">Kembali</a>
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
    <script src="{{ asset($tPath.'assets/js/dashboard.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/page/editAdmin.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>

</html>