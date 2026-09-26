<div class="auth-wrap">
  <div class="uk-card uk-card-default uk-card-body auth-card" x-data="loginForm()">
    <div class="uk-text-center">
      <span class="auth-brand ic-lg"><i data-lucide="list-checks"></i></span>
      <h3 class="uk-card-title" style="margin: 0 0 4px;">Selamat datang kembali</h3>
      <p class="uk-text-meta" style="margin: 0 0 20px;">Masuk untuk mengelola tugas Anda</p>
    </div>
    <form @submit.prevent="submit()">
      <div class="uk-margin">
        <div class="uk-inline uk-width-1-1">
          <span class="uk-form-icon"><i data-lucide="user"></i></span>
          <input class="uk-input" x-model="form.username" placeholder="Username" autocomplete="username" />
        </div>
      </div>
      <div class="uk-margin">
        <div class="uk-inline uk-width-1-1">
          <span class="uk-form-icon"><i data-lucide="lock"></i></span>
          <input class="uk-input" type="password" x-model="form.password" placeholder="Password" autocomplete="current-password" />
        </div>
      </div>
      <div class="uk-margin">
        <label class="uk-text-small"><input class="uk-checkbox" type="checkbox" x-model="form.remember_me" /> Ingat saya</label>
      </div>
      <button class="uk-button uk-button-primary uk-width-1-1" type="submit" :disabled="loading">
        <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
      </button>
    </form>
    <p class="uk-text-meta uk-text-center" style="margin: 18px 0 0;">Belum punya akun? <a href="/register">Registrasi</a></p>
  </div>
</div>
<script>
function loginForm() {
  return {
    loading: false,
    form: { username: '', password: '', remember_me: false },
    async submit() {
      this.loading = true;
      try {
        await axios.post('/api/auth/login', this.form);
        location.href = '/';
      } catch (e) { apiError(e, 'Login gagal'); }
      finally { this.loading = false; }
    }
  };
}
</script>
