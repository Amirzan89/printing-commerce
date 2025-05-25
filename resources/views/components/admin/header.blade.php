{{-- <script>
    var errFotos = [];
    function imgError(err) {
        errFotos.push(err);
        var image = document.getElementById(err);
        if (image && image.src !== "{{ route('download.foto.default') }}") {
            image.src = "{{ route('download.foto.default') }}";
        }
    }
</script> --}}
<header class="app-header" style="box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);">
    <nav class="navbar navbar-expand-lg navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>
        </ul>
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon-hover rounded-pill" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset($tPath.'assets2/icon/header/notification.png') }}" alt="">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2" style="width: 70vw;">
                        <ul class="message-body row gap-3">
                            @foreach ($headerData as $iHeaderData)
                                <li class="col-12 d-flex align-items-center gap-4 p-2">
                                    <img src="{{ asset($tPath.'assets2/img/profile.png') }}" alt="Profile" id="top_bar" class="rounded-circle foto_admin" style="width: 50px; height: 50px;">
                                    <div>
                                        <h5>{{ $iHeaderData['nama_editor']}}</h5>
                                        <p>{{ $iHeaderData['deskripsi_pengerjaan']}}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                <a class="nav-link" href="/chat" class="btn btn-outline-primary mx-3 mt-2 d-block">
                    <div class="rounded-pill">
                        <img src="{{ asset($tPath.'assets2/icon/header/chat.png') }}" alt="">
                    </div>
                </a>
            </ul>
        </div>
    </nav>
</header>
{{-- <script>
    window.addEventListener('load', function() {
        var imgs = document.querySelectorAll('.foto_admin');
        imgs.forEach(function(image) {
            if (errFotos.includes(image.id)) {
                image.src = "{{ route('download.foto.default') }}";
            }
            if (image.complete && image.naturalWidth === 0) {
                image.src = "{{ route('download.foto.default') }}";
            }
        });
    });
</script> --}}