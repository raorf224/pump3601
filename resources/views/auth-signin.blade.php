@extends('partials.layouts.master_auth')

@section('title', 'Pump 360 - Sign In')

@section('css')
    <style>
        /* Orange filter for logo */
        .logo-orange-filter {
            filter: brightness(0) saturate(100%) invert(51%) sepia(93%) saturate(745%) hue-rotate(350deg) brightness(95%) contrast(92%);
            transition: all 0.3s ease;
        }

        .logo-orange-filter:hover {
            filter: brightness(0) saturate(100%) invert(51%) sepia(93%) saturate(845%) hue-rotate(350deg) brightness(105%) contrast(102%);
            transform: scale(1.05);
        }

        /* Orange border for inputs */
        .border-orange {
            border: 1px solid #fdba74 !important;
            transition: all 0.3s ease;
        }

        .border-orange:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.25) !important;
        }

        /* Orange gradient button */
        .orange-gradient-btn {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            transition: all 0.3s ease !important;
        }

        .orange-gradient-btn:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
        }

        .orange-gradient-btn:active {
            transform: translateY(0);
        }

        /* Light orange background for alerts */
        .bg-orange-light {
            background-color: rgba(254, 215, 170, 0.2) !important;
            border-left: 4px solid #f97316 !important;
        }

        /* Card styling */
        .card {
            border-radius: 12px !important;
            overflow: hidden !important;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #f97316 0%, #fdba74 100%);
        }
    </style>

@endsection

@section('content')

    <!-- START -->
    <div>
        <img src="{{ asset('assets/images/auth/login_bg.jpg') }}" alt="Auth Background"
            class="auth-bg light w-full h-full opacity-60 position-absolute top-0">
        <img src="{{ asset('assets/images/auth/auth_bg_dark.jpg') }}" alt="Auth Background" class="auth-bg d-none dark">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100 py-10">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card mx-xxl-8 border-0 shadow-lg">
                        <div class="card-body py-12 px-8">
                            <!-- Orange-themed Logo with Filter -->
                            <div class="mb-4 mx-auto d-block text-center">
                                <img src="{{ asset('https://pump360.pk/wp/wp-content/uploads/2025/01/logo-sized-1.png') }}"
                                    alt="Logo" height="40" class="logo-orange-filter">
                            </div>

                            <h6 class="mb-3 mb-8 fw-semibold text-center" style="color: #f97316;">Login to Your Account</h6>

                            {{-- ✅ Laravel Login Form --}}
                            <form method="POST" action="{{ url('/login') }}">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label for="username" class="form-label fw-medium" style="color: #ea580c;">
                                            Username or Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control border-orange" id="username" name="login"
                                            value="{{ old('login') }}" placeholder="Enter your username or email" required
                                            autofocus>
                                        @error('login')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="password" class="form-label fw-medium" style="color: #ea580c;">
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" class="form-control border-orange" id="password"
                                            name="password" placeholder="Enter your password" required>
                                        @error('password')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12 mt-8">
                                        <button type="submit" class="btn btn-primary w-100 mb-4 orange-gradient-btn">
                                            Sign In <i class="bi bi-box-arrow-in-right ms-1 fs-16"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Show global login error --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger mt-3 border-orange bg-orange-light">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        {{ $errors->first() }}
                                    </div>
                                @endif
                            </form>

                        </div>
                    </div>
                    <p class="position-relative text-center fs-12 mb-0 mt-4" style="color: #090706ff;">
                        © 2025. Crafted with <span style="color: #090706ff;">❤️</span> by P2P Track
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
@endsection