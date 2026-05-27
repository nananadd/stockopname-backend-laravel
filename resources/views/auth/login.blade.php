<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WMS PT Sigma Berkat Sejati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sigma-black: #111111;
            --sigma-magenta: #f31a6b;
            --sigma-magenta-hover: #d11559;
        }
        
        body {
            background-color: #f4f6f9;
            /* Memberikan efek gradient radial halus di background */
            background-image: radial-gradient(circle at center, #ffffff 0%, #f4f6f9 100%);
        }

        .card-login {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .login-header {
            background-color: var(--sigma-black);
            padding: 2.5rem 2rem;
            /* Garis bawah tebal khas Sigma */
            border-bottom: 5px solid var(--sigma-magenta); 
        }

        .btn-sigma {
            background-color: var(--sigma-magenta);
            color: white;
            font-weight: 600;
            padding: 0.8rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-sigma:hover {
            background-color: var(--sigma-magenta-hover);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 26, 107, 0.3);
        }

        /* Styling agar Ikon menyatu dengan Input Text */
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: #6c757d;
        }

        .form-control {
            border-left: none;
            background-color: #f8f9fa;
            padding: 0.8rem 1rem;
        }

        .form-control:focus {
            box-shadow: none;
            background-color: #ffffff;
        }

        /* Efek nyala magenta saat form diklik */
        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: var(--sigma-magenta);
            background-color: #ffffff;
        }
        
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(243, 26, 107, 0.15);
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="card card-login">
                    <div class="login-header text-center text-white">
                        <div class="mb-3">
                            <img 
                                src="{{ asset('images/LogoSigmaWhite520x129.webp') }}" 
                                alt="Logo Sigma"
                                width="180"
                                class="img-fluid"
                            >
                        </div>
                        <h4 class="fw-bold mb-1">Stock Opname System</h4>
                        <p class="text-white-50 small mb-0">PT Sigma Berkat Sejati</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <h5 class="text-center fw-bold mb-4 text-dark">Masuk ke Akun Anda</h5>

                        @if($errors->any())
                            <div class="alert alert-danger rounded-3 p-3 text-sm">
                                <div class="d-flex align-items-center mb-2 fw-bold">
                                    <i class="fas fa-exclamation-circle me-2"></i> Gagal Login
                                </div>
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf 
                            
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold text-secondary small text-uppercase">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" required autofocus placeholder="nama@sigmastationery.com">
                                </div>
                            </div>
                            
                            <div class="mb-5">
                                <label for="password" class="form-label fw-semibold text-secondary small text-uppercase">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" id="password" required placeholder="Masukkan kata sandi">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-sigma w-100 d-flex justify-content-center align-items-center">
                                <i class="fas fa-sign-in-alt me-2"></i> Akses Sistem
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted small">
                    &copy; {{ date('Y') }} PT Sigma Berkat Sejati.<br>Stock Opname System.
                </div>

            </div>
        </div>
    </div>
</body>
</html>