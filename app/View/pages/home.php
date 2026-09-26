<div x-data="dashboard()" x-init="load()">
  <div class="app-page-head">
    <h2>Dashboard</h2>
    <p class="uk-text-meta">Ringkasan progres tugas Anda sekilas.</p>
  </div>

  <div class="uk-grid uk-grid-small uk-child-width-1-3@m" uk-grid>
    <div><div class="uk-card uk-card-default uk-card-body" style="display: flex; align-items: center; gap: 14px;">
      <span class="auth-brand ic-lg" style="margin: 0; background: #f4f4f5; color: #18181b;"><i data-lucide="layers"></i></span>
      <div><div class="uk-heading-medium" style="margin: 0; font-size: 30px;" x-text="stats.total ?? '-'"></div>
      <div class="uk-text-meta">Total tugas</div></div>
    </div></div>
    <div><div class="uk-card uk-card-default uk-card-body" style="display: flex; align-items: center; gap: 14px;">
      <span class="auth-brand ic-lg" style="margin: 0; background: #fef3c7; color: #b45309;"><i data-lucide="clock"></i></span>
      <div><div class="uk-heading-medium" style="margin: 0; font-size: 30px;" x-text="stats.pending ?? '-'"></div>
      <div class="uk-text-meta">Pending</div></div>
    </div></div>
    <div><div class="uk-card uk-card-default uk-card-body" style="display: flex; align-items: center; gap: 14px;">
      <span class="auth-brand ic-lg" style="margin: 0; background: #dcfce7; color: #15803d;"><i data-lucide="circle-check-big"></i></span>
      <div><div class="uk-heading-medium" style="margin: 0; font-size: 30px;" x-text="stats.completed ?? '-'"></div>
      <div class="uk-text-meta">Selesai</div></div>
    </div></div>
  </div>

  <div class="uk-grid uk-grid-small uk-margin-top" uk-grid>
    <div class="uk-width-1-2@m">
      <div class="uk-card uk-card-default uk-card-body">
        <h4 style="margin: 0 0 12px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="chart-pie"></i> Pending vs Selesai</h4>
        <div id="donut"></div>
      </div>
    </div>
    <div class="uk-width-1-2@m">
      <div class="uk-card uk-card-default uk-overflow-auto">
        <h4 style="margin: 0; padding: 20px 22px 4px; display: flex; align-items: center; gap: 8px;" class="ic-sm"><i data-lucide="tags"></i> Per Kategori</h4>
        <table class="uk-table uk-table-hover uk-table-divider uk-table-middle uk-table-small app-table" style="margin: 0;">
          <thead><tr><th style="padding-left: 22px;">Kategori</th><th class="uk-text-right">Total</th><th class="uk-text-right">Pending</th><th class="uk-text-right" style="padding-right: 22px;">Selesai</th></tr></thead>
          <tbody>
            <template x-for="c in (stats.by_category || [])" :key="c.category_uuid">
              <tr>
                <td style="padding-left: 22px; font-weight: 500;"><span class="dot" :style="'background:' + catBg(c.category_color)"></span><span x-text="c.category_name"></span></td>
                <td class="uk-text-right" x-text="c.total"></td>
                <td class="uk-text-right" x-text="c.pending"></td>
                <td class="uk-text-right" style="padding-right: 22px;" x-text="c.completed"></td>
              </tr>
            </template>
            <template x-if="!(stats.by_category || []).length">
              <tr><td colspan="4" class="uk-text-center uk-text-meta" style="padding: 28px 0;">Belum ada kategori.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
function dashboard() {
  return {
    stats: {},
    chart: null,
    async load() {
      try {
        const { data } = await axios.get('/api/dashboard/stats');
        this.stats = data.data;
        this.render();
      } catch (e) { apiError(e, 'Gagal memuat statistik'); }
    },
    render() {
      const el = document.querySelector('#donut');
      if (!el || typeof ApexCharts === 'undefined') return;
      if (this.chart) this.chart.destroy();
      this.chart = new ApexCharts(el, {
        chart: { type: 'donut', height: 260 },
        series: [this.stats.pending || 0, this.stats.completed || 0],
        labels: ['Pending', 'Selesai'],
        legend: { position: 'bottom' },
      });
      this.chart.render();
    }
  };
}
</script>
