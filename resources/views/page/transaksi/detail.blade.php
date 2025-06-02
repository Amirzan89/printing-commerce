<?php
$tPath = app()->environment('local') ? '' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran | TATA</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset($tPath.'img/icon/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/preloader.css') }}" />
    <style>
        .detail-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .detail-title {
            font-size: 24px;
            margin: 0;
        }
        .detail-subtitle {
            color: #666;
            margin: 5px 0 20px 0;
        }
        .detail-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f8f9fa;
        }
        .form-control:disabled {
            background-color: #e9ecef;
        }
        .price-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .price-item {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .price-item p {
            margin: 0;
            font-weight: 500;
        }
        .banner-section {
            margin: 20px 0;
        }
        .banner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .banner-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .banner-input {
            width: 100%;
        }
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-save {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
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
    const domain = window.location.protocol + '//' + window.location.hostname + ":" + window.location.port;
    const reff = '/transaksi';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    var transaksi = @json($transaksiData);
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
            <div class="container-fluid">
                <div class="detail-container">
                    <div class="detail-header">
                        <div>
                            <h1 class="detail-title">Detail Pembayaran</h1>
                            <p class="detail-subtitle">{{ $transaksiData['nama_metode_pembayaran'] ?? 'Tidak Ada Metode Pembayaran' }}</p>
                        </div>
                    </div>

                    <div class="detail-form">
                        <div class="form-group">
                            <label>Nomor Rekening</label>
                            <input type="text" class="form-control" value="{{ $transaksiData['no_rekening'] ?? 5252522424 }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Harga Jasa</label>
                            <select class="form-control" disabled>
                                <option>Regular</option>
                            </select>
                        </div>

                        <div class="price-grid">
                            <div class="price-item">
                                <p>Desain Logo</p>
                                <p>150.000</p>
                            </div>
                            <div class="price-item">
                                <p>Desain Poster</p>
                                <p>150.000</p>
                            </div>
                            <div class="price-item">
                                <p>Desain Banner</p>
                                <p>150.000</p>
                            </div>
                        </div>

                        <div class="banner-section">
                            <div class="banner-header">
                                <h5>Cetak Banner</h5>
                                <p>Total Harga: Rp {{ number_format($transaksiData['total_pembayaran'] ?? 150000, 0, ',', '.') }}</p>
                            </div>
                            <div class="banner-grid">
                                <div>
                                    <label>Bahan Banner</label>
                                    <select class="form-control" disabled>
                                        <option>Flexi China</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Ukuran</label>
                                    <select class="form-control" disabled>
                                        <option>1 x 2 m</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Total Harga</label>
                                    <input type="text" class="form-control" value="200.000" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="/transaksi" class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-save">Save</button>
                        </div>
                    </div>
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
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/page/detailTransaksi.js') }}"></script>
</body>
</html>