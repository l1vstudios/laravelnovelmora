@extends('layouts/blankLayout')

@section('title', 'Login - NovelMora')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">

      <div class="card">
        <div class="card-body">

          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-text demo fw-bold fs-4">BACAAN</span>
            </a>
          </div>
          <!-- /Logo -->

          <h4 class="mb-1 pt-2">Selamat Datang!</h4>
          <p class="mb-6 text-muted">Masuk ke akun admin Anda untuk melanjutkan</p>

          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible mb-5" role="alert">
              {{ $errors->first() }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <form class="mb-6" action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="mb-6">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email') }}" placeholder="admin@novelmora.com" autofocus />
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" name="password"
                  class="form-control @error('password') is-invalid @enderror" placeholder="············" />
                <span class="input-group-text cursor-pointer">
                  <i class="icon-base bx bx-hide"></i>
                </span>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-8">
              <div class="form-check ms-2">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" />
                <label class="form-check-label" for="remember">Ingat saya</label>
              </div>
            </div>

            <button class="btn btn-primary d-grid w-100" type="submit">Masuk</button>
          </form>

        </div>
      </div>

    </div>
  </div>
@endsection
