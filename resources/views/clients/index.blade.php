@extends('layouts.app')
@section('title', 'Клиенты')

@section('header_left')
<div>
    <div class="page-title">Справочник клиентов</div>
    <div class="page-subtitle">Управление клиентской базой</div>
</div>
@endsection

@section('header_actions')
<button class="btn btn-primary" onclick="createClient()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать клиента
</button>
@endsection

@section('content')
<div class="side-panel-layout">
    {{-- MAIN TABLE --}}
    <div>
        <div class="toolbar">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="searchInput" placeholder="Поиск по названию, БИН..." oninput="debounceLoad()">
            </div>
            <div class="toolbar-right">
                <select class="form-select" id="statusFilter" onchange="loadClients()" style="width:140px">
                    <option value="">Все статусы</option>
                    <option value="active">Активные</option>
                    <option value="inactive">Неактивные</option>
                </select>
            </div>
        </div>

        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Компания</th>
                        <th>БИН/ИИН</th>
                        <th>Контакт</th>
                        <th>Договоры</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="clientsTable">
                    <tr><td colspan="6" class="table-empty">Загрузка...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="clientsPagination" class="pagination" style="margin-top:16px"></div>
    </div>

    {{-- RIGHT PANEL --}}
    <div>
        <div class="side-stats-card">
            <div class="side-stats-title">Сводка</div>
            <div class="side-stat-row">
                <span>Всего клиентов</span>
                <strong id="statsTotal">—</strong>
            </div>
            <div class="side-stat-row">
                <span>Активные</span>
                <strong id="statsActive" style="color:var(--success)">—</strong>
            </div>
            <div class="side-stat-row">
                <span>Неактивные</span>
                <strong id="statsInactive" style="color:var(--text-muted)">—</strong>
            </div>
        </div>

        <div class="side-tasks-card">
            <div class="side-tasks-title">Ожидающие задачи</div>
            <div id="pendingTasksList">
                <div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">Загрузка...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let debounce;

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadClients, 350);
}

async function loadClients(page) {
    const q = document.getElementById('searchInput').value;
    const s = document.getElementById('statusFilter').value;
    let url = `/clients?page=${page || 1}`;
    if (q) url += `&search=${encodeURIComponent(q)}`;
    if (s) url += `&status=${s}`;

    const res = await Starget.api('GET', url);
    if (!res) return;

    if (res.stats) {
        Starget.dom.set('statsTotal',    res.stats.total    || 0);
        Starget.dom.set('statsActive',   res.stats.active   || 0);
        Starget.dom.set('statsInactive', res.stats.inactive || 0);
    }

    const tbody = document.getElementById('clientsTable');
    const items = res.data || [];
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="table-empty">Клиенты не найдены</td></tr>';
        return;
    }

    const colors = ['#2563eb','#16a34a','#d97706','#dc2626','#7c3aed'];
    tbody.innerHTML = items.map((c, i) => {
        const initials = Starget.fmt.initials(c.company_name || c.name || '?');
        const color = colors[i % colors.length];
        const contracts = c.contracts_count || 0;
        return `<tr onclick="viewClient(${c.id})" style="cursor:pointer">
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="initials-avatar" style="background:${color}">${initials}</div>
                    <div>
                        <div style="font-weight:500">${c.company_name || c.name || '—'}</div>
                        <div style="font-size:11px;color:var(--text-muted)">${c.industry || ''}</div>
                    </div>
                </div>
            </td>
            <td class="text-mono">${c.bin || c.iin || '—'}</td>
            <td>
                <div>${c.contact_person || '—'}</div>
                <div style="font-size:11px;color:var(--text-muted)">${c.phone || ''}</div>
            </td>
            <td><span class="badge badge-secondary">${contracts} договора</span></td>
            <td>${Starget.fmt.status(c.status || 'active')}</td>
            <td>
                <div class="row-actions">
                    <button class="action-link" onclick="event.stopPropagation();viewClient(${c.id})">Открыть</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (res.meta) Starget.renderPagination(res.meta, 'clientsPagination', loadClients);
}

async function loadPendingTasks() {
    const res = await Starget.api('GET', '/tasks?status=open&limit=5');
    const el = document.getElementById('pendingTasksList');
    if (!res || !res.data || !res.data.length) {
        el.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">Нет задач</div>';
        return;
    }
    const prioClass = { high: 'badge-danger', medium: 'badge-warning', low: 'badge-secondary' };
    el.innerHTML = res.data.map(t => `<div class="task-item">
        <div class="task-item-top">
            <span class="task-title">${t.title}</span>
            <span class="badge ${prioClass[t.priority] || 'badge-secondary'}" style="font-size:9px">${t.priority?.toUpperCase() || ''}</span>
        </div>
        <div class="task-due">${Starget.fmt.date(t.due_date) || ''}</div>
    </div>`).join('');
}

function viewClient(id) {
    Starget.toast('Просмотр клиента — в разработке', 'info');
}

function createClient() {
    Starget.toast('Создание клиента — в разработке', 'info');
}

loadClients();
loadPendingTasks();
</script>
@endpush
