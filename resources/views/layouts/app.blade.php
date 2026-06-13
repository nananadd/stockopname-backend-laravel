@php
    $userRole = strtolower(auth()->user()->role->name ?? '');
    
    $dashboardLink = ($userRole === 'manager' || $userRole === 'owner')
        ? route('management.dashboard')
        : route('dashboard');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Sigma Berkat Sejati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sigma-black: #111111;       
            --sigma-magenta: #f31a6b;     
            
            --ambient-bg: #f8f9fa;
            --ambient-card-header: #ffffff;
            
            --variance-kurang: #dc3545; 
            --variance-sesuai: #28a745; 
            --variance-lebih: #17a2b8;  
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--ambient-bg);
            color: var(--sigma-black);
            overflow-x: hidden;
        }

        .text-primary { color: var(--sigma-magenta) !important; }
        .text-dark { color: var(--sigma-black) !important; }
        
        .bg-sigma-black { background-color: var(--sigma-black) !important; color: white; }
        .bg-sigma-magenta { background-color: var(--sigma-magenta) !important; color: white; }
        .border-sigma-magenta { border-bottom: 3px solid var(--sigma-magenta) !important; }
        
        .btn-primary { 
            background-color: var(--sigma-magenta); 
            border-color: var(--sigma-magenta);
            color: #ffffff;
            font-weight: bold;
        }
        .btn-primary:hover { 
            background-color: #c91256;
            border-color: #c91256;
            color: #ffffff;
        }

        .card {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .card-header {
            background-color: var(--ambient-card-header);
            border-bottom: 1px solid #eeeeee;
            padding: 1.25rem 1.5rem;
        }

        .sidebar {
            width: 280px;
            min-width: 280px;
            flex-shrink: 0;
            height: 100vh;          
            position: sticky;       
            top: 0;                 
            overflow-y: auto;       
            background-color: var(--sigma-black);
            transition: all 0.3s;
        }
        
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: var(--sigma-black); }
        .sidebar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: var(--sigma-magenta); }

        .sidebar .nav-link {
            color: #ccc;
            margin-bottom: 0.5rem;
            border-radius: 0.375rem;
            padding: 0.8rem 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link.active {
            background-color: var(--sigma-magenta);
            color: white;
            font-weight: bold;
        }

        .main-content {
            flex-grow: 1;
            min-height: 100vh;
        }

        .logout-btn {
            transition: all 0.2s ease-in-out;
        }

        .logout-btn:hover {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        .logout-btn:hover i {
            color: #ffffff !important;
        }

        .settings-hover {
            transition: all 0.2s ease-in-out;
            color: #333333;
        }

        .settings-hover:hover {
            background-color: rgba(243, 26, 107, 0.08) !important; 
            color: var(--sigma-magenta) !important;
        }
        
        .settings-hover:hover .setting-icon {
            color: var(--sigma-magenta) !important;
        }

        .settings-hover:hover .setting-icon {
            color: var(--sigma-magenta) !important;
        }

        [data-bs-theme="dark"] body {
            --ambient-bg: #121212;
            --ambient-card-header: #1e1e1e;
            color: #e0e0e0;
        }
        [data-bs-theme="dark"] .bg-white { background-color: #1e1e1e !important; }
        [data-bs-theme="dark"] .bg-light { background-color: #2d2d2d !important; }
        [data-bs-theme="dark"] .card { background-color: #1e1e1e; border: 1px solid #333 !important; }
        [data-bs-theme="dark"] .text-dark { color: #f8f9fa !important; }
        [data-bs-theme="dark"] .text-muted { color: #adb5bd !important; }
        [data-bs-theme="dark"] .border-bottom { border-color: #333 !important; }
        [data-bs-theme="dark"] .table { color: #e0e0e0; border-color: #444; }

        [data-bs-theme="dark"] .table-light, 
        [data-bs-theme="dark"] .table-light th, 
        [data-bs-theme="dark"] .table-light td {
            background-color: #2a2a2a !important;
            color: #f8f9fa !important;
            border-color: #444 !important;
        }

        [data-bs-theme="dark"] th, 
        [data-bs-theme="dark"] td {
            border-color: #444 !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #2d2d2d !important;
            border-color: #444 !important;
            color: #f8f9fa !important;
        }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #1e1e1e !important;
            color: #f8f9fa !important;
        }

        [data-bs-theme="dark"] .pagination .page-link {
            background-color: #1e1e1e;
            border-color: #444;
            color: var(--sigma-magenta);
        }
        [data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background-color: var(--sigma-magenta);
            border-color: var(--sigma-magenta);
            color: white;
        }
        [data-bs-theme="dark"] .pagination .page-link:hover {
            background-color: #2d2d2d;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1e1e1e !important;
            border-color: #444 !important;
        }
        
        [data-bs-theme="dark"] .dropdown-item,
        [data-bs-theme="dark"] .settings-hover {
            color: #f8f9fa !important; 
        }

        [data-bs-theme="dark"] .settings-hover .setting-icon {
            color: #adb5bd !important;
        }
    </style>
</head>
<body>
    
    <div class="d-flex flex-nowrap">
        
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column p-3 text-white border-end border-sigma-magenta">
            <a href="{{ $dashboardLink }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none w-100 pb-3 border-bottom border-secondary">
                <img src="{{ asset('images/LogoSigmaWhite520x129.webp') }}" 
                    alt="Sigma Logo"
                    class="me-3"
                    style="width: 500px; height: 40px; object-fit: contain;">
            </a>
            
            <ul class="nav nav-pills flex-column mb-auto mt-4">
                <li class="nav-item">
                    <a href="{{ $dashboardLink }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('management.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home me-2 fa-fw"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('racks.index') }}" class="nav-link {{ request()->routeIs('racks.*') ? 'active' : '' }}">
                        <i class="fas fa-pallet me-2 fa-fw"></i> Rak
                    </a>
                </li>
                <li>
                    <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <i class="fas fa-box-open me-2 fa-fw"></i> Item
                    </a>
                </li>
                <li>
                    <a href="{{ route('cycle.index') }}" class="nav-link {{ request()->routeIs('cycle.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check me-2 fa-fw"></i> Cycle Count
                    </a>
                </li>
                @if(auth()->user()->role_id == 4 || auth()->user()->role_id == 1 || auth()->user()->role_id == 3 || auth()->user()->role_id == 2)
                <li>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users me-2 fa-fw"></i> Manajemen Staf
                    </a>
                </li>
                @endif
            </ul>
            
            <hr class="border-secondary">
            
            <!-- User Menu / Logout -->
            <div class="dropdown mt-3">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle px-2 py-2 rounded transition-all border border-secondary border-opacity-25" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: rgba(255,255,255,0.03);">
                    
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 38px; height: 38px; background-color: var(--sigma-magenta);">
                        <span class="fw-bold text-white fs-5">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    
                    <div class="d-flex flex-column me-3">
                        <strong class="text-white lh-1 mb-1 text-truncate" style="max-width: 130px;">{{ auth()->user()->name ?? 'User' }}</strong>
                        <span class="text-white-50" style="font-size: 0.75rem;">{{ ucfirst(auth()->user()->role->name ?? 'Staff') }}</span>
                    </div>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" aria-labelledby="dropdownUser" style="min-width: 250px;">
                    
                    <li class="px-4 py-3 border-bottom mb-2 bg-light rounded-top">
                        <div class="fw-bold text-dark text-truncate">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-muted small text-truncate">{{ auth()->user()->email ?? 'user@sigmaberkat.com' }}</div>
                    </li>
                    
                        <li>
                        <a class="dropdown-item py-2 d-flex align-items-center fw-medium settings-hover" href="{{ route('settings.index') }}">
                            <i class="fas fa-user-cog me-3 setting-icon" style="color: #6c757d;"></i> Profil & Pengaturan
                        </a>
                    </li>
                    
                    <li><hr class="dropdown-divider my-2"></li>
                    
                    <li class="px-2 pb-2">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger fw-bold rounded d-flex align-items-center py-2 swal-confirm logout-btn" 
                                data-swal-title="Keluar Sistem?" 
                                data-swal-text="Sesi kerja Anda akan diakhiri.">
                                <i class="fas fa-sign-out-alt me-3"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="container-fluid p-4 p-md-5">

                @yield('content')
                
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Menangkap event 'submit' pada form
        document.addEventListener('submit', function (e) {
            let btn = e.submitter;
            
            if (btn && btn.classList.contains('swal-confirm')) {
                e.preventDefault();
                
                let title = btn.getAttribute('data-swal-title') || 'Apakah Anda yakin?';
                let text = btn.getAttribute('data-swal-text') || 'Data ini akan diproses.';
                let icon = btn.getAttribute('data-swal-icon') || 'warning';
                let confirmText = btn.getAttribute('data-swal-confirm') || 'Ya, Lanjutkan!';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#f31a6b',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.submit();
                    }
                });
            }
        });

        // Alert Sukses / Error bawaan Controller Laravel
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{!! session('success') !!}', timer: 2000, showConfirmButton: false });
        @endif

        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Oops...', text: '{!! session('error') !!}' });
        @endif

        // Penangkap Error Validasi dari Form
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: '{!! implode("<br>", $errors->all()) !!}'
            });
        @endif
    </script>

    <script>
        // Cek memori browser langsung saat halaman dimuat
        if (localStorage.getItem('sigma_theme') === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }

        // Fungsi untuk mengontrol tombol toggle di halaman Pengaturan
        document.addEventListener('DOMContentLoaded', function() {
            const darkToggle = document.getElementById('darkModeToggle');
            
            if (darkToggle) {
                if(localStorage.getItem('sigma_theme') === 'dark') {
                    darkToggle.checked = true;
                }

                darkToggle.addEventListener('change', function() {
                    if(this.checked) {
                        document.documentElement.setAttribute('data-bs-theme', 'dark');
                        localStorage.setItem('sigma_theme', 'dark');
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Mode Gelap Aktif', showConfirmButton: false, timer: 1500 });
                    } else {
                        document.documentElement.setAttribute('data-bs-theme', 'light');
                        localStorage.setItem('sigma_theme', 'light');
                        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Mode Terang Aktif', showConfirmButton: false, timer: 1500 });
                    }
                });
            }
        });
    </script>

</body>
</html>