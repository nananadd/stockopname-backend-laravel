@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-cog text-primary me-2"></i>Pengaturan Akun
        </h3>
        <p class="text-muted">Kelola keamanan akun Anda di sistem PT Sigma Berkat Sejati.</p>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-key text-warning me-2"></i>Ubah Password
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.password') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Password Saat Ini</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control border-start-0 @error('current_password') is-invalid @enderror" placeholder="Masukkan password lama" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control border-start-0 @error('new_password') is-invalid @enderror" placeholder="Minimal 6 karakter" required minlength="6">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-check text-muted"></i></span>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control border-start-0" placeholder="Ulangi password baru" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-medium rounded-pill py-2">
                                <i class="fas fa-save me-2"></i>Perbarui Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetSelector = this.getAttribute('data-target');
                const inputField = document.querySelector(targetSelector);
                const icon = this.querySelector('i');

                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    inputField.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>
@endsection