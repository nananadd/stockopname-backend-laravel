@php
$role = auth()->user()->role->name ?? '';
$dashboardRoute = $role === 'manager'
    ? route('manager.dashboard')
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
            /* Warna asli dari website Sigma */
            --sigma-black: #111111;       
            --sigma-magenta: #f31a6b;     
            
            /* Warna latar (Ambient) */
            --ambient-bg: #f8f9fa;
            --ambient-card-header: #ffffff;
            
            /* Warna indikator tabel selisih */
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

        /* Override Bootstrap utilities */
        .text-primary { color: var(--sigma-magenta) !important; }
        .text-dark { color: var(--sigma-black) !important; }
        
        .bg-sigma-black { background-color: var(--sigma-black) !important; color: white; }
        .bg-sigma-magenta { background-color: var(--sigma-magenta) !important; color: white; }
        .border-sigma-magenta { border-bottom: 3px solid var(--sigma-magenta) !important; }
        
        /* Tombol Utama jadi warna Magenta */
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

        /* Styling Kartu */
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

        /* --- SIDEBAR STYLING --- */
        .sidebar {
            width: 280px;
            height: 100vh;          /* Tinggi full 1 layar */
            position: sticky;       /* Agar diam tidak ikut discroll */
            top: 0;                 /* Nempel di atas */
            overflow-y: auto;       /* Jika isi menu panjang, hanya area menu yang bisa di scroll */
            background-color: var(--sigma-black);
            transition: all 0.3s;
        }
        
        /* Modifikasi scrollbar pada sidebar agar lebih rapi (opsional) */
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
    </style>
</head>
<body>
    
    <div class="d-flex flex-nowrap">
        
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column p-3 text-white border-end border-sigma-magenta">
            <a href="{{ $dashboardRoute }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none w-100 pb-3 border-bottom border-secondary">
                <img src="{{ asset('images/LogoSigmaWhite520x129.webp') }}" 
                    alt="Sigma Logo"
                    class="me-3"
                    style="width: 500px; height: 40px; object-fit: contain;">
            </a>
            
            <ul class="nav nav-pills flex-column mb-auto mt-4">
                <li class="nav-item">
                    <a href="{{ $dashboardRoute }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('manager.dashboard') ? 'active' : '' }}">
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
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.index') ? 'active fw-bold' : '' }}" href="{{ route('settings.index') }}">
                        <i class="fas fa-cog me-2 text-secondary"></i>
                        <span>Pengaturan Akun</span>
                    </a>
                </li>
            </ul>
            
            <hr class="border-secondary">
            
            <!-- User Menu / Logout -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2 fs-4"></i>
                    <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger fw-bold">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="container-fluid p-4 p-md-5">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Yield Content -->
                @yield('content')
                
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>