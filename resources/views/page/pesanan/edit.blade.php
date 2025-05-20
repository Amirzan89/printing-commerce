<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'css/preloader.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'css/page/editData.css') }}" />
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
    const reff = '/disi';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    var uuid = "{{ $disi['uuid'] }}";
    var data = {!! json_encode($disi) !!};
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'disi';
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
                            <label for="">Judul Pesanan</label>
                            <input type="text" id="inpJudul" value="{{ $disi['judul'] }}">
                        </div>
                        <div class="crow">
                            <div>
                                <label for="">Rentang Usia</label>
                                <select class="" aria-label="Default select example" id="inpRentangUsia" disabled>
                                    <option value="">Pilih Umur</option>
                                    <option value="0-3 tahun"
                                        {{ ($disi['rentang_usia'] == '0-3 tahun') ? 'selected' : ''}}>0-3 Tahun</option>
                                    <option value="4-6 tahun"
                                        {{ ($disi['rentang_usia'] == '4-6 tahun') ? 'selected' : ''}}>4-6 Tahun</option>
                                    <option value="7-9 tahun"
                                        {{ ($disi['rentang_usia'] == '7-9 tahun') ? 'selected' : ''}}>7-9 Tahun</option>
                                    <option value="10-12 tahun"
                                        {{ ($disi['rentang_usia'] == '10-12 tahun') ? 'selected' : ''}}>10-12 Tahun
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="">Link Video</label>
                                <input type="text" id="inpLinkVideo" value="{{ $disi['link_video'] }}">
                            </div>
                        </div>
                        <div class="crow">
                            <label for="">Deskripsi</label>
                            <textarea name="deskripsi" id="inpDeskripsi" placeholder="Masukkan Isi Pesanan"
                                class="" style="height:120px">{{ $disi['deskripsi'] }}</textarea>
                        </div>
                        <div class="img" onclick="handleFileClick()" ondragover="handleDragOver(event)"
                            ondrop="handleDrop(event)" style="{{ $disi['foto'] ? '' : 'border: 4px dashed #b1b1b1;'}}">
                            <img src="{{ asset($tPath.'img/icon/upload.svg') }}" alt="" id="icon">
                            <span>Pilih File atau Jatuhkan File</span>
                            <input type="file" id="inpFoto" hidden onchange="handleFileChange(event)">
                            <img src="{{ asset($tPath.'img/digital_literasi/' . $disi['foto']) }}" alt="" id="file">
                        </div>
                        <div class="crow">
                            <a href="/disi" class="btn btn-danger">Kembali</a>
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
    <script src="{{ asset($tPath.'js/page/editData.js') }}"></script>
    <script src="{{ asset($tPath.'js/popup.js') }}"></script>
</body>

</html>