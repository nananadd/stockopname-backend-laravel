@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-primary">
                <i class="fas fa-cog me-2"></i>Pengaturan Akun
            </h3>
            <p class="text-muted mb-0">Kelola profil, keamanan, dan preferensi tampilan Anda.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-id-badge text-primary me-2"></i>Profil Pengguna</h6>
                </div>
                <div class="card-body text-center p-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 100px; height: 100px; background-color: var(--sigma-magenta);">
                        <span class="fw-bold text-white display-4">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    
                    <h5 class="fw-bold mb-1 text-dark">{{ auth()->user()->name ?? 'Nama Pengguna' }}</h5>
                    <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 shadow-sm">
                        {{ strtoupper(auth()->user()->role->name ?? 'STAFF') }}
                    </span>
                    
                    <ul class="list-group list-group-flush text-start mt-3">
                        <li class="list-group-item px-0 py-3 bg-transparent">
                            <i class="fas fa-envelope text-muted me-3 w-15px"></i> 
                            <span class="fw-medium text-dark">{{ auth()->user()->email ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 py-3 bg-transparent">
                            <i class="fas fa-calendar-check text-muted me-3 w-15px"></i> 
                            <span class="text-muted small me-1">Bergabung:</span>
                            <span class="fw-medium text-dark">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d F Y') : '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 py-3 bg-transparent">
                            <i class="fas fa-network-wired text-muted me-3 w-15px"></i> 
                            <span class="text-muted small me-1">Alamat IP:</span>
                            <span class="fw-medium text-dark">{{ request()->ip() }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-moon text-primary me-2"></i>Tema Layar</h6>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-4">Pengaturan tema ini disimpan secara lokal di browser Anda.</p>
                    
                    <div class="form-check form-switch mb-3 d-flex align-items-center">
                        <input class="form-check-input fs-5 mt-0 me-3 shadow-sm" type="checkbox" id="darkModeToggle">
                        <label class="form-check-label fw-medium text-dark" for="darkModeToggle" style="cursor: pointer;">
                            Mode Gelap (Dark Mode)
                            <span class="d-block text-muted small fw-normal mt-1">Mengubah warna latar menjadi gelap agar lebih nyaman di mata</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-shield-alt text-primary me-2"></i>Keamanan Sandi</h6>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <div class="alert bg-light border text-muted small d-flex mb-4 p-3">
                        <i class="fas fa-info-circle text-primary fs-5 me-3"></i>
                        <div>Demi keamanan pangkalan data, pastikan kata sandi baru Anda terdiri dari kombinasi yang kuat dan tidak dibagikan kepada staf lain.</div>
                    </div>

                    <form action="{{ route('settings.password') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Kata Sandi Saat Ini</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-unlock text-muted"></i></span>
                                <input type="password" id="current_password" name="current_password" class="form-control border-start-0 border-end-0 ps-0" required placeholder="Masukkan kata sandi lama...">
                                <span class="input-group-text bg-light toggle-password" data-target="current_password" style="cursor: pointer;" title="Tampilkan/Sembunyikan Sandi">
                                    <i class="fas fa-eye text-muted"></i>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-4 g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Kata Sandi Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                                    <input type="password" id="new_password" name="password" class="form-control border-start-0 border-end-0 ps-0" required placeholder="Sandi baru...">
                                    <span class="input-group-text bg-light toggle-password" data-target="new_password" style="cursor: pointer;" title="Tampilkan/Sembunyikan Sandi">
                                        <i class="fas fa-eye text-muted"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Konfirmasi Sandi Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-double text-muted"></i></span>
                                    <input type="password" id="confirm_password" name="password_confirmation" class="form-control border-start-0 border-end-0 ps-0" required placeholder="Ketik ulang sandi baru...">
                                    <span class="input-group-text bg-light toggle-password" data-target="confirm_password" style="cursor: pointer;" title="Tampilkan/Sembunyikan Sandi">
                                        <i class="fas fa-eye text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm swal-confirm" 
                                data-swal-title="Perbarui Kata Sandi?" 
                                data-swal-text="Anda akan menggunakan kata sandi ini pada login berikutnya." 
                                data-swal-icon="warning"
                                data-swal-confirm="Ya, Simpan Sandi Baru">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Cek memori browser langsung saat halaman dimuat
    if (localStorage.getItem('sigma_theme') === 'dark') {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    }

    // 2. Fungsi untuk mengontrol tombol toggle di halaman Pengaturan
    document.addEventListener('DOMContentLoaded', function() {
        const darkToggle = document.getElementById('darkModeToggle');
        
        if (darkToggle) {
            // Sesuaikan posisi centang tombol (On/Off) saat halaman pengaturan dibuka
            if(localStorage.getItem('sigma_theme') === 'dark') {
                darkToggle.checked = true;
            }

            // Aksi saat tombol digeser
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil semua elemen dengan class 'toggle-password'
        const togglePasswords = document.querySelectorAll('.toggle-password');
        
        togglePasswords.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                // Cari input yang terhubung dengan tombol ini melalui data-target
                const targetId = this.getAttribute('data-target');
                const inputField = document.getElementById(targetId);
                const icon = this.querySelector('i');

                // Jika tipenya password, ubah ke text (terlihat)
                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash'); // Ganti ikon jadi mata dicoret
                } else {
                    // Jika teks, kembalikan menjadi password (tersembunyi)
                    inputField.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye'); // Ganti kembali ke ikon mata biasa
                }
            });
        });
    });
</script>

<style>
    /* Sedikit CSS untuk mempercantik ukuran ikon */
    .w-15px { width: 15px; text-align: center; }
    
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
</style>
@endsection