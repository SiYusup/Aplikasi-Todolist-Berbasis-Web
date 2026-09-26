<div x-data="taskPage()" x-init="load()">
  <div class="app-page-head" style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <h2>Tugas</h2>
      <p class="uk-text-meta">Tugas aktif Anda. Yang sudah selesai pindah ke History.</p>
    </div>
    <button class="uk-button uk-button-primary" @click="openCreate()"><i data-lucide="plus"></i> Tugas Baru</button>
  </div>

  <div class="uk-grid uk-grid-small uk-margin-bottom" uk-grid>
    <div class="uk-width-1-4@m">
      <select class="uk-select" x-model="filter.category_uuid" @change="load()">
        <option value="">Semua kategori</option>
        <template x-for="c in categories" :key="c.uuid">
          <option :value="c.uuid" x-text="c.name"></option>
        </template>
      </select>
    </div>
  </div>

  <div class="uk-grid uk-grid-small uk-child-width-1-3@m task-grid" uk-grid>
    <template x-for="t in tasks" :key="t.uuid">
      <div>
        <div class="uk-card uk-card-default uk-card-body task-card">
          <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px;">
            <strong x-text="t.title" style="line-height: 1.4;"></strong>
            <span class="uk-badge" style="flex-shrink: 0; background:#fef3c7; color:#b45309;">Pending</span>
          </div>
          <p style="margin: 0 0 10px;">
            <span x-show="t.category" class="uk-badge" :style="catBadgeStyle(t.category?.color)" x-text="t.category?.name"></span>
          </p>
          <p class="uk-text-small uk-text-meta task-desc" style="margin: 0 0 10px; line-height: 1.5;" x-text="t.description || 'Tanpa deskripsi'"></p>
          <p class="uk-text-small uk-text-meta ic-sm" style="margin: 0 0 14px; display: flex; gap: 14px; flex-wrap: wrap;">
            <span x-show="t.due_date" style="display: inline-flex; align-items: center; gap: 5px;"><i data-lucide="calendar-clock"></i><span x-text="(t.due_date || '').slice(0, 16).replace('T', ' ')"></span></span>
          </p>
          <div class="task-foot" style="display: flex; gap: 8px; padding-top: 12px; border-top: 1px solid #f4f4f5;">
            <button class="uk-button uk-button-default uk-button-small" style="flex: 1;" @click="openDetail(t)"><i data-lucide="file-text"></i> Detail</button>
            <button class="uk-button uk-button-primary uk-button-small" style="flex: 1;" @click="quickComplete(t)">Selesai</button>
          </div>
        </div>
      </div>
    </template>
  </div>

  <div x-show="!tasks.length" x-cloak class="uk-card uk-card-default uk-card-body uk-text-center uk-text-meta" style="padding: 44px 20px;">
    <i data-lucide="inbox" style="width: 30px; height: 30px; opacity: 0.5;"></i>
    <p style="margin: 10px 0 0;">Tidak ada tugas aktif. Klik “Tugas Baru” untuk mulai.</p>
  </div>

  <!-- Modal detail / tambah / edit -->
  <div x-show="modalOpen" x-cloak class="app-overlay" @click.self="closeModal()">
    <div class="uk-card uk-card-default uk-card-body app-modal">

      <!-- MODE LIHAT -->
      <template x-if="mode === 'view' && detail">
        <div>
          <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px;">
            <h4 style="margin: 0; line-height: 1.4;" x-text="detail.title"></h4>
            <button class="uk-button uk-button-default uk-button-small" @click="closeModal()" aria-label="Tutup">✕</button>
          </div>
          <p style="margin: 0 0 14px; display: flex; gap: 8px; flex-wrap: wrap;">
            <span class="uk-badge"
              :style="detail.status === 'completed' ? 'background:#dcfce7;color:#15803d;' : 'background:#fef3c7;color:#b45309;'"
              x-text="detail.status === 'completed' ? 'Selesai' : 'Pending'"></span>
            <span x-show="detail.category" class="uk-badge" :style="catBadgeStyle(detail.category?.color)" x-text="detail.category?.name"></span>
          </p>
          <p class="uk-text-small" style="margin: 0 0 16px; line-height: 1.6; white-space: pre-wrap;" x-text="detail.description || 'Tanpa deskripsi'"></p>
          <dl class="uk-description-list uk-text-small" style="margin: 0 0 20px; display: grid; grid-template-columns: auto 1fr; gap: 6px 14px;">
            <dt class="uk-text-meta">Jatuh tempo</dt><dd style="margin: 0;" x-text="detail.due_date ? detail.due_date.slice(0, 16).replace('T', ' ') : '-'"></dd>
            <dt class="uk-text-meta">Pengingat</dt><dd style="margin: 0;" x-text="(detail.reminder_minutes ?? 30) + ' menit sebelumnya'"></dd>
            <dt class="uk-text-meta">Selesai pada</dt><dd style="margin: 0;" x-text="detail.completed_at ? detail.completed_at.slice(0, 16).replace('T', ' ') : '-'"></dd>
          </dl>
          <div x-show="!confirmDelete" style="display: flex; gap: 8px; flex-wrap: wrap; padding-top: 14px; border-top: 1px solid #f4f4f5;">
            <button x-show="detail.status === 'pending'" class="uk-button uk-button-primary uk-button-small" @click="setStatus('complete')">Tandai selesai</button>
            <button x-show="detail.status === 'completed'" class="uk-button uk-button-default uk-button-small" @click="setStatus('reopen')">Buka lagi</button>
            <button class="uk-button uk-button-default uk-button-small" @click="startEdit()">Edit</button>
            <button class="uk-button uk-button-danger uk-button-small" @click="confirmDelete = true">Hapus</button>
          </div>
          <div x-show="confirmDelete" style="padding-top: 14px; border-top: 1px solid #f4f4f5;">
            <p class="uk-text-small" style="margin: 0 0 12px;">Yakin hapus tugas ini? Tindakan tidak bisa dibatalkan.</p>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
              <button class="uk-button uk-button-default uk-button-small" @click="confirmDelete = false">Batal</button>
              <button class="uk-button uk-button-danger uk-button-small" @click="doDelete()">Ya, hapus</button>
            </div>
          </div>
        </div>
      </template>

      <!-- MODE FORM (tambah / edit) -->
      <template x-if="mode !== 'view'">
        <div>
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h4 style="margin: 0;" x-text="mode === 'create' ? 'Tugas Baru' : 'Edit Tugas'"></h4>
            <button class="uk-button uk-button-default uk-button-small" @click="mode === 'edit' ? mode = 'view' : closeModal()" aria-label="Tutup">✕</button>
          </div>
          <div class="uk-margin">
            <label class="uk-form-label">Judul *</label>
            <input class="uk-input" x-model="form.title" placeholder="cth. Belajar PHP MVC" />
          </div>
          <div class="uk-margin">
            <label class="uk-form-label">Deskripsi</label>
            <textarea class="uk-textarea" rows="3" x-model="form.description" placeholder="Detail tugas (opsional)"></textarea>
          </div>
          <div class="uk-grid uk-grid-small" uk-grid>
            <div class="uk-width-1-2">
              <label class="uk-form-label">Kategori</label>
              <select class="uk-select" x-model="form.category_uuid">
                <option value="">Tanpa kategori</option>
                <template x-for="c in categories" :key="c.uuid"><option :value="c.uuid" x-text="c.name"></option></template>
              </select>
            </div>
            <div class="uk-width-1-2">
              <label class="uk-form-label">Jatuh tempo</label>
              <input class="uk-input" type="datetime-local" x-model="form.due_date" />
            </div>
          </div>
          <div class="uk-margin">
            <label class="uk-form-label">Ingatkan (menit sebelum jatuh tempo)</label>
            <input class="uk-input" type="number" min="0" x-model.number="form.reminder_minutes" />
          </div>
          <div style="margin-top: 20px; display: flex; gap: 8px; justify-content: flex-end;">
            <button class="uk-button uk-button-default" @click="mode === 'edit' ? mode = 'view' : closeModal()">Batal</button>
            <button class="uk-button uk-button-primary" @click="save()">Simpan</button>
          </div>
        </div>
      </template>

    </div>
  </div>
</div>
<script>
function taskPage() {
  return {
    tasks: [], categories: [],
    filter: { category_uuid: '' },
    modalOpen: false, mode: 'view', detail: null, confirmDelete: false,
    form: { uuid: '', title: '', description: '', category_uuid: '', due_date: '', reminder_minutes: parseInt(localStorage.getItem('defaultReminder') || '30', 10) },
    async load() {
      try {
        const params = { status: 'pending' };
        if (this.filter.category_uuid) params.category_uuid = this.filter.category_uuid;
        const [{ data: td }, { data: cd }] = await Promise.all([
          axios.get('/api/tasks', { params }),
          axios.get('/api/categories'),
        ]);
        this.tasks = td.data || [];
        this.categories = cd.data || [];
        refreshIcons();
      } catch (e) { apiError(e, 'Gagal memuat tugas'); }
    },
    openCreate() {
      this.form = { uuid: '', title: '', description: '', category_uuid: '', due_date: '', reminder_minutes: parseInt(localStorage.getItem('defaultReminder') || '30', 10) };
      this.mode = 'create';
      this.modalOpen = true;
    },
    openDetail(t) {
      this.detail = t;
      this.confirmDelete = false;
      this.mode = 'view';
      this.modalOpen = true;
    },
    closeModal() {
      this.modalOpen = false;
      this.detail = null;
      this.confirmDelete = false;
    },
    startEdit() {
      const t = this.detail;
      this.form = { uuid: t.uuid, title: t.title, description: t.description || '', category_uuid: t.category?.uuid || '', due_date: (t.due_date || '').slice(0, 16), reminder_minutes: t.reminder_minutes ?? 30 };
      this.mode = 'edit';
    },
    async save() {
      try {
        const payload = { ...this.form };
        if (!payload.category_uuid) payload.category_uuid = null;
        if (!payload.due_date) payload.due_date = null;
        let saved;
        if (payload.uuid) {
          ({ data: saved } = await axios.put('/api/tasks/' + payload.uuid, payload));
        } else {
          ({ data: saved } = await axios.post('/api/tasks', payload));
        }
        toast('Tugas disimpan', 'success');
        await this.load();
        if (this.mode === 'create') {
          this.closeModal();
        } else {
          this.detail = saved.data;
          this.mode = 'view';
        }
      } catch (e) { apiError(e, 'Gagal menyimpan'); }
    },
    async quickComplete(t) {
      try {
        await axios.patch(`/api/tasks/${t.uuid}/complete`);
        toast('Tugas selesai, pindah ke History', 'success');
        this.load();
      } catch (e) { apiError(e, 'Gagal menyelesaikan tugas'); }
    },
    async setStatus(action) {
      try {
        await axios.patch(`/api/tasks/${this.detail.uuid}/${action}`);
        if (action === 'complete') {
          toast('Tugas selesai, pindah ke History', 'success');
          this.closeModal();
        } else {
          const { data } = await axios.get('/api/tasks/' + this.detail.uuid);
          this.detail = data.data;
          toast('Tugas dibuka lagi', 'success');
        }
        this.load();
      } catch (e) { apiError(e, 'Gagal mengubah status'); }
    },
    async doDelete() {
      try {
        await axios.delete('/api/tasks/' + this.detail.uuid);
        toast('Tugas dihapus', 'success');
        this.closeModal();
        this.load();
      } catch (e) { apiError(e, 'Gagal menghapus'); }
    },
  };
}
</script>
