<div x-data="settingsPage()" x-init="load()">
  <div class="app-page-head">
    <h2 x-text="t('settings.title')">Pengaturan</h2>
    <p class="uk-text-meta" x-text="t('settings.sub')">Profil, tampilan, bahasa, dan data Anda.</p>
  </div>

  <!-- PROFIL -->
  <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
    <h4 style="margin: 0 0 14px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="circle-user-round"></i><span x-text="t('settings.profile')">Profil Saya</span></h4>
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
      <span class="auth-brand ic-lg" style="margin: 0; width: 52px; height: 52px; font-size: 20px; font-weight: 700;" x-text="(me.username || '?').charAt(0).toUpperCase()">?</span>
      <div>
        <div style="font-weight: 700; font-size: 18px;" x-text="me.username || '-'"></div>
        <div class="uk-text-meta uk-text-small"><span x-text="t('settings.status')">Status</span>: <span x-text="me.status || '-'"></span> · <span x-text="t('settings.joined')">Bergabung sejak</span>: <span x-text="(me.created_at || '').slice(0, 10) || '-'"></span></div>
      </div>
    </div>
    <div class="uk-grid uk-grid-small uk-child-width-1-3@m uk-margin-bottom" uk-grid>
      <div><div class="uk-text-center" style="background: #f4f4f5; border-radius: 10px; padding: 12px;"><div style="font-weight: 700; font-size: 20px;" x-text="stats.total ?? '-'"></div><div class="uk-text-meta uk-text-small" x-text="t('settings.total')">Total</div></div></div>
      <div><div class="uk-text-center" style="background: #fef3c7; border-radius: 10px; padding: 12px;"><div style="font-weight: 700; font-size: 20px;" x-text="stats.pending ?? '-'"></div><div class="uk-text-meta uk-text-small" x-text="t('settings.pending')">Pending</div></div></div>
      <div><div class="uk-text-center" style="background: #dcfce7; border-radius: 10px; padding: 12px;"><div style="font-weight: 700; font-size: 20px;" x-text="stats.completed ?? '-'"></div><div class="uk-text-meta uk-text-small" x-text="t('settings.done')">Selesai</div></div></div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <button class="uk-button uk-button-default uk-button-small" @click="showUname = !showUname; showPwd = false"><i data-lucide="user-pen"></i> <span x-text="t('settings.editProfile')">Ubah Profile</span></button>
      <button class="uk-button uk-button-default uk-button-small" @click="showPwd = !showPwd; showUname = false"><i data-lucide="key-round"></i> <span x-text="t('settings.editPassword')">Ubah Password</span></button>
    </div>
    <div x-show="showUname" x-cloak class="uk-margin-top" style="border-top: 1px solid #f4f4f5; padding-top: 16px;">
      <div class="uk-margin">
        <label class="uk-form-label" x-text="t('settings.newUsername')">Username baru</label>
        <input class="uk-input" x-model="uname.new_username" />
      </div>
      <div class="uk-margin">
        <label class="uk-form-label" x-text="t('settings.currentPassword')">Password saat ini</label>
        <input class="uk-input" type="password" x-model="uname.current_password" />
      </div>
      <button class="uk-button uk-button-primary uk-button-small" @click="saveUsername()" x-text="t('common.save')">Simpan</button>
    </div>
    <div x-show="showPwd" x-cloak class="uk-margin-top" style="border-top: 1px solid #f4f4f5; padding-top: 16px;">
      <div class="uk-margin">
        <label class="uk-form-label" x-text="t('settings.oldPassword')">Password lama</label>
        <input class="uk-input" type="password" x-model="pwd.old_password" />
      </div>
      <div class="uk-margin">
        <label class="uk-form-label" x-text="t('settings.newPassword')">Password baru (min 6)</label>
        <input class="uk-input" type="password" x-model="pwd.new_password" />
      </div>
      <button class="uk-button uk-button-primary uk-button-small" @click="savePassword()" x-text="t('common.save')">Simpan</button>
    </div>
  </div>

  <div class="uk-grid uk-grid-small uk-child-width-1-2@m" uk-grid>
    <!-- TAMPILAN -->
    <div>
      <div class="uk-card uk-card-default uk-card-body">
        <h4 style="margin: 0 0 4px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="palette"></i><span x-text="t('settings.appearance')">Tampilan</span></h4>
        <p class="uk-text-meta uk-text-small" style="margin: 0 0 14px;" x-text="t('settings.appearanceSub')">Pilih mode terang atau gelap.</p>
        <div style="display: flex; gap: 8px;">
          <button class="uk-button uk-button-small" :class="$store.prefs.theme !== 'dark' ? 'uk-button-primary' : 'uk-button-default'" @click="setTheme('light')"><i data-lucide="sun"></i> <span x-text="t('settings.light')">Terang</span></button>
          <button class="uk-button uk-button-small" :class="$store.prefs.theme === 'dark' ? 'uk-button-primary' : 'uk-button-default'" @click="setTheme('dark')"><i data-lucide="moon"></i> <span x-text="t('settings.dark')">Gelap</span></button>
        </div>
      </div>
    </div>
    <!-- BAHASA -->
    <div>
      <div class="uk-card uk-card-default uk-card-body">
        <h4 style="margin: 0 0 4px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="languages"></i><span x-text="t('settings.language')">Bahasa</span></h4>
        <p class="uk-text-meta uk-text-small" style="margin: 0 0 14px;" x-text="t('settings.languageSub')">Bahasa antarmuka aplikasi.</p>
        <div style="display: flex; gap: 8px;">
          <button class="uk-button uk-button-small" :class="$store.prefs.lang === 'id' ? 'uk-button-primary' : 'uk-button-default'" @click="setLang('id')">Indonesia</button>
          <button class="uk-button uk-button-small" :class="$store.prefs.lang === 'en' ? 'uk-button-primary' : 'uk-button-default'" @click="setLang('en')">English</button>
        </div>
      </div>
    </div>
    <!-- PENGINGAT DEFAULT -->
    <div>
      <div class="uk-card uk-card-default uk-card-body">
        <h4 style="margin: 0 0 4px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="bell"></i><span x-text="t('settings.reminder')">Pengingat Default</span></h4>
        <p class="uk-text-meta uk-text-small" style="margin: 0 0 14px;" x-text="t('settings.reminderSub')">Menit sebelum jatuh tempo untuk tugas baru.</p>
        <div style="display: flex; gap: 8px;">
          <input class="uk-input" type="number" min="0" x-model.number="defaultReminder" style="max-width: 140px;" />
          <button class="uk-button uk-button-primary" @click="saveReminder()" x-text="t('settings.reminderSave')">Simpan preferensi</button>
        </div>
      </div>
    </div>
    <!-- DATA -->
    <div>
      <div class="uk-card uk-card-default uk-card-body">
        <h4 style="margin: 0 0 4px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="database"></i><span x-text="t('settings.data')">Data</span></h4>
        <p class="uk-text-meta uk-text-small" style="margin: 0 0 14px;" x-text="t('settings.dataSub')">Ekspor atau bersihkan data tugas Anda.</p>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <button class="uk-button uk-button-default uk-button-small" @click="exportData()"><i data-lucide="download"></i> <span x-text="t('settings.export')">Ekspor JSON</span></button>
          <button x-show="!confirmClear" class="uk-button uk-button-danger uk-button-small" @click="confirmClear = true"><i data-lucide="trash-2"></i> <span x-text="t('settings.clearDone')">Hapus semua yang selesai</span></button>
          <button x-show="confirmClear" class="uk-button uk-button-danger uk-button-small" @click="clearCompleted()" x-text="t('common.yes') + ', hapus'">Ya, hapus</button>
          <button x-show="confirmClear" class="uk-button uk-button-default uk-button-small" @click="confirmClear = false" x-text="t('common.cancel')">Batal</button>
        </div>
        <p x-show="confirmClear" class="uk-text-small" style="color: #b91c1c; margin: 10px 0 0;" x-text="t('settings.clearConfirm')"></p>
      </div>
    </div>
  </div>

  <!-- KELUAR -->
  <div class="uk-card uk-card-default uk-card-body uk-margin-top">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
      <div>
        <h4 style="margin: 0 0 4px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="log-out"></i><span x-text="t('settings.logout')">Keluar</span></h4>
        <p class="uk-text-meta uk-text-small" style="margin: 0;" x-text="t('settings.logoutSub')">Akhiri sesi di perangkat ini.</p>
      </div>
      <button class="uk-button uk-button-danger" @click="$refs.logoutDialog2.showModal()" x-text="t('settings.logout')">Keluar</button>
    </div>
  </div>

  <dialog class="app-dialog" x-ref="logoutDialog2">
    <div class="uk-card-body" style="padding: 26px;">
      <h4 style="margin: 0 0 6px;" x-text="t('settings.logoutTitle')">Keluar dari aplikasi?</h4>
      <p class="uk-text-meta" style="margin: 0 0 20px;" x-text="t('settings.logoutMsg')">Anda harus login kembali.</p>
      <div style="display: flex; gap: 8px; justify-content: flex-end;">
        <button class="uk-button uk-button-default" @click="$refs.logoutDialog2.close()" x-text="t('common.cancel')">Batal</button>
        <button class="uk-button uk-button-danger" @click="logout()" x-text="t('settings.logout')">Keluar</button>
      </div>
    </div>
  </dialog>
</div>
<script>
function settingsPage() {
  return {
    me: {}, stats: {},
    showUname: false, showPwd: false, confirmClear: false,
    uname: { new_username: '', current_password: '' },
    pwd: { old_password: '', new_password: '' },
    defaultReminder: parseInt(localStorage.getItem('defaultReminder') || '30', 10),
    async load() {
      try {
        const [{ data: m }, { data: s }] = await Promise.all([
          axios.get('/api/auth/me'),
          axios.get('/api/dashboard/stats'),
        ]);
        this.me = m.data || {};
        this.stats = s.data || {};
        refreshIcons();
      } catch (e) { apiError(e, 'Gagal memuat profil'); }
    },
    async saveUsername() {
      try {
        await axios.put('/api/user/username', this.uname);
        toast('Username diperbarui', 'success');
        setTimeout(() => location.reload(), 800);
      } catch (e) { apiError(e, 'Gagal mengubah username'); }
    },
    async savePassword() {
      try {
        await axios.put('/api/user/password', this.pwd);
        toast('Password diperbarui', 'success');
        this.pwd = { old_password: '', new_password: '' };
        this.showPwd = false;
      } catch (e) { apiError(e, 'Gagal mengubah password'); }
    },
    saveReminder() {
      localStorage.setItem('defaultReminder', String(this.defaultReminder || 30));
      toast('Preferensi pengingat disimpan', 'success');
    },
    async exportData() {
      try {
        const [{ data: td }, { data: cd }] = await Promise.all([
          axios.get('/api/tasks', { params: { limit: 100 } }),
          axios.get('/api/categories'),
        ]);
        const blob = new Blob([JSON.stringify({ exported_at: new Date().toISOString(), tasks: td.data, categories: cd.data }, null, 2)], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'todolist-export.json';
        a.click();
        URL.revokeObjectURL(a.href);
        toast('Data diekspor', 'success');
      } catch (e) { apiError(e, 'Gagal mengekspor'); }
    },
    async clearCompleted() {
      try {
        const { data } = await axios.get('/api/tasks', { params: { status: 'completed', limit: 100 } });
        for (const item of (data.data || [])) {
          await axios.delete('/api/tasks/' + item.uuid);
        }
        this.confirmClear = false;
        toast('Tugas selesai dihapus', 'success');
        this.load();
      } catch (e) { apiError(e, 'Gagal membersihkan'); }
    },
    async logout() {
      await axios.post('/api/auth/logout');
      location.href = '/login';
    },
  };
}
</script>
