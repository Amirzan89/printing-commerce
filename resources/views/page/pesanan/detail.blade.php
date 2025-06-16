<?php
$tPath = app()->environment('local') ? '' : '';
exit();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan | TATA</title>
    <link href="{{ asset($tPath.'assets2/img/logo.png') }}" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/popup.css') }}" />
    <link rel="stylesheet" href="{{ asset($tPath.'assets2/css/preloader.css') }}" />
    <style>
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .detail-title {
            font-size: 1.5rem;
            margin: 0;
            color: #333;
        }
        .detail-subtitle {
            color: #666;
            margin: 0.5rem 0;
        }
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #ddd;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .form-control:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        .dropzone-container {
            border: 2px dashed #ddd;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .dropzone-container:hover {
            border-color: #4CAF50;
        }
        .img-preview {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-success {
            background-color: #4CAF50;
            border: none;
        }
        .btn-success:hover {
            background-color: #43A047;
        }
        .btn-danger {
            background-color: #f44336;
            border: none;
        }
        .btn-danger:hover {
            background-color: #e53935;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .status-menunggu { background-color: #FFF3CD; color: #856404; }
        .status-proses { background-color: #CCE5FF; color: #004085; }
        .status-dikerjakan { background-color: #D4EDDA; color: #155724; }
        .status-revisi { background-color: #F8D7DA; color: #721C24; }
        .status-selesai { background-color: #D1E7DD; color: #0F5132; }
        .status-dibatalkan { background-color: #E2E3E5; color: #383D41; }
        
        .revision-item {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6 !important;
        }
        .file-item {
            background-color: white;
            transition: all 0.3s ease;
        }
        .file-item:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.75rem;
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
    const reff = '/pesanan';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @php
            $nav = 'pesanan';
        @endphp
        @include('components.admin.sidebar')
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            @include('components.admin.header')
            <!--  Header End -->
            <div class="container-fluid" style="background-color: #F6F9FF">
                <div class="pagetitle mt-2 mt-sm-3 mt-md-3 mt-lg-4 mb-2 mb-sm-3 mb-md-3 mb-lg-4">
                    <h1>Detail Pesanan</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="/pesanan">Kelola Pesanan</a></li>
                            <li class="breadcrumb-item active">Detail Pesanan</li>
                        </ol>
                    </nav>
                </div>

                <div class="container py-4">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Detail Pesanan</h2>

                            <form id="editForm" class="needs-validation" novalidate>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">ID Pesanan</label>
                                            <input type="text" class="form-control" value="{{ $pesananData['uuid'] }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Nama Pelanggan</label>
                                            <input type="text" class="form-control" value="{{ $pesananData['nama_pelanggan'] }}" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Jenis Jasa</label>
                                            <input type="text" class="form-control" value="{{ $pesananData['jenis_jasa'] }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Kelas Jasa</label>
                                            <input type="text" class="form-control" value="{{ $pesananData['kelas_jasa'] }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Revisi</label>
                                            <input type="text" class="form-control" value="{{ $pesananData['revisi_used'] }}/{{ $pesananData['maksimal_revisi'] }} (Sisa: {{ $pesananData['sisa_revisi'] }})" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">Deskripsi Pesanan</label>
                                            <textarea class="form-control" rows="4" disabled>{{ $pesananData['deskripsi'] }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">Status Pesanan</label>
                                            <div class="status-badge status-{{ strtolower($pesananData['status']) }}">
                                                {{ $pesananData['status'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Brief Section -->
                                @if($pesananData['catatan_pesanan'])
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">Catatan Pesanan</label>
                                            <textarea class="form-control" rows="4" disabled>{{ $pesananData['catatan_pesanan']->catatan_pesanan }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                @if($pesananData['catatan_pesanan']->hasGambar())
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">Gambar Referensi</label>
                                            <div class="dropzone-container">
                                                <img src="{{ $pesananData['catatan_pesanan']->gambar_referensi }}" alt="Gambar Referensi" class="img-fluid img-preview">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endif

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Estimasi Pengerjaan</label>
                                            <div class="input-group">
                                                <input type="date" class="form-control" value="{{ $pesananData['estimasi_waktu']['dari'] }}" disabled>
                                                <span class="input-group-text">Sampai</span>
                                                <input type="date" class="form-control" value="{{ $pesananData['estimasi_waktu']['sampai'] }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Nama Editor</label>
                                            <select class="form-select" id="editor" name="editor">
                                                <option value="">Pilih Editor</option>
                                                @foreach($editorList as $editor)
                                                    <option value="{{ $editor->id_editor }}" {{ $pesananData['editor']['id'] == $editor->id_editor ? 'selected' : '' }}>
                                                        {{ $editor->nama_editor }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary" onclick="window.location.href='/pesanan'">Cancel</button>
                                        <button type="submit" class="btn btn-success">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Riwayat Revisi Section -->
                    @if(count($pesananData['revisions']) > 0)
                    <div class="card mt-4">
                        <div class="card-body">
                            <h3 class="card-title">Riwayat Revisi</h3>
                            
                            @foreach($pesananData['revisions'] as $revision)
                            <div class="revision-item border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Revisi #{{ $revision->urutan_revisi }}</h5>
                                    <small class="text-muted">{{ $revision->created_at->format('d M Y H:i') }}</small>
                                </div>
                                
                                @if($revision->catatan_user)
                                <div class="mb-3">
                                    <strong>Catatan User:</strong>
                                    <p class="mb-0">{{ $revision->catatan_user }}</p>
                                </div>
                                @endif
                                
                                <!-- User Files -->
                                @if(count($revision->userFiles) > 0)
                                <div class="mb-3">
                                    <strong>File dari User:</strong>
                                    <div class="row">
                                        @foreach($revision->userFiles as $file)
                                        <div class="col-md-4 mb-2">
                                            <div class="file-item p-2 border rounded">
                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                                <span>{{ $file->file_name }}</span>
                                                @if($file->catatan_user)
                                                <small class="d-block text-muted">{{ $file->catatan_user }}</small>
                                                @endif
                                                <small class="d-block text-muted">{{ $file->uploaded_at->format('d M Y H:i') }}</small>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Editor Files -->
                                @if(count($revision->editorFiles) > 0)
                                <div class="mb-3">
                                    <strong>Response dari Editor:</strong>
                                    <div class="row">
                                        @foreach($revision->editorFiles as $file)
                                        <div class="col-md-4 mb-2">
                                            <div class="file-item p-2 border rounded bg-light">
                                                <i class="fas fa-file-pdf text-success me-2"></i>
                                                <span>{{ $file->file_name }}</span>
                                                <span class="badge badge-{{ $file->type === 'final' ? 'success' : 'info' }} ms-2">
                                                    {{ ucfirst($file->type) }}
                                                </span>
                                                @if($file->catatan_editor)
                                                <small class="d-block text-muted">{{ $file->catatan_editor }}</small>
                                                @endif
                                                <small class="d-block text-muted">{{ $file->uploaded_at->format('d M Y H:i') }}</small>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock me-2"></i>
                                    Menunggu response dari editor
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
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
    <script src="{{ asset($tPath.'assets/libs/simplebar/dist/simplebar.js') }}"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
</body>

</html>