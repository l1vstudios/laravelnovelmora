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

          {{-- Logo --}}
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <img src="{{ asset('assets/img/bacaanlogo2.png') }}" alt="Logo" style="width: 180px; height: auto;">
            </a>
          </div>
          {{-- /Logo --}}

          {{-- <h4 class="mb-1 pt-2">
            Selamat Datang!
          </h4>

          <p class="mb-6 text-muted">
            Masuk ke akun admin Anda untuk melanjutkan
          </p> --}}

          {{-- Global Error --}}
          @if ($errors->any())
            <div
              class="alert alert-danger alert-dismissible fade show mb-5"
              role="alert"
            >
              {{ $errors->first() }}

              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
              ></button>
            </div>
          @endif

          {{-- Login Form --}}
          <form
            id="loginForm"
            class="mb-6"
            action="{{ route('login.post') }}"
            method="POST"
          >
            @csrf

            {{-- Email --}}
            <div class="mb-6">
              <label
                for="email"
                class="form-label"
              >
                Email
              </label>

              <input
                type="email"
                id="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="admin@novelmora.com"
                autocomplete="email"
                autofocus
                required
              />

              @error('email')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror
            </div>

            {{-- Password --}}
            <div class="mb-6 form-password-toggle">
              <label
                class="form-label"
                for="password"
              >
                Password
              </label>

              <div class="input-group input-group-merge">

                <input
                  type="password"
                  id="password"
                  name="password"
                  class="form-control @error('password') is-invalid @enderror"
                  placeholder="············"
                  autocomplete="current-password"
                  required
                />

                <span
                  class="input-group-text cursor-pointer"
                  id="togglePassword"
                  role="button"
                >
                  <i
                    id="passwordIcon"
                    class="icon-base bx bx-hide"
                  ></i>
                </span>

              </div>

              @error('password')
                <div class="invalid-feedback d-block">
                  {{ $message }}
                </div>
              @enderror
            </div>

            {{-- Remember Me --}}
            <div class="mb-6">
              <div class="form-check ms-2">

                <input
                  class="form-check-input"
                  type="checkbox"
                  name="remember"
                  id="remember"
                  value="1"
                  {{ old('remember') ? 'checked' : '' }}
                />

                <label
                  class="form-check-label"
                  for="remember"
                >
                  Ingat saya
                </label>

              </div>
            </div>

            {{-- Cloudflare Turnstile --}}
            <div class="mb-6">

              <div
                class="cf-turnstile"
                data-sitekey="0x4AAAAAAD-wz78qJNys6wGL"
                data-theme="light"
                data-size="normal"
                data-callback="onTurnstileSuccess"
                data-expired-callback="onTurnstileExpired"
                data-error-callback="onTurnstileError"
              ></div>

              @error('cf-turnstile-response')
                <div class="text-danger small mt-2">
                  {{ $message }}
                </div>
              @enderror

              <div
                id="turnstileError"
                class="text-danger small mt-2 d-none"
              >
                Silakan verifikasi keamanan terlebih dahulu.
              </div>

            </div>

            {{-- Submit --}}
            <button
              id="loginButton"
              class="btn btn-primary d-grid w-100"
              type="submit"
            >
              <span id="loginButtonText">
                Masuk
              </span>

              <span
                id="loginButtonLoading"
                class="d-none"
              >
                <span
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>

                Memproses...
              </span>
            </button>

          </form>
          {{-- /Login Form --}}

        </div>
      </div>

    </div>
  </div>

  {{-- Cloudflare Turnstile --}}
  <script
    src="https://challenges.cloudflare.com/turnstile/v0/api.js"
    async
    defer
  ></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {

      const form = document.getElementById('loginForm');

      const loginButton = document.getElementById('loginButton');
      const loginButtonText = document.getElementById('loginButtonText');
      const loginButtonLoading = document.getElementById('loginButtonLoading');

      const turnstileError = document.getElementById('turnstileError');

      const passwordInput = document.getElementById('password');
      const togglePassword = document.getElementById('togglePassword');
      const passwordIcon = document.getElementById('passwordIcon');

      /*
       |--------------------------------------------------------------------------
       | Toggle Password
       |--------------------------------------------------------------------------
       */
      if (togglePassword && passwordInput) {

        togglePassword.addEventListener('click', function () {

          const isPassword =
            passwordInput.getAttribute('type') === 'password';

          passwordInput.setAttribute(
            'type',
            isPassword ? 'text' : 'password'
          );

          if (passwordIcon) {

            passwordIcon.classList.toggle(
              'bx-hide',
              !isPassword
            );

            passwordIcon.classList.toggle(
              'bx-show',
              isPassword
            );

          }

        });

      }

      /*
       |--------------------------------------------------------------------------
       | Form Submit
       |--------------------------------------------------------------------------
       */
      if (form) {

        form.addEventListener('submit', function (event) {

          const turnstileResponse = document.querySelector(
            '[name="cf-turnstile-response"]'
          );

          /*
           * Pastikan Turnstile sudah berhasil
           */
          if (
            !turnstileResponse ||
            !turnstileResponse.value
          ) {

            event.preventDefault();

            if (turnstileError) {
              turnstileError.classList.remove('d-none');
            }

            return false;
          }

          /*
           * Disable tombol agar tidak double submit
           */
          if (loginButton) {

            loginButton.disabled = true;

            loginButtonText.classList.add('d-none');
            loginButtonLoading.classList.remove('d-none');

          }

        });

      }

    });


    /*
     |--------------------------------------------------------------------------
     | Turnstile Callback
     |--------------------------------------------------------------------------
     */

    function onTurnstileSuccess(token) {

      const turnstileError =
        document.getElementById('turnstileError');

      if (turnstileError) {
        turnstileError.classList.add('d-none');
      }

    }


    function onTurnstileExpired() {

      const turnstileError =
        document.getElementById('turnstileError');

      if (turnstileError) {

        turnstileError.textContent =
          'Verifikasi keamanan telah kedaluwarsa. Silakan verifikasi kembali.';

        turnstileError.classList.remove('d-none');

      }

    }


    function onTurnstileError() {

      const turnstileError =
        document.getElementById('turnstileError');

      if (turnstileError) {

        turnstileError.textContent =
          'Verifikasi keamanan gagal dimuat. Silakan coba kembali.';

        turnstileError.classList.remove('d-none');

      }

    }
  </script>
@endsection
