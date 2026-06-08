@extends('layouts/contentNavbarLayout')
@section('title', 'Profil Saya')
@section('content')
  <div class="row justify-content-center">
    <div class="col-md-8">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible mb-6" role="alert">
          {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible mb-6" role="alert">
          <ul class="mb-0">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="mb-0">Informasi Akun</h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-4 pb-5 mb-5 border-bottom">
            <div class="avatar avatar-xl flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-primary" style="font-size:2rem;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
              </span>
            </div>
            <div>
              <h5 class="mb-1">{{ $user->name }}</h5>
              <small class="text-muted">{{ $user->email }}</small><br>
            </div>
          </div>
          <form action="{{ route('profile.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-5">
              <div class="col-md-6">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                  value="{{ old('name', $user->name) }}" placeholder="Nama lengkap">
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                  value="{{ old('email', $user->email) }}" placeholder="email@example.com">
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <hr class="my-2">
                <p class="text-muted mb-4"><small>Kosongkan password jika tidak ingin mengubahnya.</small></p>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password Baru</label>
                <div class="input-group input-group-merge">
                  <input type="password" name="password" id="new-password"
                    class="form-control @error('password') is-invalid @enderror" placeholder="Min. 6 karakter">
                  <span class="input-group-text cursor-pointer" onclick="togglePwd('new-password', this)">
                    <i class="icon-base bx bx-hide"></i>
                  </span>
                </div>
                @error('password')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" name="password_confirmation" id="confirm-password" class="form-control"
                    placeholder="Ulangi password baru">
                  <span class="input-group-text cursor-pointer" onclick="togglePwd('confirm-password', this)">
                    <i class="icon-base bx bx-hide"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="mt-6 d-flex gap-3">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base bx bx-save me-1"></i> Simpan Perubahan
              </button>
              <a href="{{ url('/') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Hapus Akun</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-warning mb-5">
            <strong>Perhatian!</strong> Menghapus akun bersifat permanen. Semua data akun Anda akan hilang.
          </div>
          <form action="{{ route('profile.destroy') }}" method="POST"
            data-confirm="Yakin ingin menghapus akun ini secara permanen? Tindakan ini tidak bisa dibatalkan.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">
              <i class="icon-base bx bx-trash me-1"></i> Hapus Akun Saya
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    function togglePwd(id, el) {
      const input = document.getElementById(id);
      const icon = el.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bx-hide', 'bx-show');
      } else {
        input.type = 'password';
        icon.classList.replace('bx-show', 'bx-hide');
      }
    }
  </script>
@endsection
