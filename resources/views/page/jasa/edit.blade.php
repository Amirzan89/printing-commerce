<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jasa | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/preloader.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/page/editJasa.css') }}" />
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
    const reff = '/jasa';
    var csrfToken = "{{ csrf_token() }}";
    var email = "{{ $userAuth['email'] }}";
    var number = "{{ $userAuth['number'] }}";
    var uuid = "{{ $jasa['uuid'] }}";
    var dataFetch = {!! json_encode($jasa) !!};
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'jasa';
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
                    <h1>Edit Pesanan</h1>
                </div>
                <div class="d-flex align-items-stretch"
                    style="background-color: #ffffff; border-radius: 20px; box-shadow: rgba(145,158,171,0.2) 0px 0px 2px 0px, rgba(145,158,171,0.12) 0px 12px 24px -4px;">
                    <form id="editForm">
                        <div class="crow">
                            <label for="inpGambar">Gambar Jasa</label>
                            <button class="btn btn-success">Tambah Baru</button>
                        </div>
                        {{-- <div class="img" onclick="handleFileClick()" ondragover="handleDragOver(event)"
                            ondrop="handleDrop(event)">
                            <img src="{{ asset($tPath.'assets2/icon/upload.svg') }}" alt="" id="icon">
                            <span>Pilih File atau Jatuhkan File</span>
                            <input type="file" id="inpFoto" hidden onchange="handleFileChange(event)">
                            <img src="" alt="" id="file" style="display:none">
                        </div> --}}
                        <div class="crow">
                            <label for="">Nama Jasa</label>
                            <input type="text" id="inpJudul" value="{{ $jasa['nama_jasa'] }}">
                        </div>
                        <div class="crow">
                            <label for="">Deskripsi Jasa</label>
                            <textarea name="deskripsi" id="inpDeskripsi" placeholder="Masukkan Deskripsi Jasa" class="" style="height:120px">{{ $jasa['deskripsi'] }}</textarea>
                        </div>
                        <div class="crow">
                            <label for="">Kelas Jasa</label>
                            <select class="" aria-label="Default select example" id="inpKelasJasa">
                                <option value="" selected>Pilih Kelas Jasa</option>
                                <option value="printing" {{ $jasa['kategori'] == 'printing' ? 'selected' : '' }}>Printing</option>
                                <option value="desain" {{ $jasa['kategori'] == 'desain' ? 'selected' : '' }}>Desain</option>
                            </select>
                        </div>
                        <div class="crow">
                            <label for="">Harga Jasa</label>
                            <input type="number" id="inpHarga">
                        </div>
                        <div class="crow">
                            <label for="">Deskripsi Singkat</label>
                            <textarea name="deskripsiSingkat" id="inpDeskripsiSingkat" placeholder="Masukkan Deskripsi Singkat" class="" style="height:120px">{{ $jasa['deskripsi_singkat'] }}</textarea>
                        </div>
                        <div class="crow">
                            <label for="">Waktu Pengerjaan</label>
                            <input type="date" id="inpWaktuPengerjaan" value="{{ $jasa['waktu_pengerjaan'] }}">
                        </div>
                        <div class="crow">
                            <label for="">Total Revisi</label>
                            <input type="number" id="inpTotalRevisi" value="{{ $jasa['total_revisi'] }}">
                        </div>
                        <div class="crow">
                            <button type="submit" class="btn btn-success">
                                <img src="{{ asset($tPath.'assets2/icon/tambah.svg') }}" alt="" width="30" height="30">
                                <span>Edit</span>
                            </button>
                            <a href="/jasa" class="btn btn-danger">Kembali</a>
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
    <script src="{{ asset($tPath.'assets2/js/page/editJasa.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>

</html>