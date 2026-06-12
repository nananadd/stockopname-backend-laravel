@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">
        <i class="fas fa-users text-primary me-2"></i>Manajemen Staf Gudang
    </h3>
    @if(in_array(auth()->user()->role_id, [1, 2]))
    <button type="button" class="btn btn-primary fw-medium rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="fas fa-plus-circle me-2"></i>Tambah Staf Baru
    </button>
    @endif
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center py-3" width="5%">No</th>
                        <th class="py-3">Nama Staf</th>
                        <th class="py-3">Email</th>
                        <th class="text-center py-3">Status Hari Ini</th>
                        <th class="text-center py-3" width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffs as $index => $staff)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $staff->name }}</td>
                        <td class="text-muted">{{ $staff->email }}</td>
                        
                        <td class="text-center">
                            @if($staff->is_present)
                                <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">
                                    <i class="fas fa-check-circle me-1"></i> Hadir / Ready
                                </span>
                            @else
                                <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">
                                    <i class="fas fa-times-circle me-1"></i> Sedang Cuti
                                </span>
                            @endif
                        </td>
                        
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('users.toggle', $staff->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" title="Ubah Kehadiran" class="btn btn-sm {{ $staff->is_present ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill transition-all">
                                        <i class="fas {{ $staff->is_present ? 'fa-user-times' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>

                                @if(in_array(auth()->user()->role_id, [1, 2]))
                                <button type="button" title="Edit Staf" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $staff->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('users.destroy', $staff->id) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Staf" class="btn btn-sm btn-outline-danger rounded-pill transition-all swal-confirm" 
                                        data-swal-title="Hapus Akun Staf?" 
                                        data-swal-text="Riwayat opname staf ini tidak akan hilang dari log aktivitas.">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="editStaffModal{{ $staff->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit text-primary me-2"></i>Edit Data Staf</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('users.update', $staff->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Nama Lengkap</label>
                                            <input type="text" name="name" class="form-control" value="{{ $staff->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Alamat Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ $staff->email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Password Baru <small class="text-muted">(Opsional)</small></label>
                                            <div class="input-group">
                                                <input type="password" name="password" id="editPassword{{ $staff->id }}" class="form-control" placeholder="Kosongkan jika tidak mengubah password">
                                                
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#editPassword{{ $staff->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                        
                                        <button type="submit" class="btn btn-primary rounded-pill swal-confirm"
                                            data-swal-title="Simpan Perubahan?"
                                            data-swal-text="Pastikan data email dan nama staf sudah benar."
                                            data-swal-icon="info">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-1 mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold">Belum ada data staf gudang.</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Tambah Staf Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan nama staf" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@sigma.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="addPassword" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                            
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#addPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    
                    <button type="submit" class="btn btn-primary rounded-pill swal-confirm"
                        data-swal-title="Tambah Staf Baru?"
                        data-swal-text="Akun staf gudang baru akan dibuat di dalam sistem."
                        data-swal-icon="info">
                        Simpan Staf
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil semua tombol yang memiliki class 'toggle-password'
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');

        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Cari tahu input mana yang jadi target tombol ini
                const targetSelector = this.getAttribute('data-target');
                const passwordInput = document.querySelector(targetSelector);
                const icon = this.querySelector('i');

                // Jika tipenya password, ubah ke text (tampilkan)
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash'); // Ganti ikon mata dicoret
                } else {
                    // Jika tipenya text, kembalikan ke password (sembunyikan)
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye'); // Ganti ikon mata normal
                }
            });
        });
    });
</script>
@endsection