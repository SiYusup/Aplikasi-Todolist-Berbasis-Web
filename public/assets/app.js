// Helper global: toast + axios defaults + lucide refresh
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

function toast(msg, type = 'info') {
  const bg = { info: '#27272a', success: '#15803d', warning: '#b45309', error: '#b91c1c' }[type] || '#27272a';
  Toastify({ text: msg, duration: 3500, gravity: 'top', position: 'right', style: { background: bg } }).showToast();
}

function apiError(e, fallback = 'Terjadi kesalahan') {
  const msg = e?.response?.data?.message || fallback;
  const errs = e?.response?.data?.errors;
  toast(errs ? msg + ': ' + Object.values(errs).join(', ') : msg, 'error');
}

function refreshIcons() {
  if (window.lucide) lucide.createIcons();
}

// ---- Tema terang/gelap (persist localStorage, class di <html>) ----
function currentTheme() {
  try { return localStorage.getItem('theme') || 'light'; } catch (e) { return 'light'; }
}
function setTheme(mode) {
  try { localStorage.setItem('theme', mode); } catch (e) {}
  document.documentElement.classList.toggle('app-dark', mode === 'dark');
  if (window.Alpine && Alpine.store('prefs')) Alpine.store('prefs').theme = mode;
}

// ---- Bahasa ID/EN ----
function currentLang() {
  try { return localStorage.getItem('lang') || 'id'; } catch (e) { return 'id'; }
}
const I18N = {
  id: {
    'nav.dashboard': 'Dashboard', 'nav.tasks': 'Tugas', 'nav.categories': 'Kategori', 'nav.history': 'History', 'nav.settings': 'Pengaturan',
    'common.save': 'Simpan', 'common.cancel': 'Batal', 'common.delete': 'Hapus', 'common.edit': 'Edit', 'common.yes': 'Ya',
    'settings.title': 'Pengaturan', 'settings.sub': 'Profil, tampilan, bahasa, dan data Anda.',
    'settings.profile': 'Profil Saya', 'settings.username': 'Username', 'settings.status': 'Status', 'settings.joined': 'Bergabung sejak',
    'settings.total': 'Total', 'settings.pending': 'Pending', 'settings.done': 'Selesai',
    'settings.editProfile': 'Ubah Profile', 'settings.editPassword': 'Ubah Password',
    'settings.newUsername': 'Username baru', 'settings.currentPassword': 'Password saat ini',
    'settings.oldPassword': 'Password lama', 'settings.newPassword': 'Password baru (min 6)',
    'settings.appearance': 'Tampilan', 'settings.appearanceSub': 'Pilih mode terang atau gelap.',
    'settings.light': 'Terang', 'settings.dark': 'Gelap',
    'settings.language': 'Bahasa', 'settings.languageSub': 'Bahasa antarmuka aplikasi.',
    'settings.reminder': 'Pengingat Default', 'settings.reminderSub': 'Menit sebelum jatuh tempo untuk tugas baru.',
    'settings.reminderSave': 'Simpan preferensi',
    'settings.data': 'Data', 'settings.dataSub': 'Ekspor atau bersihkan data tugas Anda.',
    'settings.export': 'Ekspor JSON', 'settings.clearDone': 'Hapus semua yang selesai',
    'settings.clearConfirm': 'Yakin hapus semua tugas yang selesai? Tidak bisa dibatalkan.',
    'settings.logout': 'Keluar', 'settings.logoutSub': 'Akhiri sesi di perangkat ini.',
    'settings.logoutTitle': 'Keluar dari aplikasi?',
    'settings.logoutMsg': 'Anda harus login kembali untuk mengakses tugas Anda.',
  },
  en: {
    'nav.dashboard': 'Dashboard', 'nav.tasks': 'Tasks', 'nav.categories': 'Categories', 'nav.history': 'History', 'nav.settings': 'Settings',
    'common.save': 'Save', 'common.cancel': 'Cancel', 'common.delete': 'Delete', 'common.edit': 'Edit', 'common.yes': 'Yes',
    'settings.title': 'Settings', 'settings.sub': 'Your profile, appearance, language, and data.',
    'settings.profile': 'My Profile', 'settings.username': 'Username', 'settings.status': 'Status', 'settings.joined': 'Member since',
    'settings.total': 'Total', 'settings.pending': 'Pending', 'settings.done': 'Done',
    'settings.editProfile': 'Edit Profile', 'settings.editPassword': 'Change Password',
    'settings.newUsername': 'New username', 'settings.currentPassword': 'Current password',
    'settings.oldPassword': 'Old password', 'settings.newPassword': 'New password (min 6)',
    'settings.appearance': 'Appearance', 'settings.appearanceSub': 'Choose light or dark mode.',
    'settings.light': 'Light', 'settings.dark': 'Dark',
    'settings.language': 'Language', 'settings.languageSub': 'Application interface language.',
    'settings.reminder': 'Default Reminder', 'settings.reminderSub': 'Minutes before due date for new tasks.',
    'settings.reminderSave': 'Save preference',
    'settings.data': 'Data', 'settings.dataSub': 'Export or clean up your task data.',
    'settings.export': 'Export JSON', 'settings.clearDone': 'Delete all completed',
    'settings.clearConfirm': 'Delete all completed tasks? This cannot be undone.',
    'settings.logout': 'Log out', 'settings.logoutSub': 'End the session on this device.',
    'settings.logoutTitle': 'Log out of the app?',
    'settings.logoutMsg': 'You will need to log in again to access your tasks.',
  },
};
function t(key) {
  const lang = (window.Alpine && Alpine.store('prefs') && Alpine.store('prefs').lang) || currentLang();
  return (I18N[lang] && I18N[lang][key]) || I18N.id[key] || key;
}
function applyStaticLang() {
  document.querySelectorAll('[data-i18n]').forEach((el) => { el.textContent = t(el.getAttribute('data-i18n')); });
}
function setLang(l) {
  try { localStorage.setItem('lang', l); } catch (e) {}
  if (window.Alpine && Alpine.store('prefs')) Alpine.store('prefs').lang = l;
  applyStaticLang();
}
document.addEventListener('alpine:init', () => {
  Alpine.store('prefs', { theme: currentTheme(), lang: currentLang() });
});
document.addEventListener('DOMContentLoaded', applyStaticLang);

// Palet warna kategori → tint badge (minimalis). Kunci = nilai `color` kategori.
const CATEGORY_COLORS = {
  zinc:   { bg: '#f4f4f5', fg: '#52525b' },
  red:    { bg: '#fee2e2', fg: '#b91c1c' },
  orange: { bg: '#ffedd5', fg: '#c2410c' },
  amber:  { bg: '#fef3c7', fg: '#b45309' },
  green:  { bg: '#dcfce7', fg: '#15803d' },
  blue:   { bg: '#dbeafe', fg: '#1d4ed8' },
  violet: { bg: '#ede9fe', fg: '#6d28d9' },
  pink:   { bg: '#fce7f3', fg: '#be185d' },
};

function catBg(color) {
  if (!color) return CATEGORY_COLORS.zinc.bg;
  if (color.startsWith('#') || color.startsWith('rgb')) return color;
  return (CATEGORY_COLORS[color] || CATEGORY_COLORS.zinc).bg;
}

function catFg(bg) {
  // kontras otomatis: teks putih untuk bg gelap, abu tua untuk bg terang
  let r, g, b;
  if (bg.startsWith('#')) {
    const h = bg.slice(1);
    const v = (h.length === 3 ? h.split('').map(c => c + c).join('') : h).slice(0, 6);
    r = parseInt(v.slice(0, 2), 16); g = parseInt(v.slice(2, 4), 16); b = parseInt(v.slice(4, 6), 16);
  } else {
    const m = bg.match(/\d+(\.\d+)?/g) || [];
    [r, g, b] = [Number(m[0] || 244), Number(m[1] || 244), Number(m[2] || 245)];
  }
  const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  return lum > 0.6 ? '#3f3f46' : '#ffffff';
}

function toHex(color) {
  // normalisasi nilai lama (nama palet) ke hex agar cocok untuk <input type="color">
  if (!color) return '#71717a';
  if (color.startsWith('#') && (color.length === 4 || color.length === 7)) return color;
  return catBg(color);
}

function catBadgeStyle(color) {
  if (color && (color.startsWith('#') || color.startsWith('rgb'))) {
    const bg = color;
    return `background:${bg};color:${catFg(bg)};`;
  }
  const c = CATEGORY_COLORS[color] || CATEGORY_COLORS.zinc;
  return `background:${c.bg};color:${c.fg};`;
}
