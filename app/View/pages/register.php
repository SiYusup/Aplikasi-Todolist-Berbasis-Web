<div class="auth-wrap">
  <div class="uk-card uk-card-default uk-card-body auth-card" x-data="registerForm()">
    <div class="uk-text-center">
      <span class="auth-brand ic-lg"><i data-lucide="user-plus"></i></span>
      <h3 class="uk-card-title" style="margin: 0 0 4px;">Buat akun baru</h3>
      <p class="uk-text-meta" style="margin: 0 0 20px;">Gratis, cukup username & password</p>
    </div>
    <form @submit.prevent="submit()">
      <div class="uk-margin">
        <div class="uk-inline uk-width-1-1">
          <span class="uk-form-icon"><i data-lucide="user"></i></span>
          <input class="uk-input" x-model="form.username" placeholder="Username (min 3 karakter)" autocomplete="username" />
        </div>
      </div>
      <div class="uk-margin">
        <div class="uk-inline uk-width-1-1">
          <span class="uk-form-icon"><i data-lucide="lock"></i></span>
          <input class="uk-input" type="password" x-model="form.password" placeholder="Password (min 6 karakter)" autocomplete="new-password" />
        </div>
      </div>
      <button class="uk-button uk-button-primary uk-width-1-1" type="submit" :disabled="loading">
        <span x-text="loading ? 'Memproses...' : 'Daftar'"></span>
      </button>
    </form>
    <p class="uk-text-meta uk-text-center" style="margin: 18px 0 0;">Sudah punya akun? <a href="/login">Login</a></p>
  </div>
</div>
<script>
function registerForm() {
  return {
    loading: false,
    form: { username: '', password: '' },
    async submit() {
      this.loading = true;
      try {
        await axios.post('/api/auth/register', this.form);
        toast('Registrasi berhasil, silakan login', 'success');
        location.href = '/login';
      } catch (e) { apiError(e, 'Registrasi gagal'); }
      finally { this.loading = false; }
    }
  };
}
</script>
