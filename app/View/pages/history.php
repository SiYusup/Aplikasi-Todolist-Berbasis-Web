<div x-data="historyPage()" x-init="load()">
  <div class="app-page-head">
    <h2>History</h2>
    <p class="uk-text-meta">Tugas yang telah selesai. Buka lagi jika ingin mengerjakan ulang.</p>
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
            <span class="uk-badge" style="flex-shrink: 0; background:#dcfce7; color:#15803d;">Selesai</span>
          </div>
          <p style="margin: 0 0 10px;">
            <span x-show="t.category" class="uk-badge" :style="catBadgeStyle(t.category?.color)" x-text="t.category?.name"></span>
          </p>
          <p class="uk-text-small uk-text-meta task-desc" style="margin: 0 0 10px; line-height: 1.5;" x-text="t.description || 'Tanpa deskripsi'"></p>
          <p class="uk-text-small uk-text-meta ic-sm" style="margin: 0 0 14px; display: flex; gap: 5px; align-items: center;">
            <i data-lucide="circle-check-big"></i><span x-text="'Selesai: ' + ((t.completed_at || '').slice(0, 16).replace('T', ' ') || '-')"></span>
          </p>
          <div class="task-foot" style="display: flex; gap: 8px; padding-top: 12px; border-top: 1px solid #f4f4f5;">
            <button class="uk-button uk-button-default uk-button-small" @click="reopen(t)">Buka lagi</button>
            <button class="uk-button uk-button-danger uk-button-small" @click="askDelete(t)">Hapus</button>
          </div>
        </div>
      </div>
    </template>
  </div>

  <div x-show="!tasks.length" x-cloak class="uk-card uk-card-default uk-card-body uk-text-center uk-text-meta" style="padding: 44px 20px;">
    <i data-lucide="history" style="width: 30px; height: 30px; opacity: 0.5;"></i>
    <p style="margin: 10px 0 0;">Belum ada tugas yang selesai.</p>
  </div>

  <dialog class="app-dialog" x-ref="delDialog">
    <div class="uk-card-body" style="padding: 26px;">
      <h4 style="margin: 0 0 6px;">Hapus permanen?</h4>
      <p class="uk-text-meta" style="margin: 0 0 20px;" x-text="toDelete?.title"></p>
      <div style="display: flex; gap: 8px; justify-content: flex-end;">
        <button class="uk-button uk-button-default" @click="$refs.delDialog.close()">Batal</button>
        <button class="uk-button uk-button-danger" @click="doDelete()">Ya, hapus</button>
      </div>
    </div>
  </dialog>
</div>
<script>
function historyPage() {
  return {
    tasks: [], categories: [], toDelete: null,
    filter: { category_uuid: '' },
    async load() {
      try {
        const params = { status: 'completed' };
        if (this.filter.category_uuid) params.category_uuid = this.filter.category_uuid;
        const [{ data: td }, { data: cd }] = await Promise.all([
          axios.get('/api/tasks', { params }),
          axios.get('/api/categories'),
        ]);
        this.tasks = td.data || [];
        this.categories = cd.data || [];
        refreshIcons();
      } catch (e) { apiError(e, 'Gagal memuat history'); }
    },
    async reopen(t) {
      try {
        await axios.patch(`/api/tasks/${t.uuid}/reopen`);
        toast('Tugas kembali ke daftar aktif', 'success');
        this.load();
      } catch (e) { apiError(e, 'Gagal membuka tugas'); }
    },
    askDelete(t) { this.toDelete = t; this.$refs.delDialog.showModal(); },
    async doDelete() {
      try {
        await axios.delete('/api/tasks/' + this.toDelete.uuid);
        this.$refs.delDialog.close();
        toast('Tugas dihapus', 'success');
        this.load();
      } catch (e) { apiError(e, 'Gagal menghapus'); }
    },
  };
}
</script>
