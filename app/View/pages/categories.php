<div x-data="categoryPage()" x-init="load()">
  <div class="app-page-head" style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <h2>Kategori</h2>
      <p class="uk-text-meta">Kelompokkan tugas berdasarkan kategori & warna.</p>
    </div>
    <button class="uk-button uk-button-primary" @click="openModal()"><i data-lucide="plus"></i> Kategori Baru</button>
  </div>

  <div class="uk-card uk-card-default uk-overflow-auto">
    <table class="uk-table uk-table-hover uk-table-divider uk-table-middle app-table" style="margin: 0;">
      <thead>
        <tr><th style="padding-left: 22px;">Nama</th><th>Warna</th><th class="uk-text-right" style="padding-right: 22px; width: 190px;">Aksi</th></tr>
      </thead>
      <tbody>
        <template x-for="c in categories" :key="c.uuid">
          <tr>
          <td style="padding-left: 22px; font-weight: 500;" x-text="c.name"></td>
          <td><span class="uk-badge" :style="catBadgeStyle(c.color)" x-text="c.color"></span></td>
            <td class="uk-text-right" style="padding-right: 22px; white-space: nowrap;">
              <button class="uk-button uk-button-default uk-button-small" @click="openModal(c)">Edit</button>
              <button class="uk-button uk-button-danger uk-button-small" @click="askDelete(c)">Hapus</button>
            </td>
          </tr>
        </template>
        <template x-if="!categories.length">
          <tr><td colspan="3" class="uk-text-center uk-text-meta" style="padding: 36px 0;">Belum ada kategori. Klik “Kategori Baru” untuk mulai.</td></tr>
        </template>
      </tbody>
    </table>
  </div>

  <!-- Modal tambah/edit: overlay mandiri -->
  <div x-show="modalOpen" x-cloak class="app-overlay" @click.self="modalOpen = false">
    <div class="uk-card uk-card-default uk-card-body app-modal" style="max-width: 440px;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h4 style="margin: 0;" x-text="form.uuid ? 'Edit Kategori' : 'Kategori Baru'"></h4>
        <button class="uk-button uk-button-default uk-button-small" @click="modalOpen = false" aria-label="Tutup">✕</button>
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">Nama *</label>
        <input class="uk-input" x-model="form.name" placeholder="cth. Kuliah" />
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">Warna badge</label>
        <div style="display: flex; align-items: center; gap: 12px;">
          <input type="color" x-model="form.color" style="width: 52px; height: 40px; padding: 3px; border: 1px solid #e4e4e7; border-radius: 9px; cursor: pointer; background: #fff;" />
          <code class="uk-text-small uk-text-meta" x-text="form.color"></code>
          <span class="uk-badge" :style="catBadgeStyle(form.color)" x-text="form.name || 'Contoh'"></span>
        </div>
        <p class="uk-text-meta uk-text-small" style="margin: 8px 0 0;">Badge kategori di kartu tugas mengikuti warna ini.</p>
      </div>
      <div class="uk-text-right" style="margin-top: 20px; display: flex; gap: 8px; justify-content: flex-end;">
        <button class="uk-button uk-button-default" @click="modalOpen = false">Batal</button>
        <button class="uk-button uk-button-primary" @click="save()">Simpan</button>
      </div>
    </div>
  </div>

  <dialog class="app-dialog" x-ref="delDialog">
    <div class="uk-card-body" style="padding: 26px;">
      <h4 style="margin: 0 0 6px;">Hapus kategori ini?</h4>
      <p class="uk-text-meta" style="margin: 0 0 20px;">Tugas yang memakainya otomatis menjadi tanpa kategori.</p>
      <div style="display: flex; gap: 8px; justify-content: flex-end;">
        <button class="uk-button uk-button-default" @click="$refs.delDialog.close()">Batal</button>
        <button class="uk-button uk-button-danger" @click="doDelete()">Ya, hapus</button>
      </div>
    </div>
  </dialog>
</div>
<script>
function categoryPage() {
  return {
    categories: [], modalOpen: false, toDelete: null,
    form: { uuid: '', name: '', color: '#71717a' },
    async load() {
      try {
        const { data } = await axios.get('/api/categories');
        this.categories = data.data || [];
        refreshIcons();
      } catch (e) { apiError(e, 'Gagal memuat kategori'); }
    },
    openModal(c = null) {
      this.form = c ? { uuid: c.uuid, name: c.name, color: toHex(c.color) } : { uuid: '', name: '', color: '#71717a' };
      this.modalOpen = true;
    },
    async save() {
      try {
        if (this.form.uuid) await axios.put('/api/categories/' + this.form.uuid, this.form);
        else await axios.post('/api/categories', this.form);
        this.modalOpen = false;
        toast('Kategori disimpan', 'success');
        this.load();
      } catch (e) { apiError(e, 'Gagal menyimpan'); }
    },
    askDelete(c) { this.toDelete = c; this.$refs.delDialog.showModal(); },
    async doDelete() {
      try {
        await axios.delete('/api/categories/' + this.toDelete.uuid);
        this.$refs.delDialog.close();
        toast('Kategori dihapus', 'success');
        this.load();
      } catch (e) { apiError(e, 'Gagal menghapus'); }
    },
  };
}
</script>
