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
            <div class="container-fluid" style="background-color: #F6F9FF">
                <div class="pagetitle">
                    <h1>Edit Jasa</h1>
                </div>
                <div class="d-flex align-items-stretch"
                    style="background-color: #ffffff; border-radius: 20px; box-shadow: rgba(145,158,171,0.2) 0px 0px 2px 0px, rgba(145,158,171,0.12) 0px 12px 24px -4px;">
                    <form id="editForm" enctype="multipart/form-data">
                        <input type="hidden" name="id_jasa" value="{{ $jasa['uuid'] }}">
                        
                        <div class="form-section-header">
                            <label for="inpThumbnail">Gambar Produk</label>
                            {{-- <div class="edit-only">
                                <button type="button" class="btn btn-success btn-sm" onclick="document.getElementById('inpThumbnail').click()">
                                    <i class="fas fa-plus"></i> Tambah Baru
                                </button>
                            </div> --}}
                        </div>
                        
                        <!-- Carousel for images -->
                        <div class="carousel-container">
                            <div class="carousel" id="imageCarousel">
                                <!-- Carousel slides will be generated with JavaScript -->
                            </div>
                            
                            <!-- Thumbnails for navigation -->
                            <div class="thumbnail-gallery" id="thumbnailGallery">
                                <!-- Thumbnails will be generated with JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Hidden file inputs -->
                        <div class="edit-only">
                            <div class="thumbnail-container" style="display: none;">
                                <div class="img" onclick="handleThumbnailClick()" ondragover="handleDragOver(event)"
                                    ondrop="handleThumbnailDrop(event)">
                                    @if(isset($jasa['thumbnail_jasa']) && !empty($jasa['thumbnail_jasa']))
                                        <img src="{{ asset($tPath.'assets3/img/jasa' . $jasa['thumbnail_jasa']) }}" alt="Thumbnail" id="thumbnailPreview">
                                        <img src="{{ asset($tPath.'assets2/icon/upload.svg') }}" alt="" id="thumbnailIcon" style="display:none">
                                    @else
                                        <img src="{{ asset($tPath.'assets2/icon/upload.svg') }}" alt="" id="thumbnailIcon">
                                        <img src="" alt="" id="thumbnailPreview" style="display:none">
                                    @endif
                                    <span>Pilih File atau Jatuhkan File</span>
                                    <input type="file" id="inpThumbnail" name="thumbnail_jasa" hidden onchange="handleThumbnailChange(event)">
                                </div>
                            </div>
                            
                            <div class="crow">
                                <label for="inpImages">Galeri Gambar (Opsional)</label>
                            </div>
                            <div class="image-preview-container" id="imagePreviewContainer">
                                <!-- Existing images will be loaded here from JavaScript -->
                                <div class="upload-placeholder" onclick="document.getElementById('inpImages').click()">
                                    <i class="fas fa-plus"></i>
                                    <span>Tambah Gambar</span>
                                </div>
                            </div>
                            <input type="file" id="inpImages" name="images[]" hidden multiple onchange="handleImagesChange(event)">
                            <input type="hidden" id="deletedImages" name="deleted_images" value="">
                        </div>
                        
                        <div class="crow">
                            <label for="inpDeskripsi">Deskripsi Produk</label>
                            <textarea name="deskripsi_paket_jasa" id="inpDeskripsi" placeholder="Masukkan Deskripsi Jasa" class="" style="height:120px">{{ $jasa['deskripsi_paket_jasa'] ?? '' }}</textarea>
                        </div>
                        
                        <div class="crow">
                            <label for="inpKelasJasa">Kelas Jasa</label>
                            <select class="" aria-label="Default select example" id="inpKelasJasa" name="kategori">
                                <option value="">Pilih Kelas Jasa</option>
                                <option value="printing" {{ $jasa['kategori'] == 'printing' ? 'selected' : '' }}>Printing</option>
                                <option value="desain" {{ $jasa['kategori'] == 'desain' ? 'selected' : '' }}>Desain</option>
                            </select>
                        </div>
                        <small class="text-muted">Ketika dipilih yang bawah baru muncul</small>
                        
                        <div class="crow">
                            <label for="inpJudul">Nama Jasa</label>
                            <input type="text" id="inpJudul" name="nama_jasa" value="{{ $jasa['nama_jasa'] }}">
                        </div>
                        
                        <div class="crow">
                            <label for="inpNamaPaket">Nama Paket Jasa</label>
                            <input type="text" id="inpNamaPaket" name="nama_paket_jasa" value="{{ $jasa['nama_paket_jasa'] ?? '' }}">
                        </div>
                        
                        <div class="crow">
                            <label for="inpHarga">Harga Jasa</label>
                            <input type="number" id="inpHarga" name="harga_paket_jasa" value="{{ $jasa['harga_paket_jasa'] ?? '' }}">
                        </div>
                        
                        <div class="crow">
                            <label for="inpFitur">Deskripsi Singkat</label>
                            <textarea name="fitur" id="inpFitur" placeholder="Masukkan Fitur" class="" style="height:120px">{{ $jasa['fitur'] ?? '' }}</textarea>
                        </div>
                        
                        <div class="crow">
                            <label for="inpWaktuPengerjaan">Waktu Pengerjaan</label>
                            <input type="date" id="inpWaktuPengerjaan" name="waktu_pengerjaan" value="{{ isset($jasa['waktu_pengerjaan']) ? date('Y-m-d', strtotime($jasa['waktu_pengerjaan'])) : '' }}">
                        </div>
                        
                        <div class="crow">
                            <label for="inpTotalRevisi">Total Revisi</label>
                            <input type="number" id="inpTotalRevisi" name="maksimal_revisi" value="{{ $jasa['maksimal_revisi'] ?? '' }}">
                        </div>
                        
                        <div class="crow">
                            <div class="edit-only">
                                <button type="submit" class="btn btn-success">
                                    <img src="{{ asset($tPath.'assets2/icon/tambah.svg') }}" alt="" width="30" height="30">
                                    <span>Simpan</span>
                                </button>
                            </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script src="{{ asset($tPath.'assets2/js/popup.js') }}"></script>
    <script>
        // Store deleted image IDs
        let deletedImageIds = [];
        
        // Thumbnail handling
        function handleThumbnailClick() {
            document.getElementById('inpThumbnail').click();
        }
        
        function handleDragOver(event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        function handleThumbnailDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            
            if (event.dataTransfer.files.length) {
                const file = event.dataTransfer.files[0];
                if (file.type.match('image.*')) {
                    document.getElementById('inpThumbnail').files = event.dataTransfer.files;
                    previewThumbnail(file);
                }
            }
        }
        
        function handleThumbnailChange(event) {
            if (event.target.files.length) {
                previewThumbnail(event.target.files[0]);
            }
        }
        
        function previewThumbnail(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const thumbnailPreview = document.getElementById('thumbnailPreview');
                const thumbnailIcon = document.getElementById('thumbnailIcon');
                
                thumbnailPreview.src = e.target.result;
                thumbnailPreview.style.display = 'block';
                thumbnailIcon.style.display = 'none';
                
                // Add to carousel
                updateCarousel();
            };
            reader.readAsDataURL(file);
        }
        
        // Multiple images handling
        function handleImagesChange(event) {
            if (event.target.files.length) {
                for (let i = 0; i < event.target.files.length; i++) {
                    previewImage(event.target.files[i]);
                }
                updateCarousel();
            }
        }
        
        function previewImage(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('imagePreviewContainer');
                const previewDiv = document.createElement('div');
                previewDiv.className = 'image-preview';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Preview';
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    container.removeChild(previewDiv);
                    updateCarousel();
                };
                
                previewDiv.appendChild(img);
                previewDiv.appendChild(removeBtn);
                
                // Insert before the "+" placeholder
                const placeholder = container.querySelector('.upload-placeholder');
                container.insertBefore(previewDiv, placeholder);
            };
            reader.readAsDataURL(file);
        }
        
        // Load existing images
        function loadExistingImages() {
            // This would typically be loaded from your backend
            // For now, we'll simulate with the dataFetch variable
            if (dataFetch && dataFetch.images) {
                const container = document.getElementById('imagePreviewContainer');
                const placeholder = container.querySelector('.upload-placeholder');
                
                dataFetch.images.forEach(image => {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    previewDiv.dataset.id = image.id;
                    
                    const img = document.createElement('img');
                    img.src = `${tPath}assets3/img/jasa/gallery/${image.image_path}`;
                    img.alt = 'Gallery Image';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = function() {
                        // Add to deleted images list
                        deletedImageIds.push(image.id);
                        document.getElementById('deletedImages').value = JSON.stringify(deletedImageIds);
                        
                        // Remove from DOM
                        container.removeChild(previewDiv);
                        updateCarousel();
                    };
                    
                    previewDiv.appendChild(img);
                    previewDiv.appendChild(removeBtn);
                    
                    container.insertBefore(previewDiv, placeholder);
                });
                
                updateCarousel();
            }
        }
        
        // Carousel functionality
        function initCarousel() {
            $('#imageCarousel').slick({
                dots: false,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true
            });
        }
        
        function updateCarousel() {
            // Destroy existing carousel if it exists
            if ($('#imageCarousel').hasClass('slick-initialized')) {
                $('#imageCarousel').slick('unslick');
            }
            
            // Clear carousel
            $('#imageCarousel').empty();
            
            // Clear thumbnails
            $('#thumbnailGallery').empty();
            
            // Add thumbnail to carousel if it exists
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            if (thumbnailPreview && thumbnailPreview.style.display !== 'none' && thumbnailPreview.src) {
                // Add to main carousel
                const slide = document.createElement('div');
                slide.className = 'carousel-slide';
                const img = document.createElement('img');
                img.src = thumbnailPreview.src;
                img.alt = 'Thumbnail';
                slide.appendChild(img);
                $('#imageCarousel').append(slide);
                
                // Add to thumbnail gallery
                const thumb = document.createElement('div');
                thumb.className = 'thumbnail active';
                thumb.dataset.index = 0;
                const thumbImg = document.createElement('img');
                thumbImg.src = thumbnailPreview.src;
                thumbImg.alt = 'Thumbnail';
                thumb.appendChild(thumbImg);
                $('#thumbnailGallery').append(thumb);
            }
            
            // Add all other images to carousel
            const previews = document.querySelectorAll('.image-preview img');
            previews.forEach((preview, index) => {
                // Add to main carousel
                const slide = document.createElement('div');
                slide.className = 'carousel-slide';
                const img = document.createElement('img');
                img.src = preview.src;
                img.alt = 'Gallery Image';
                slide.appendChild(img);
                $('#imageCarousel').append(slide);
                
                // Add to thumbnail gallery
                const thumb = document.createElement('div');
                thumb.className = 'thumbnail';
                thumb.dataset.index = thumbnailPreview && thumbnailPreview.style.display !== 'none' && thumbnailPreview.src ? index + 1 : index;
                const thumbImg = document.createElement('img');
                thumbImg.src = preview.src;
                thumbImg.alt = 'Gallery Image';
                thumb.appendChild(thumbImg);
                $('#thumbnailGallery').append(thumb);
            });
            
            // If no images, add placeholder
            if ((!thumbnailPreview || thumbnailPreview.style.display === 'none' || !thumbnailPreview.src) && previews.length === 0) {
                const slide = document.createElement('div');
                slide.className = 'carousel-slide';
                const img = document.createElement('img');
                img.src = "{{ asset($tPath.'assets2/icon/image-placeholder.svg') }}";
                img.alt = 'Placeholder';
                slide.appendChild(img);
                $('#imageCarousel').append(slide);
            }
            
            // Reinitialize carousel
            initCarousel();
            
            // Handle thumbnail clicks
            $('.thumbnail').on('click', function() {
                const index = $(this).data('index');
                $('#imageCarousel').slick('slickGoTo', index);
                
                // Update active thumbnail
                $('.thumbnail').removeClass('active');
                $(this).addClass('active');
            });
            
            // Update thumbnail on slide change
            $('#imageCarousel').on('afterChange', function(event, slick, currentSlide) {
                $('.thumbnail').removeClass('active');
                $(`.thumbnail[data-index="${currentSlide}"]`).addClass('active');
            });
        }
        
        // Form submission
        $(document).ready(function() {
            // Load existing images
            loadExistingImages();
            
            // Initialize carousel
            initCarousel();
            
            $('#editForm').submit(function(e) {
                e.preventDefault();
                
                if (isDetailMode) {
                    window.location.href = '/jasa/edit/' + uuid;
                    return false;
                }
                
                // Show preloader
                $('#preloader').show();
                
                const formData = new FormData(this);
                
                // Add all selected images
                const imageInput = document.getElementById('inpImages');
                if (imageInput.files.length > 0) {
                    for (let i = 0; i < imageInput.files.length; i++) {
                        formData.append('images[]', imageInput.files[i]);
                    }
                }
                
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