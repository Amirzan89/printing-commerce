<?php
?>
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="/dashboard" class="text-nowrap logo-img" style="display: flex; gap: 10px; align-items:center">
                <img src="{{ asset($tPath.'img/icon/logo.png') }}" alt="" style="width: 100px; height:50px;"></img>
                <span class="hide-menu" style="color:black; text-decoration: none; font-size:27px; font-weight:600;">EduAksi</span>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8"></i>
            </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="" style="margin-top: 15px">
            <ul id="sidebarnav" style="display:flex; flex-direction: column; gap: 2px;">
                <li class="sidebar-item {{ $nav == 'dashboard' ? 'selected' : ''}}">
                    <a class="sidebar-link {{ $nav == 'dashboard' ? 'active' : ''}}" href="/dashboard"
                        aria-expanded="false">
                        <img src="{{ asset($tPath.'img/icon/sidebar/dashboard.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'dashboard' ? 'block' : 'none'}}" class="white">
                        <img src="{{ asset($tPath.'img/icon/sidebar/dashboard_dark.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'dashboard' ? 'none' : 'block'}}" class="dark">
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

                @if($userAuth['role'] == 'super_admin' || $userAuth['role'] == 'admin')
                <li class="sidebar-item {{ $nav == 'jasa' ? 'selected' : ''}}">
                    <a class="sidebar-link {{ $nav == 'jasa' ? 'active' : ''}}" href="/jasa" aria-expanded="false">
                        <img src="{{ asset($tPath.'img/icon/sidebar/jasa.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'jasa' ? 'block' : 'none'}}" class="white">
                        <img src="{{ asset($tPath.'img/icon/sidebar/jasa_dark.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'jasa' ? 'none' : 'block'}}" class="dark">
                        <span class="hide-menu">Kelola Jasa</span>
                    </a>
                </li>
                @endif

                @if($userAuth['role'] == 'super_admin' || $userAuth['role'] == 'admin')
                <li class="sidebar-item {{ $nav == 'pesanan' ? 'selected' : ''}}">
                    <a class="sidebar-link {{ $nav == 'pesanan' ? 'active' : ''}}" href="/pesanan"
                        aria-expanded="false">
                        <img src="{{ asset($tPath.'img/icon/sidebar/pesanan.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'pesanan' ? 'block' : 'none'}}" class="white">
                        <img src="{{ asset($tPath.'img/icon/sidebar/pesanan_dark.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'pesanan' ? 'none' : 'block'}}" class="dark">
                        <span class="hide-menu">Kelola Pesanan</span>
                    </a>
                </li>
                @endif

                <li class="sidebar-item {{ $nav == 'pembayaran' ? 'selected' : ''}}">
                    <a class="sidebar-link {{ $nav == 'pembayaran' ? 'active' : ''}}" href="/metode-pembayaran"
                        aria-expanded="false">
                        <img src="{{ asset($tPath.'img/icon/sidebar/metode-pembayaran.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'pembayaran' ? 'block' : 'none'}}" class="white">
                        <img src="{{ asset($tPath.'img/icon/sidebar/metode-pembayaran_dark.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'pembayaran' ? 'none' : 'block'}}" class="dark">
                        <span class="hide-menu">Kelola Pembayaran</span>
                    </a>
                </li>

                @if($userAuth['role'] == 'super_admin')
                <li class="sidebar-item {{ $nav == 'admin' ? 'selected' : ''}}">
                    <a class="sidebar-link {{ $nav == 'admin' ? 'active' : ''}}"" href=" /admin" aria-expanded="false">
                        <img src="{{ asset($tPath.'img/icon/sidebar/admin.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'admin' ? 'block' : 'none'}}" class="white">
                        <img src="{{ asset($tPath.'img/icon/sidebar/admin_dark.svg') }}" alt="" width="30" height="30" style="display: {{ $nav == 'admin' ? 'none' : 'block'}}" class="dark">
                        <span class="hide-menu">Kelola Admin</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<script>
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    sidebarItems.forEach(function(item){
        if(!item.classList.contains('selected')){
            item.addEventListener('click', function(){
                item.querySelector('.dark').style.display = 'none';
                item.querySelector('.white').style.display = 'block';
                sidebarItems.forEach(function(itemActive){
                    if(itemActive.classList.contains('selected')){
                        itemActive.querySelector('.dark').style.display = 'block';
                        itemActive.querySelector('.white').style.display = 'none';
                        itemActive.classList.remove('selected');
                    }
                });
                item.classList.add('selected');
            });
        }
    });
</script>