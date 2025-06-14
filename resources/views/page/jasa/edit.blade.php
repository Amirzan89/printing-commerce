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
    <!-- Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" />
    <style>
        .container-fluid {
            background-color: #F6F9FF;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 24px;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            width: 100%;
            margin-bottom: 16px;
        }
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76,175,80,0.1);
        }
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .gallery-item {
            width: 150px;
            height: 150px;
            position: relative;
            border-radius: 4px;
            overflow: hidden;
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .add-image-btn {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }
        .add-image-btn:hover {
            border-color: #4CAF50;
            color: #4CAF50;
        }
        .btn-primary {
            background: #4CAF50;
            border: none;
            padding: 10px 20px;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-secondary {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px 20px;
            color: #333;
            border-radius: 4px;
            cursor: pointer;
        }
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .btn-add-new {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .image-preview {
            position: relative;
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .thumbnail-container {
            margin-bottom: 20px;
        }
        .carousel-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .carousel-slide {
            position: relative;
            height: 250px;
            overflow: hidden;
            border-radius: 8px;
        }
        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .slick-prev:before, .slick-next:before {
            color: #007bff;
        }
        .upload-placeholder {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #777;
        }
        .upload-placeholder i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .existing-image {
            position: relative;
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .thumbnail-gallery {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            margin-top: 15px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .thumbnail.active {
            border-color: #4CAF50;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-add {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-return {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-edit-mode {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: auto;
        }
        
        .mode-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .form-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
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
    const reff = '/jasa';
    var csrfToken = "{{ csrf_token() }}";
    var userAuth = @json($userAuth);
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
            <div class="container-fluid">
                <div class="card">
                    <form id="editForm">
                        <input type="hidden" name="id_jasa" value="{{ $jasa['uuid'] }}">

                        <div class="section-title">
                            <h5>Gambar Produk</h5>
                            <button type="button" class="btn-add-new" onclick="document.getElementById('inpImages').click()">
                                <i class="fas fa-plus"></i> Tambah Baru
                            </button>
                        </div>

                        <div class="image-gallery" id="imageGallery">
                            @if(isset($jasa['images']) && count($jasa['images']) > 0)
                                @foreach($jasa['images'] as $image)
                                    <div class="gallery-item" data-id="{{ $image['id'] }}">
                                        <img src="{{ asset($tPath.'assets3/img/jasa/gallery/'.$image['image_path']) }}" alt="Gallery Image">
                                        <button type="button" class="remove-btn" onclick="removeImage(this)">×</button>
                                    </div>
                                @endforeach
                            @endif
                            <div class="add-image-btn" onclick="document.getElementById('inpImages').click()">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Gambar</span>
                            </div>
                        </div>
                        <input type="file" id="inpImages" name="images[]" hidden multiple onchange="handleImagesChange(event)">
                        <input type="hidden" id="deletedImages" name="deleted_images" value="">

                        <div class="form-group">
                            <label class="form-label">Deskripsi Produk</label>
                            <textarea class="form-control" name="deskripsi_paket_jasa" rows="4" placeholder="Masukkan deskripsi produk">{{ $jasa['deskripsi_paket_jasa'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kelas Jasa</label>
                            <select class="form-control" name="kelas_jasa">
                                <option value="">Pilih Kelas Jasa</option>
                                <option value="basic" {{ $jasa['kategori'] == 'basic' ? 'selected' : '' }}>Basic</option>
                                <option value="standard" {{ $jasa['kategori'] == 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="premium" {{ $jasa['kategori'] == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                            <small class="text-muted">Ketika dipilih yang bawah baru muncul</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Harga Jasa</label>
                            <input type="number" class="form-control" name="harga_paket_jasa" value="{{ $jasa['harga_paket_jasa'] ?? '' }}" placeholder="Masukkan harga jasa">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi Singkat</label>
                            <textarea class="form-control" name="fitur" rows="3" placeholder="Masukkan deskripsi singkat">{{ $jasa['fitur'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Waktu Pengerjaan</label>
                            <input type="text" class="form-control" name="waktu_pengerjaan" value="{{ $jasa['waktu_pengerjaan'] ?? '' }}" placeholder="Isi waktu pengerjaan">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Total Revisi</label>
                            <input type="number" class="form-control" name="maksimal_revisi" value="{{ $jasa['maksimal_revisi'] ?? '' }}" placeholder="Masukkan jumlah revisi">
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn-secondary" onclick="window.location.href='/jasa'">Cancel</button>
                            <button type="submit" class="btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
            @include('components.admin.footer')
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
    <script>
        // Store deleted image IDs
        let deletedImageIds = [];

        function handleImagesChange(event) {
            const files = event.target.files;
            const gallery = document.getElementById('imageGallery');
            const addButton = gallery.querySelector('.add-image-btn');

            for (let file of files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'gallery-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = function() {
                        gallery.removeChild(galleryItem);
                    };
                    
                    galleryItem.appendChild(img);
                    galleryItem.appendChild(removeBtn);
                    gallery.insertBefore(galleryItem, addButton);
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage(button) {
            const item = button.parentElement;
            const imageId = item.dataset.id;
            if (imageId) {
                deletedImageIds.push(imageId);
                document.getElementById('deletedImages').value = JSON.stringify(deletedImageIds);
            }
            item.remove();
        }

        $(document).ready(function() {
            $('#editForm').submit(function(e) {
                e.preventDefault();
                $('#preloader').show();

                const formData = new FormData(this);
                
                $.ajax({
                    url: domain + '/api/jasa/update',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('#preloader').hide();
                        if (response.status === 'success') {
                            showGreenPopup(response.message);
                            setTimeout(function() {
                                window.location.href = reff;
                            }, 2000);
                        } else {
                            showRedPopup(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#preloader').hide();
                        const response = xhr.responseJSON;
                        if (response && response.message) {
                            showRedPopup(response.message);
                        } else {
                            showRedPopup('Terjadi kesalahan. Silakan coba lagi.');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>