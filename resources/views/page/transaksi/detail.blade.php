<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi | TATA</title>
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
    const reff = '/transaksi';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    var users = {!! json_encode($transaksiData) !!};
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'transaksi';
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
                    <h1>Edit Transaksi</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="/transaksi">Kelola Transaksi</a></li>
                            <li class="breadcrumb-item">Edit Transaksi</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-stretch" style="background-color: #ffffff; border-radius: 20px;">
                    <form id="editForm">
                        <div class="crow">
                            <label for="inpNama">Nama Lengkap</label>
                            <input type="text" id="inpNama" value="{{ $transaksiData['nama_lengkap']}}">
                        </div>
                        <div class="crow">
                            <label for="inpJenisKelamin">Jenis Kelamin</label>
                            <select id="inpJenisKelamin">
                                <option value="Laki-laki" {{ $transaksiData['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ $transaksiData['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="crow">
                            <label for="inpEmail">Email</label>
                            <input type="email" id="inpEmail" value="{{ $transaksiData['email']}}">
                        </div>
                        <div class="crow">
                            <label for="inpNomerTelepon">Nomor Telepon</label>
                            <input type="text" id="inpNomerTelepon" value="{{ $transaksiData['no_telpon'] ?? '' }}">
                        </div>
                        <div class="crow">
                            <label for="inpStatus">Status Transaksi</label>
                            <select id="inpStatus">
                                <option value="Menunggu Pembayaran" {{ $transaksiData['status'] == 'Menunggu Pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                <option value="Proses" {{ $transaksiData['status'] == 'Proses' ? 'selected' : '' }}>Proses</option>
                                <option value="Selesai" {{ $transaksiData['status'] == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Dibatalkan" {{ $transaksiData['status'] == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="crow">
                            <label for="inpTanggal">Tanggal Transaksi</label>
                            <input type="datetime-local" id="inpTanggal" value="{{ $transaksiData['tanggal'] ?? '' }}">
                        </div>
                        <div class="crow">
                            <label>Bukti Pembayaran</label>
                            <div class="img" onclick="handleFileClick()" ondragover="handleDragOver(event)"
                                ondrop="handleDrop(event)"
                                style="{{ $transaksiData['bukti_pembayaran'] ? '' : 'border: 4px dashed #b1b1b1;'}}">
                                <img src="{{ asset($tPath.'img/icon/upload.svg') }}" alt="" id="icon" style="{{ $transaksiData['bukti_pembayaran'] ? 'display: none;' : '' }}">
                                <span style="{{ $transaksiData['bukti_pembayaran'] ? 'display: none;' : '' }}">Pilih File atau Jatuhkan File</span>
                                <input type="file" id="inpFoto" hidden onchange="handleFileChange(event)">
                                @if($transaksiData['bukti_pembayaran'])
                                    <img src="{{ asset($tPath . $transaksiData['bukti_pembayaran']) }}" alt="Bukti Pembayaran" id="file" class="foto_admin" onerror="imgError('file')">
                                @endif
                            </div>
                        </div>
                        <div class="crow">
                            <a href="/transaksi" class="btn btn-danger">Kembali</a>
                            <button type="submit" class="btn btn-success">
                                <img src="{{ asset($tPath.'img/icon/edit.svg') }}" alt="">
                                <span>Simpan Perubahan</span>
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
    <script src="{{ asset($tPath.'assets2/js/page/editTransaksi.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>

</html>