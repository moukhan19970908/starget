/* =============================================================
   STARGET CRM — MAIN JAVASCRIPT
   ============================================================= */

const Starget = {

  /* --------------------------------------------------------
     CONFIG
  -------------------------------------------------------- */
  cfg: {
    api: '/api',
    tokenKey: 'starget_token',
    userKey:  'starget_user',
  },

  /* --------------------------------------------------------
     AUTH
  -------------------------------------------------------- */
  auth: {
    token() { return localStorage.getItem(Starget.cfg.tokenKey); },
    user()  {
      const u = localStorage.getItem(Starget.cfg.userKey);
      return u ? JSON.parse(u) : null;
    },
    check() {
      if (!this.token()) { window.location.href = '/login'; return false; }
      return true;
    },
    can(permission) {
      // Client-side soft check; real enforcement is on API
      const user = this.user();
      if (!user) return false;
      const role = user.role;
      const map = {
        admin: '*',
        doc_manager: ['dashboard.view','clients.view','clients.create','clients.edit',
          'contracts.view','contracts.create','suppliers.view','suppliers.create',
          'tasks.view','tasks.close','applications.view','transportations.view',
          'vehicles.view','drivers.view'],
        client_manager: ['dashboard.view','clients.view','contracts.view',
          'applications.view','applications.create','applications.edit',
          'applications.change_status','tasks.view','tasks.create',
          'transportations.view','vehicles.view','drivers.view','suppliers.view'],
        logistic_manager: ['dashboard.view','applications.view','transportations.view',
          'transportations.create','transportations.edit','transportations.complete',
          'vehicles.view','vehicles.create','vehicles.edit',
          'drivers.view','drivers.create','drivers.edit',
          'owners.create','owners.edit',
          'suppliers.view','clients.view','contracts.view'],
      };
      const perms = map[role];
      if (perms === '*') return true;
      return Array.isArray(perms) && perms.includes(permission);
    },
    logout() {
      Starget.api('POST', '/auth/logout').finally(() => {
        localStorage.removeItem(Starget.cfg.tokenKey);
        localStorage.removeItem(Starget.cfg.userKey);
        window.location.href = '/login';
      });
    },
  },

  /* --------------------------------------------------------
     API WRAPPER
  -------------------------------------------------------- */
  async api(method, path, data, isFormData = false) {
    const headers = { 'Accept': 'application/json' };
    if (!isFormData) headers['Content-Type'] = 'application/json';
    const token = this.auth.token();
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const opts = { method, headers };
    if (data) opts.body = isFormData ? data : JSON.stringify(data);
    if (method === 'GET' && data) {
      const qs = new URLSearchParams(data).toString();
      path += (path.includes('?') ? '&' : '?') + qs;
    }

    try {
      const res = await fetch(this.cfg.api + path, opts);
      if (res.status === 401) {
        localStorage.removeItem(this.cfg.tokenKey);
        window.location.href = '/login';
        return null;
      }
      const json = await res.json();
      if (!res.ok) {
        if (json.errors) {
          const first = Object.values(json.errors)[0];
          this.toast(Array.isArray(first) ? first[0] : first, 'error');
        } else if (json.message) {
          this.toast(json.message, 'error');
        }
        return null;
      }
      return json;
    } catch (e) {
      this.toast('Ошибка соединения с сервером', 'error');
      return null;
    }
  },

  /* --------------------------------------------------------
     FORMAT UTILITIES
  -------------------------------------------------------- */
  fmt: {
    money(n, curr = '₸') {
      if (!n && n !== 0) return '—';
      return new Intl.NumberFormat('ru-RU').format(Math.round(n)) + '\u00A0' + curr;
    },
    date(d) {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('ru-RU');
    },
    datetime(d) {
      if (!d) return '—';
      return new Date(d).toLocaleString('ru-RU', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
    },
    status(s) {
      const m = {
        active:         { label: 'Активен',      cls: 'badge-success' },
        inactive:       { label: 'Неактивен',    cls: 'badge-danger' },
        blocked:        { label: 'Заблокирован', cls: 'badge-danger' },
        open:           { label: 'Открыта',      cls: 'badge-primary' },
        draft:          { label: 'Черновик',     cls: 'badge-secondary' },
        in_transit:     { label: 'В рейсе',      cls: 'badge-primary' },
        completed:      { label: 'Завершено',    cls: 'badge-success' },
        closed:         { label: 'Закрыта',      cls: 'badge-secondary' },
        refused:        { label: 'Отказано',     cls: 'badge-danger' },
        client_refusal: { label: 'Отказ клиента',cls: 'badge-danger' },
        logistic_refusal:{ label: 'Отказ логиста',cls: 'badge-danger' },
        pending:        { label: 'На проверке',  cls: 'badge-warning' },
        expiring:       { label: 'Истекает',     cls: 'badge-warning' },
        on_trip:        { label: 'На рейсе',     cls: 'badge-primary' },
        idle:           { label: 'Свободен',     cls: 'badge-success' },
        repair:         { label: 'Ремонт',       cls: 'badge-danger' },
        reserve:        { label: 'Резерв',       cls: 'badge-secondary' },
        sick:           { label: 'Больничный',   cls: 'badge-warning' },
      };
      const t = m[s] || { label: s || '—', cls: 'badge-secondary' };
      return `<span class="badge ${t.cls}">${t.label}</span>`;
    },
    docStatus(docs) {
      if (!docs || !docs.length) return '<span class="badge badge-secondary">Нет документов</span>';
      const expiring = docs.filter(d => d.expires_at && new Date(d.expires_at) <= new Date(Date.now() + 30*86400000));
      if (expiring.length) return '<span class="badge badge-warning">ИСТЕКАЕТ</span>';
      return '<span class="badge badge-success">ВАЛИДЕН</span>';
    },
    timeAgo(d) {
      if (!d) return '';
      const diff = Math.floor((Date.now() - new Date(d)) / 1000);
      if (diff < 60)  return diff + ' сек. назад';
      if (diff < 3600) return Math.floor(diff/60) + ' мин. назад';
      if (diff < 86400) return Math.floor(diff/3600) + ' ч. назад';
      return Math.floor(diff/86400) + ' дн. назад';
    },
    percent(n) {
      return (n || 0).toFixed(1) + '%';
    },
    initials(name) {
      if (!name) return '?';
      return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0,2);
    },
  },

  /* --------------------------------------------------------
     DOM HELPERS
  -------------------------------------------------------- */
  dom: {
    set(id, html) {
      const el = document.getElementById(id);
      if (el) el.innerHTML = html;
    },
    setText(id, txt) {
      const el = document.getElementById(id);
      if (el) el.textContent = txt;
    },
    get(id) { return document.getElementById(id); },
    on(id, ev, fn) {
      const el = typeof id === 'string' ? document.getElementById(id) : id;
      if (el) el.addEventListener(ev, fn);
    },
    show(id) {
      const el = document.getElementById(id);
      if (el) el.classList.remove('hidden');
    },
    hide(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('hidden');
    },
    val(id) {
      const el = document.getElementById(id);
      return el ? el.value.trim() : '';
    },
    setVal(id, v) {
      const el = document.getElementById(id);
      if (el) el.value = v;
    },
    qs(sel, ctx = document) { return ctx.querySelector(sel); },
    qsa(sel, ctx = document) { return [...ctx.querySelectorAll(sel)]; },
  },

  /* --------------------------------------------------------
     TOAST NOTIFICATIONS
  -------------------------------------------------------- */
  _toastContainer: null,
  toast(msg, type = 'info') {
    if (!this._toastContainer) {
      this._toastContainer = document.createElement('div');
      this._toastContainer.className = 'toast-container';
      document.body.appendChild(this._toastContainer);
    }
    const icons = {
      success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
      info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons[type]}</svg>
      <span class="toast-msg">${msg}</span>
      <svg class="toast-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px" onclick="this.parentElement.remove()"><path d="M6 18L18 6M6 6l12 12"/></svg>`;
    this._toastContainer.appendChild(t);
    setTimeout(() => t.remove(), 4000);
  },

  /* --------------------------------------------------------
     HEADER USER INIT
  -------------------------------------------------------- */
  initHeader() {
    const user = this.auth.user();
    if (!user) return;
    this.dom.setText('headerUserName', (user.name || '') + ' ' + (user.surname || ''));
    this.dom.setText('headerUserRole', this._roleLabel(user.role));
    const av = this.dom.get('headerAvatar');
    if (av) av.textContent = (user.name || '?')[0].toUpperCase();
  },

  _roleLabel(role) {
    const map = {
      doc_manager:      'Менеджер по документам',
      client_manager:   'Менеджер по клиентам',
      logistic_manager: 'Логист-менеджер',
      admin:            'Администратор',
    };
    return map[role] || role;
  },

  /* --------------------------------------------------------
     PAGINATION HELPER
  -------------------------------------------------------- */
  renderPagination(meta, containerId, onPage) {
    const el = document.getElementById(containerId);
    if (!el || !meta) return;
    const { current_page: cur, last_page: last, total } = meta;
    if (last <= 1) { el.innerHTML = ''; return; }
    let html = '';
    const btn = (p, label, active = false, disabled = false) =>
      `<button class="page-btn${active?' active':''}" ${disabled?'disabled':''} onclick="${disabled || active ? '' : `(${onPage.toString()})(${p})`}">${label}</button>`;
    html += btn(cur - 1, '←', false, cur === 1);
    const range = [];
    for (let i = Math.max(1, cur-2); i <= Math.min(last, cur+2); i++) range.push(i);
    if (range[0] > 1) { html += btn(1, '1'); if (range[0] > 2) html += '<span class="page-btn" style="border:none;cursor:default">…</span>'; }
    range.forEach(p => { html += btn(p, p, p === cur); });
    if (range[range.length-1] < last) { if (range[range.length-1] < last-1) html += '<span class="page-btn" style="border:none;cursor:default">…</span>'; html += btn(last, last); }
    html += btn(cur + 1, '→', false, cur === last);
    el.innerHTML = html;
  },

  /* --------------------------------------------------------
     DICTIONARY LOADERS (cached)
  -------------------------------------------------------- */
  _cache: {},
  async loadDict(key, path) {
    if (this._cache[key]) return this._cache[key];
    const res = await this.api('GET', path);
    if (res && res.success) this._cache[key] = res.data;
    return this._cache[key] || [];
  },
  loadCities()        { return this.loadDict('cities',       '/dict/cities'); },
  loadCurrencies()    { return this.loadDict('currencies',   '/dict/currencies'); },
  loadLoadingTypes()  { return this.loadDict('loadTypes',    '/dict/loading-types'); },
  loadVehicleTypes()  { return this.loadDict('vehicleTypes', '/dict/vehicle-types'); },
  loadDocTypes()      { return this.loadDict('docTypes',     '/dict/document-types'); },
  loadStopTypes()     { return this.loadDict('stopTypes',    '/dict/stop-types'); },
  loadRefusalReasons(){ return this.loadDict('refReasons',  '/dict/refusal-reasons'); },

  fillSelect(id, items, valField = 'id', labelField = 'name', placeholder = 'Выберите...') {
    const el = document.getElementById(id);
    if (!el) return;
    const getLabel = typeof labelField === 'function' ? labelField : (i) => i[labelField];
    el.innerHTML = `<option value="">${placeholder}</option>` +
      items.map(i => `<option value="${i[valField]}">${getLabel(i)}</option>`).join('');
  },

  /* --------------------------------------------------------
     ICONS
  -------------------------------------------------------- */
  icon: {
    dashboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>`,
    apps:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
    truck:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1.5.5M13 16h2l3-4.5V9h-5v7z"/></svg>`,
    folder:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>`,
    users:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
    contract:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h3M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>`,
    building:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0H5m14 0v-2H5v2M7 7h2m-2 4h2m-2 4h2m6-8h2m-2 4h2m-2 4h2"/></svg>`,
    gear:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
    bell:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`,
    plus:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 6v12m6-6H6"/></svg>`,
    search:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>`,
    filter:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18M7 12h10M10 20h4"/></svg>`,
    download:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15V3m0 12l-3-3m3 3l3-3M3 21h18"/></svg>`,
    print:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-6-3H6v6h12v-6h-6z"/></svg>`,
    arrowLeft: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>`,
    check:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    x:         `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6m0-6l6 6"/></svg>`,
    warn:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`,
    doc:       `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
    upload:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12"/></svg>`,
    camera:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>`,
    person:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/></svg>`,
    moreVert:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>`,
    chevDown:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>`,
    arrowUp:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>`,
    arrowDown: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>`,
  },

};

/* -----------------------------------------------------------------
   GLOBAL: init on every protected page
----------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
  if (window._noAuth) return; // login page skips
  if (!Starget.auth.check()) return;
  Starget.initHeader();

  // Logout links
  document.querySelectorAll('[data-logout]').forEach(el => {
    el.addEventListener('click', e => { e.preventDefault(); Starget.auth.logout(); });
  });

  // Active nav
  const path = location.pathname;
  document.querySelectorAll('.nav-item[href]').forEach(el => {
    const href = el.getAttribute('href');
    if (href === '/' && path === '/dashboard') { el.classList.add('active'); return; }
    if (href !== '/' && path.startsWith(href)) el.classList.add('active');
  });
});
