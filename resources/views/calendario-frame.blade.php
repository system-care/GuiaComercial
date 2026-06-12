<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendário</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    height: 100%; overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', sans-serif;
    font-size: 13px; background: #f1f5f9; color: #1e293b;
}

/* ── Layout ── */
#app { display: flex; flex-direction: column; height: 100%; }

/* ── Topbar ── */
#topbar {
    background: #fff; border-bottom: 1px solid #e2e8f0;
    padding: 10px 16px; display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
#topbar .tb-nav { display: flex; align-items: center; gap: 4px; }
.tb-btn {
    border: 1px solid #e2e8f0; background: #fff; border-radius: 7px;
    padding: 5px 12px; font-size: 12px; font-weight: 600; cursor: pointer;
    color: #475569; transition: all .15s; line-height: 1.4;
}
.tb-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
.tb-btn.active { background: #6366f1; border-color: #6366f1; color: #fff; }
.tb-icon-btn {
    border: 1px solid #e2e8f0; background: #fff; border-radius: 7px;
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #64748b; transition: all .15s;
}
.tb-icon-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
#tb-title { font-size: 15px; font-weight: 700; color: #0f172a; flex: 1; text-align: center; }
.tb-sep { width: 1px; height: 20px; background: #e2e8f0; margin: 0 4px; }

/* ── Stats bar ── */
#statsbar {
    background: #fff; border-bottom: 1px solid #e2e8f0;
    padding: 8px 16px; display: flex; align-items: center; gap: 8px;
    flex-shrink: 0; overflow-x: auto;
}
.stat-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600;
    white-space: nowrap; border: 1px solid transparent;
}
.stat-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
#stats-total { color: #475569; font-size: 11px; font-weight: 500; margin-left: auto; white-space: nowrap; }

/* ── Cal wrap ── */
#cal-wrap { flex: 1; overflow: hidden; padding: 12px 16px; }

/* ── Custom Grid (Week / Day) ── */
.cg-outer {
    height: 100%; background: #fff; border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 12px rgba(0,0,0,.04);
    overflow: hidden; display: flex; flex-direction: column;
}
.cg-scroll { flex: 1; overflow-y: auto; }
.cg-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.cg-col-time { width: 54px; }
.cg-head-row { position: sticky; top: 0; z-index: 10; }
.cg-th-time {
    background: #f8fafc; border-bottom: 2px solid #e2e8f0;
    width: 54px; padding: 0;
}
.cg-th-day {
    background: #f8fafc; border-bottom: 2px solid #e2e8f0;
    border-left: 1px solid #f1f5f9;
    padding: 8px 4px; text-align: center;
    font-size: 11px; color: #64748b; font-weight: 600;
    text-transform: uppercase; letter-spacing: .04em;
}
.cg-th-day.is-today { background: #eef2ff; }
.cg-dow { display: block; }
.cg-dnum {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    font-size: 13px; font-weight: 700; color: #334155; margin-top: 3px;
}
.cg-dnum.is-today { background: #6366f1; color: #fff; }
.cg-td-time {
    padding: 0 8px 0 4px; font-size: 10px; color: #94a3b8;
    text-align: right; vertical-align: top; padding-top: 3px;
    border-right: 1px solid #e2e8f0; white-space: nowrap; width: 54px;
}
.cg-row { border-top: 1px solid #e2e8f0; }
.cg-row.half { border-top: 1px dashed #f1f5f9; }
.cg-row.now-row > .cg-td-time { color: #f43f5e; font-weight: 800; }
.cg-row.now-row { border-top: 2px solid rgba(244,63,94,.5); }
.cg-td-cell {
    border-left: 1px solid #f1f5f9; padding: 2px 3px;
    vertical-align: top; cursor: pointer; min-height: 22px;
}
.cg-td-cell:hover { background: rgba(99,102,241,.03); }
.cg-td-cell.is-today { background: rgba(99,102,241,.02); }
.cg-td-cell.drag-over { background: rgba(99,102,241,.1) !important; outline: 2px dashed #6366f1; outline-offset: -2px; }
.cg-card {
    border-radius: 6px; padding: 5px 8px; margin-bottom: 3px;
    cursor: pointer; color: #fff; user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,.15);
}
.cg-card:last-child { margin-bottom: 0; }
.cg-card:hover { filter: brightness(1.07); }
.cg-card-time { font-size: 10px; opacity: .85; white-space: nowrap; }
.cg-card-customer { font-size: 12px; font-weight: 700; }
.cg-card-service { font-size: 10px; opacity: .85; }
.cg-card-prof { font-size: 10px; opacity: .7; }

/* ── Month Grid ── */
.cgm-outer {
    height: 100%; background: #fff; border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07); overflow: auto; display: flex; flex-direction: column;
}
.cgm-table { width: 100%; border-collapse: collapse; flex: 1; }
.cgm-th {
    padding: 8px; font-size: 11px; font-weight: 700; color: #64748b;
    text-align: center; background: #f8fafc; border-bottom: 2px solid #e2e8f0;
    text-transform: uppercase; letter-spacing: .04em; position: sticky; top: 0; z-index: 5;
}
.cgm-cell {
    padding: 6px; border: 1px solid #f1f5f9; vertical-align: top;
    cursor: pointer; min-height: 90px;
}
.cgm-cell:hover { background: rgba(99,102,241,.02); }
.cgm-cell.is-today { background: rgba(99,102,241,.03); }
.cgm-cell.other-month { background: #fafafa; color: #cbd5e1; }
.cgm-dnum {
    font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 50%;
}
.cgm-dnum.is-today { background: #6366f1; color: #fff; }
.cgm-pill {
    color: #fff; border-radius: 3px; padding: 2px 5px;
    font-size: 10px; font-weight: 600; margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; display: block;
}
.cgm-more { font-size: 10px; color: #6366f1; font-weight: 600; cursor: pointer; }

/* ── List ── */
.cgl-outer {
    height: 100%; background: #fff; border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07); overflow-y: auto; padding: 4px 0;
}
.cgl-date-hdr {
    padding: 10px 16px 5px; font-size: 11px; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .05em; border-top: 1px solid #f1f5f9;
}
.cgl-date-hdr:first-child { border-top: none; }
.cgl-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 8px 16px; cursor: pointer; border-left: 4px solid transparent; margin: 1px 0;
}
.cgl-item:hover { background: #f8fafc; }
.cgl-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
.cgl-body { flex: 1; }
.cgl-time { font-size: 10px; color: #94a3b8; }
.cgl-customer { font-size: 13px; font-weight: 700; color: #0f172a; }
.cgl-meta { font-size: 11px; color: #64748b; }
.cgl-empty { padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; }

/* ── New Appointment Modal ── */
#nm-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,.4); z-index: 300; backdrop-filter: blur(3px);
    align-items: center; justify-content: center;
}
#nm-backdrop.open { display: flex; }
#nm-modal {
    background: #fff; border-radius: 16px; width: 520px; max-width: calc(100vw - 32px);
    max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2);
    display: flex; flex-direction: column;
}
#nm-header { padding: 20px 24px 0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
#nm-title { font-size: 16px; font-weight: 800; color: #0f172a; }
#nm-close {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0;
    background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: #64748b; font-size: 16px; line-height: 1;
}
#nm-close:hover { background: #f1f5f9; }
#nm-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
.nm-row { display: flex; gap: 12px; }
.nm-group { display: flex; flex-direction: column; gap: 5px; flex: 1; }
.nm-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
.nm-label span { color: #ef4444; margin-left: 2px; }
.nm-input, .nm-select, .nm-textarea {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;
    font-size: 13px; color: #1e293b; background: #fff; width: 100%; outline: none; transition: border-color .15s;
}
.nm-input:focus, .nm-select:focus, .nm-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.nm-textarea { resize: vertical; min-height: 72px; font-family: inherit; }
.nm-select { appearance: auto; }
.nm-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }
#nm-quick-wrap {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 14px; display: none; flex-direction: column; gap: 10px; margin-top: 4px;
}
#nm-quick-wrap.open { display: flex; }
.nm-quick-title { font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 2px; }
.nm-quick-row { display: flex; gap: 8px; }
#nm-footer { padding: 14px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; }
.nm-btn-cancel { font-size: 13px; color: #64748b; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; cursor: pointer; }
.nm-btn-cancel:hover { background: #f8fafc; }
.nm-btn-save { font-size: 13px; font-weight: 700; color: #fff; background: #6366f1; border: none; border-radius: 8px; padding: 8px 20px; cursor: pointer; }
.nm-btn-save:hover { background: #4f46e5; }
.nm-btn-save:disabled { opacity: .6; cursor: not-allowed; }

/* ── Drawer ── */
#drawer-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.3); z-index: 200; backdrop-filter: blur(2px); }
#drawer-backdrop.open { display: block; }
#drawer {
    position: fixed; top: 0; right: -400px; width: 360px; height: 100%;
    background: #fff; z-index: 201; box-shadow: -8px 0 32px rgba(0,0,0,.12);
    transition: right .28s cubic-bezier(.4,0,.2,1); display: flex; flex-direction: column;
}
#drawer.open { right: 0; }
#drawer-header { padding: 20px 20px 16px; flex-shrink: 0; }
#drawer-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; margin-bottom: 10px; }
#drawer-customer { font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.2; }
#drawer-service { font-size: 13px; color: #64748b; margin-top: 3px; }
#drawer-body { padding: 0 20px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 0; }
.dr-divider { height: 1px; background: #f1f5f9; margin: 12px 0; }
.dr-row { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #374151; padding: 6px 0; }
.dr-row svg { color: #94a3b8; flex-shrink: 0; }
.dr-label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
.dr-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.dr-act-btn { display: inline-flex; align-items: center; gap: 4px; color: #fff; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 7px; border: none; cursor: pointer; transition: opacity .15s; }
.dr-act-btn:hover { opacity: .85; }
.dr-no-action { font-size: 12px; color: #94a3b8; font-style: italic; }
#drawer-footer { padding: 14px 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
#drawer-close-btn { font-size: 12px; color: #64748b; background: none; border: 1px solid #e2e8f0; border-radius: 7px; padding: 6px 12px; cursor: pointer; }
#drawer-close-btn:hover { background: #f8fafc; }
#drawer-edit-btn { font-size: 12px; font-weight: 700; color: #6366f1; background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 7px; padding: 6px 14px; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 4px; }
#drawer-edit-btn:hover { background: #e0e7ff; }

@keyframes toastIn { from{opacity:0;transform:translateX(-50%) translateY(10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
</style>
</head>
<body>
<div id="app">

    {{-- ── Topbar ── --}}
    <div id="topbar">
        <div class="tb-nav">
            <button class="tb-icon-btn" onclick="cgPrev()" title="Anterior">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button class="tb-icon-btn" onclick="cgNext()" title="Próximo">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <button class="tb-btn" onclick="cgToday()">Hoje</button>
        <div class="tb-sep"></div>
        <span id="tb-title">—</span>
        <div class="tb-sep"></div>

        <div class="tb-nav">
            <button class="tb-btn active" id="btn-week"  onclick="cgSwitch('week',this)">Semana</button>
            <button class="tb-btn"        id="btn-day"   onclick="cgSwitch('day',this)">Dia</button>
            <button class="tb-btn"        id="btn-month" onclick="cgSwitch('month',this)">Mês</button>
            <button class="tb-btn"        id="btn-list"  onclick="cgSwitch('list',this)">Lista</button>
        </div>

        <div class="tb-sep"></div>

        <button class="tb-icon-btn" onclick="cgRender()" title="Atualizar">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>

        <button class="tb-btn" style="background:#6366f1;border-color:#6366f1;color:#fff;margin-left:4px"
            onclick="openNewModal('','')">+ Agendamento</button>
    </div>

    {{-- ── Stats bar ── --}}
    <div id="statsbar">
        <div class="stat-pill" style="background:#fef3c7;border-color:#fde68a;color:#92400e"><span class="stat-dot" style="background:#f59e0b"></span><span id="stat-pending">0</span> Agendados</div>
        <div class="stat-pill" style="background:#dbeafe;border-color:#bfdbfe;color:#1e40af"><span class="stat-dot" style="background:#3b82f6"></span><span id="stat-confirmed">0</span> Confirmados</div>
        <div class="stat-pill" style="background:#ede9fe;border-color:#ddd6fe;color:#5b21b6"><span class="stat-dot" style="background:#8b5cf6"></span><span id="stat-arrived">0</span> Chegaram</div>
        <div class="stat-pill" style="background:#ffedd5;border-color:#fed7aa;color:#9a3412"><span class="stat-dot" style="background:#f97316"></span><span id="stat-inservice">0</span> Em atend.</div>
        <div class="stat-pill" style="background:#dcfce7;border-color:#bbf7d0;color:#166534"><span class="stat-dot" style="background:#10b981"></span><span id="stat-completed">0</span> Concluídos</div>
        <div class="stat-pill" style="background:#fee2e2;border-color:#fecaca;color:#991b1b"><span class="stat-dot" style="background:#ef4444"></span><span id="stat-cancelled">0</span> Cancelados</div>
        <span id="stats-total"></span>
    </div>

    {{-- ── Calendar ── --}}
    <div id="cal-wrap"></div>

</div>

{{-- ── New Appointment Modal ── --}}
<div id="nm-backdrop" onclick="nmBackdropClick(event)">
    <div id="nm-modal">
        <div id="nm-header">
            <span id="nm-title">Novo Agendamento</span>
            <button id="nm-close" onclick="closeNewModal()">✕</button>
        </div>
        <div id="nm-body">
            <div class="nm-row">
                <div class="nm-group"><label class="nm-label">Data<span>*</span></label><input type="date" id="nm-date" class="nm-input"></div>
                <div class="nm-group"><label class="nm-label">Início<span>*</span></label><input type="time" id="nm-start" class="nm-input" step="900" oninput="nmAutoEnd()"></div>
                <div class="nm-group"><label class="nm-label">Término<span>*</span></label><input type="time" id="nm-end" class="nm-input" step="900"></div>
            </div>
            <div class="nm-divider"></div>
            <div class="nm-group">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <label class="nm-label">Cliente<span>*</span></label>
                    <button type="button" onclick="nmToggleQuick()" style="font-size:11px;font-weight:700;color:#6366f1;background:none;border:none;cursor:pointer;padding:0">+ Novo cliente</button>
                </div>
                <select id="nm-customer" class="nm-select" onchange="nmOnCustomerChange()"><option value="">Selecione um cliente...</option></select>
            </div>
            <div id="nm-quick-wrap">
                <div class="nm-quick-title">Cadastro rápido de cliente</div>
                <div class="nm-quick-row"><div class="nm-group"><label class="nm-label">Nome<span>*</span></label><input type="text" id="nm-q-name" class="nm-input" placeholder="Nome completo"></div></div>
                <div class="nm-quick-row">
                    <div class="nm-group"><label class="nm-label">Telefone</label><input type="tel" id="nm-q-phone" class="nm-input" placeholder="(11) 99999-9999"></div>
                    <div class="nm-group"><label class="nm-label">E-mail</label><input type="email" id="nm-q-email" class="nm-input" placeholder="email@exemplo.com"></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" onclick="nmCancelQuick()" class="nm-btn-cancel" style="font-size:12px;padding:6px 12px">Cancelar</button>
                    <button type="button" onclick="nmSaveQuick()" id="nm-q-save" style="font-size:12px;font-weight:700;color:#fff;background:#6366f1;border:none;border-radius:7px;padding:6px 14px;cursor:pointer">Salvar cliente</button>
                </div>
            </div>
            <div class="nm-group"><label class="nm-label">Serviço<span>*</span></label><select id="nm-service" class="nm-select" onchange="nmOnServiceChange()"><option value="">Selecione um serviço...</option></select></div>
            <div class="nm-group"><label class="nm-label">Profissional<span>*</span></label><select id="nm-professional" class="nm-select"><option value="">Selecione um profissional...</option></select></div>
            <div class="nm-group">
                <label class="nm-label">Status<span>*</span></label>
                <select id="nm-status" class="nm-select">
                    <option value="pending">Agendado</option><option value="confirmed">Confirmado</option>
                    <option value="arrived">Chegou</option><option value="in_service">Em atendimento</option>
                    <option value="completed">Concluído</option><option value="cancelled">Cancelado</option>
                    <option value="no_show">Não compareceu</option>
                </select>
            </div>
            <div class="nm-group"><label class="nm-label">Observações</label><textarea id="nm-notes" class="nm-textarea" placeholder="Observações opcionais..."></textarea></div>
        </div>
        <div id="nm-footer">
            <button class="nm-btn-cancel" onclick="closeNewModal()">Cancelar</button>
            <button class="nm-btn-save" id="nm-save-btn" onclick="nmSubmit()">Salvar agendamento</button>
        </div>
    </div>
</div>

{{-- ── Drawer ── --}}
<div id="drawer-backdrop" onclick="closeDrawer()"></div>
<div id="drawer">
    <div id="drawer-header">
        <div id="drawer-status-badge"></div>
        <div id="drawer-customer"></div>
        <div id="drawer-service"></div>
    </div>
    <div id="drawer-body">
        <div class="dr-row">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span id="dr-datetime"></span>
        </div>
        <div class="dr-row">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span id="dr-professional"></span>
        </div>
        <div class="dr-row" id="dr-notes-row" style="display:none">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            <span id="dr-notes" style="color:#64748b;font-style:italic"></span>
        </div>
        <div class="dr-divider"></div>
        <div class="dr-label">Alterar status</div>
        <div class="dr-actions" id="dr-act-btns"></div>
    </div>
    <div id="drawer-footer">
        <button id="drawer-close-btn" onclick="closeDrawer()">Fechar</button>
        <a id="drawer-edit-btn" href="#">Editar <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></a>
    </div>
</div>

<script>
const EVENTS_URL         = '{{ $eventsUrl }}';
const STATUS_URL         = '{{ $statusUrl }}';
const RESCHEDULE_URL     = '{{ $rescheduleUrl }}';
const OPTIONS_URL        = '{{ $optionsUrl }}';
const STORE_URL          = '{{ $storeUrl }}';
const QUICK_CUSTOMER_URL = '{{ $quickCustomerUrl }}';
const CSRF               = '{{ $csrfToken }}';

/* ── API ── */
async function apiFetch(url, method, body) {
    const r = await fetch(url, {
        method,
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
        body: body ? JSON.stringify(body) : undefined,
    });
    const data = await r.json();
    if (!r.ok || data.error) throw new Error(data.error || r.status);
    return data;
}

/* ── Toast ── */
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:${type==='error'?'#ef4444':'#10b981'};color:#fff;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2);animation:toastIn .2s ease`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

/* ── Stats ── */
function updateStats(events) {
    const c = { pending:0, confirmed:0, arrived:0, in_service:0, completed:0, cancelled:0, no_show:0 };
    events.forEach(e => { const s = e.extendedProps?.status; if (s && c[s] !== undefined) c[s]++; });
    document.getElementById('stat-pending').textContent   = c.pending;
    document.getElementById('stat-confirmed').textContent = c.confirmed;
    document.getElementById('stat-arrived').textContent   = c.arrived;
    document.getElementById('stat-inservice').textContent = c.in_service;
    document.getElementById('stat-completed').textContent = c.completed;
    document.getElementById('stat-cancelled').textContent = c.cancelled;
    const total = Object.values(c).reduce((a,b) => a+b, 0);
    document.getElementById('stats-total').textContent = total + ' agendamento' + (total !== 1 ? 's' : '');
}

/* ── Status constants ── */
const STATUS_LABELS = { pending:'Agendado', confirmed:'Confirmado', arrived:'Chegou', in_service:'Em atendimento', completed:'Concluído', cancelled:'Cancelado', no_show:'Não compareceu' };
const STATUS_BG     = { pending:'#fef3c7', confirmed:'#dbeafe', arrived:'#ede9fe', in_service:'#ffedd5', completed:'#dcfce7', cancelled:'#fee2e2', no_show:'#f1f5f9' };
const STATUS_TEXT   = { pending:'#92400e', confirmed:'#1e40af', arrived:'#5b21b6', in_service:'#9a3412', completed:'#166534', cancelled:'#991b1b', no_show:'#374151' };
const STATUS_ALL = [
    ['pending',    '#f59e0b', 'Agendado'],
    ['confirmed',  '#3b82f6', 'Confirmado'],
    ['arrived',    '#8b5cf6', 'Chegou'],
    ['in_service', '#f97316', 'Em atendimento'],
    ['completed',  '#10b981', 'Concluído'],
    ['cancelled',  '#ef4444', 'Cancelado'],
    ['no_show',    '#6b7280', 'Não compareceu'],
];

/* ── Drawer ── */
let currentEvent = null;

function openDrawer(props, id) {
    currentEvent = { id, ...props };
    const status = props.status;
    const badge  = document.getElementById('drawer-status-badge');
    badge.textContent = STATUS_LABELS[status] ?? status;
    badge.style.cssText = `display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;background:${STATUS_BG[status]};color:${STATUS_TEXT[status]};border:1px solid transparent;margin-bottom:10px`;
    document.getElementById('drawer-customer').textContent = props.customer;
    document.getElementById('drawer-service').textContent  = props.service;
    document.getElementById('dr-datetime').textContent     = props.date_fmt + '  ·  ' + props.start_time + ' – ' + props.end_time;
    document.getElementById('dr-professional').textContent = props.professional;
    document.getElementById('drawer-edit-btn').href        = props.edit_url;
    const notesRow = document.getElementById('dr-notes-row');
    if (props.notes) { document.getElementById('dr-notes').textContent = props.notes; notesRow.style.display = 'flex'; }
    else { notesRow.style.display = 'none'; }
    const available = STATUS_ALL.filter(([s]) => s !== status);
    document.getElementById('dr-act-btns').innerHTML = available
        .map(([s,c,l]) => `<button class="dr-act-btn" style="background:${c}" onclick="doStatus('${s}')">${l}</button>`).join('');
    document.getElementById('drawer-backdrop').classList.add('open');
    document.getElementById('drawer').classList.add('open');
}
function closeDrawer() {
    document.getElementById('drawer-backdrop').classList.remove('open');
    document.getElementById('drawer').classList.remove('open');
    currentEvent = null;
}
async function doStatus(status) {
    if (!currentEvent) return;
    await apiFetch(STATUS_URL, 'POST', { id: currentEvent.id, status });
    closeDrawer();
    cgRender();
}

/* ── Escape ── */
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeNewModal(); closeDrawer(); } });

/* ═══════════════════════════════════════════
   CUSTOM CALENDAR GRID
═══════════════════════════════════════════ */
const SLOTS = [];
for (let m = 6*60; m < 24*60; m += 30)
    SLOTS.push(`${String(Math.floor(m/60)).padStart(2,'0')}:${String(m%60).padStart(2,'0')}`);

const DOW  = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
const MONS = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];

let cgMode = 'week';
let cgDate = new Date();

function cgPrev() {
    if      (cgMode==='day')   cgDate.setDate(cgDate.getDate()-1);
    else if (cgMode==='week')  cgDate.setDate(cgDate.getDate()-7);
    else if (cgMode==='month') cgDate.setMonth(cgDate.getMonth()-1);
    else                       cgDate.setDate(cgDate.getDate()-30);
    cgRender();
}
function cgNext() {
    if      (cgMode==='day')   cgDate.setDate(cgDate.getDate()+1);
    else if (cgMode==='week')  cgDate.setDate(cgDate.getDate()+7);
    else if (cgMode==='month') cgDate.setMonth(cgDate.getMonth()+1);
    else                       cgDate.setDate(cgDate.getDate()+30);
    cgRender();
}
function cgToday() { cgDate = new Date(); cgRender(); }
function cgSwitch(mode, btn) {
    cgMode = mode;
    document.querySelectorAll('.tb-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    cgRender();
}

function fmtDate(d) {
    return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
}
function weekDays(ref) {
    const d = new Date(ref.getFullYear(), ref.getMonth(), ref.getDate());
    d.setDate(d.getDate() - d.getDay());
    return Array.from({length:7}, (_,i) => new Date(d.getFullYear(), d.getMonth(), d.getDate()+i));
}
function startSlot(timeStr) {
    const [h,m] = timeStr.split(':').map(Number);
    const min   = h*60 + (m >= 30 ? 30 : 0);
    return `${String(Math.floor(min/60)).padStart(2,'0')}:${String(min%60).padStart(2,'0')}`;
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

let _dragData  = null;
let _scrollRAF = null;

/* Auto-scroll while dragging near top/bottom of .cg-scroll */
document.addEventListener('dragover', e => {
    const scroll = document.querySelector('.cg-scroll');
    if (!scroll || !_dragData) return;
    const rect  = scroll.getBoundingClientRect();
    const ZONE  = 80;
    const SPEED = 10;
    if (_scrollRAF) { cancelAnimationFrame(_scrollRAF); _scrollRAF = null; }
    const y = e.clientY;
    if (y < rect.top + ZONE && scroll.scrollTop > 0) {
        const factor = (rect.top + ZONE - y) / ZONE;
        const tick = () => { scroll.scrollTop -= SPEED * factor; _scrollRAF = requestAnimationFrame(tick); };
        _scrollRAF = requestAnimationFrame(tick);
    } else if (y > rect.bottom - ZONE && scroll.scrollTop < scroll.scrollHeight - scroll.clientHeight) {
        const factor = (y - (rect.bottom - ZONE)) / ZONE;
        const tick = () => { scroll.scrollTop += SPEED * factor; _scrollRAF = requestAnimationFrame(tick); };
        _scrollRAF = requestAnimationFrame(tick);
    }
});
document.addEventListener('dragend', () => {
    if (_scrollRAF) { cancelAnimationFrame(_scrollRAF); _scrollRAF = null; }
    _dragData = null;
});

async function cgRender() {
    const today = fmtDate(new Date());

    if (cgMode === 'week' || cgMode === 'day') {
        const days  = cgMode === 'day'
            ? [new Date(cgDate.getFullYear(), cgDate.getMonth(), cgDate.getDate())]
            : weekDays(cgDate);
        const start = fmtDate(days[0]);
        const endD  = new Date(days[days.length-1]); endD.setDate(endD.getDate()+1);
        const end   = fmtDate(endD);

        // Update title
        if (cgMode === 'day') {
            const d = days[0];
            document.getElementById('tb-title').textContent =
                DOW[d.getDay()] + ', ' + d.getDate() + ' de ' + MONS[d.getMonth()] + ' de ' + d.getFullYear();
        } else {
            const s = days[0], e = days[6];
            document.getElementById('tb-title').textContent =
                s.getDate() + ' de ' + MONS[s.getMonth()] + ' – ' + e.getDate() + ' de ' + MONS[e.getMonth()] + ' de ' + e.getFullYear();
        }

        let events = [];
        try { events = await apiFetch(`${EVENTS_URL}?start=${start}&end=${end}`, 'GET'); } catch(e) { showToast('Erro: '+e.message,'error'); }
        updateStats(events);

        // Index by day → slot
        const idx = {};
        events.forEach(ev => {
            const d    = ev.start.substring(0,10);
            const slot = startSlot(ev.start.substring(11,16));
            if (!idx[d]) idx[d] = {};
            if (!idx[d][slot]) idx[d][slot] = [];
            idx[d][slot].push(ev);
        });

        // Now marker
        const now = new Date();
        const nowSlot = fmtDate(now) === today
            ? startSlot(`${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`)
            : null;

        // Build table
        let html = `<div class="cg-outer"><div class="cg-scroll"><table class="cg-table"><colgroup><col class="cg-col-time">`;
        days.forEach(() => html += `<col>`);
        html += `</colgroup><thead><tr class="cg-head-row"><th class="cg-th-time"></th>`;
        days.forEach(d => {
            const ds = fmtDate(d);
            const it = ds === today;
            html += `<th class="cg-th-day${it?' is-today':''}">
                <span class="cg-dow">${DOW[d.getDay()]}</span>
                <span class="cg-dnum${it?' is-today':''}">${d.getDate()}</span>
            </th>`;
        });
        html += `</tr></thead><tbody>`;

        SLOTS.forEach(slot => {
            const isHalf = slot.endsWith(':30');
            const isNow  = slot === nowSlot;
            html += `<tr class="cg-row${isHalf?' half':''}${isNow?' now-row':''}">
                <td class="cg-td-time">${isHalf ? '' : slot}</td>`;
            days.forEach(d => {
                const ds   = fmtDate(d);
                const it   = ds === today;
                const evs  = (idx[ds]||{})[slot] || [];
                html += `<td class="cg-td-cell${it?' is-today':''}" data-date="${ds}" data-time="${slot}"
                             onclick="cgCellClick(event,'${ds}','${slot}')">`;
                evs.forEach(ev => {
                    const p = ev.extendedProps;
                    html += `<div class="cg-card" style="background:${ev.backgroundColor}"
                                  draggable="true"
                                  data-id="${ev.id}"
                                  data-start="${esc(ev.start)}"
                                  data-end="${esc(ev.end)}"
                                  data-props="${esc(JSON.stringify(p))}"
                                  onclick="event.stopPropagation();cgOpenCard(this)"
                                  ondragstart="cgDragStart(event,this)">
                        <div class="cg-card-time">${p.start_time} – ${p.end_time}</div>
                        <div class="cg-card-customer">${esc(p.customer)}</div>
                        <div class="cg-card-service">${esc(p.service)}</div>
                        <div class="cg-card-prof">${esc(p.professional)}</div>
                    </div>`;
                });
                html += `</td>`;
            });
            html += `</tr>`;
        });

        html += `</tbody></table></div></div>`;
        document.getElementById('cal-wrap').innerHTML = html;

        // Wire drag-drop on cells
        document.querySelectorAll('.cg-td-cell').forEach(td => {
            td.addEventListener('dragover', e => { e.preventDefault(); td.classList.add('drag-over'); });
            td.addEventListener('dragleave', () => td.classList.remove('drag-over'));
            td.addEventListener('drop', e => cgDrop(e, td));
        });

        // Scroll to 07:00
        const scroll = document.querySelector('.cg-scroll');
        if (scroll) {
            const rowH = scroll.scrollHeight / SLOTS.length;
            scroll.scrollTop = rowH * 2; // 06:00 + 1h = 07:00
        }

    } else if (cgMode === 'month') {
        const yr  = cgDate.getFullYear();
        const mo  = cgDate.getMonth();
        document.getElementById('tb-title').textContent =
            MONS[mo].charAt(0).toUpperCase() + MONS[mo].slice(1) + ' de ' + yr;

        const first = new Date(yr, mo, 1);
        const last  = new Date(yr, mo+1, 0);
        const startD = new Date(first); startD.setDate(startD.getDate() - startD.getDay());
        const endD   = new Date(last);  endD.setDate(endD.getDate() + (6-endD.getDay()) + 1);

        let events = [];
        try { events = await apiFetch(`${EVENTS_URL}?start=${fmtDate(startD)}&end=${fmtDate(endD)}`, 'GET'); } catch(e) { showToast('Erro: '+e.message,'error'); }
        updateStats(events);

        const byDay = {};
        events.forEach(ev => { const d=ev.start.substring(0,10); if(!byDay[d]) byDay[d]=[]; byDay[d].push(ev); });

        let html = `<div class="cgm-outer"><table class="cgm-table"><thead><tr>`;
        DOW.forEach(d => html += `<th class="cgm-th">${d}</th>`);
        html += `</tr></thead><tbody>`;

        const cur = new Date(startD);
        while (cur < endD) {
            html += `<tr>`;
            for (let i=0; i<7; i++) {
                const ds = fmtDate(cur);
                const it = ds === today;
                const om = cur.getMonth() !== mo;
                const evs = byDay[ds] || [];
                html += `<td class="cgm-cell${om?' other-month':''}${it?' is-today':''}"
                              onclick="cgSwitch('day',document.getElementById('btn-day'));cgDate=new Date('${ds}T12:00:00');cgRender()">
                    <div class="cgm-dnum${it?' is-today':''}">${cur.getDate()}</div>`;
                evs.slice(0,3).forEach(ev => {
                    const p = ev.extendedProps;
                    html += `<span class="cgm-pill" style="background:${ev.backgroundColor}"
                                   data-id="${ev.id}" data-props="${esc(JSON.stringify(p))}"
                                   onclick="event.stopPropagation();cgOpenCard(this)">${esc(p.customer)}</span>`;
                });
                if (evs.length > 3) html += `<div class="cgm-more">+${evs.length-3} mais</div>`;
                html += `</td>`;
                cur.setDate(cur.getDate()+1);
            }
            html += `</tr>`;
        }
        html += `</tbody></table></div>`;
        document.getElementById('cal-wrap').innerHTML = html;

    } else { // list
        const start = fmtDate(cgDate);
        const endD  = new Date(cgDate); endD.setDate(endD.getDate()+30);
        document.getElementById('tb-title').textContent = 'Próximos 30 dias';

        let events = [];
        try { events = await apiFetch(`${EVENTS_URL}?start=${start}&end=${fmtDate(endD)}`, 'GET'); } catch(e) { showToast('Erro: '+e.message,'error'); }
        updateStats(events);

        const sorted = [...events].sort((a,b) => a.start.localeCompare(b.start));
        let html = `<div class="cgl-outer">`;
        let lastDate = '';
        sorted.forEach(ev => {
            const d = ev.start.substring(0,10);
            if (d !== lastDate) {
                const dd = new Date(d+'T12:00:00');
                html += `<div class="cgl-date-hdr">${dd.toLocaleDateString('pt-BR',{weekday:'long',day:'numeric',month:'long'})}</div>`;
                lastDate = d;
            }
            const p = ev.extendedProps;
            html += `<div class="cgl-item" data-id="${ev.id}" data-props="${esc(JSON.stringify(p))}" onclick="cgOpenCard(this)">
                <div class="cgl-dot" style="background:${ev.backgroundColor}"></div>
                <div class="cgl-body">
                    <div class="cgl-time">${p.start_time} – ${p.end_time}</div>
                    <div class="cgl-customer">${esc(p.customer)}</div>
                    <div class="cgl-meta">${esc(p.service)} · ${esc(p.professional)}</div>
                </div>
            </div>`;
        });
        if (!sorted.length) html += `<div class="cgl-empty">Nenhum agendamento encontrado.</div>`;
        html += `</div>`;
        document.getElementById('cal-wrap').innerHTML = html;
    }
}

function cgOpenCard(el) {
    openDrawer(JSON.parse(el.dataset.props), el.dataset.id);
}
function cgCellClick(ev, date, time) {
    if (ev.target.closest('.cg-card')) return;
    openNewModal(date, time);
}

/* Drag-and-drop */
function cgDragStart(ev, el) {
    _dragData = { id: el.dataset.id, start: el.dataset.start, end: el.dataset.end };
    ev.dataTransfer.effectAllowed = 'move';
}
async function cgDrop(ev, td) {
    ev.preventDefault();
    td.classList.remove('drag-over');
    if (!_dragData) return;
    const newDate = td.dataset.date;
    const newTime = td.dataset.time;
    const origStart = new Date(_dragData.start);
    const origEnd   = new Date(_dragData.end);
    const dur       = origEnd - origStart;
    const ns = new Date(`${newDate}T${newTime}:00`);
    const ne = new Date(ns.getTime() + dur);
    const localDT = d => fmtDate(d) + 'T'
        + String(d.getHours()).padStart(2,'0') + ':'
        + String(d.getMinutes()).padStart(2,'0') + ':00';
    try {
        await apiFetch(RESCHEDULE_URL, 'POST', { id: parseInt(_dragData.id), start: localDT(ns), end: localDT(ne) });
        showToast('Agendamento atualizado!');
        cgRender();
    } catch(e) {
        showToast('Erro ao mover: '+e.message, 'error');
    }
    _dragData = null;
}

/* ═══════════════════════════════════════════
   NEW APPOINTMENT MODAL
═══════════════════════════════════════════ */
let nmOptions = { customers:[], services:[], professionals:[] };
let nmOptionsLoaded = false;

async function openNewModal(date, time) {
    document.getElementById('nm-date').value  = date || '';
    document.getElementById('nm-start').value = time || '';
    document.getElementById('nm-end').value   = '';
    document.getElementById('nm-notes').value = '';
    document.getElementById('nm-status').value = 'pending';
    document.getElementById('nm-customer').value = '';
    document.getElementById('nm-service').value  = '';
    document.getElementById('nm-professional').value = '';
    document.getElementById('nm-quick-wrap').classList.remove('open');
    document.getElementById('nm-q-name').value  = '';
    document.getElementById('nm-q-phone').value = '';
    document.getElementById('nm-q-email').value = '';
    document.getElementById('nm-backdrop').classList.add('open');
    document.getElementById('nm-save-btn').disabled = false;

    if (!nmOptionsLoaded) {
        try { nmOptions = await apiFetch(OPTIONS_URL, 'GET'); nmOptionsLoaded = true; }
        catch(e) { showToast('Erro ao carregar opções: '+e.message,'error'); return; }
    }

    document.getElementById('nm-customer').innerHTML = '<option value="">Selecione um cliente...</option>' +
        nmOptions.customers.map(c=>`<option value="${c.id}">${esc(c.name)}${c.phone?' · '+c.phone:''}</option>`).join('');
    document.getElementById('nm-service').innerHTML = '<option value="">Selecione um serviço...</option>' +
        nmOptions.services.map(s=>`<option value="${s.id}" data-dur="${s.duration_minutes}">${esc(s.name)} (${s.duration_minutes}min)</option>`).join('');
    document.getElementById('nm-professional').innerHTML = '<option value="">Selecione um profissional...</option>' +
        nmOptions.professionals.map(p=>`<option value="${p.id}">${esc(p.name)}${p.specialty?' · '+p.specialty:''}</option>`).join('');
    nmAutoEnd();
}
function closeNewModal() { document.getElementById('nm-backdrop').classList.remove('open'); }
function nmBackdropClick(e) { if (e.target===document.getElementById('nm-backdrop')) closeNewModal(); }
function nmOnServiceChange() { nmAutoEnd(); }
function nmOnCustomerChange() { if (document.getElementById('nm-customer').value) document.getElementById('nm-quick-wrap').classList.remove('open'); }
function nmAutoEnd() {
    const sv = document.getElementById('nm-start').value;
    const opt = document.getElementById('nm-service').options[document.getElementById('nm-service').selectedIndex];
    const dur = opt ? parseInt(opt.dataset.dur||0) : 0;
    if (!sv||!dur) return;
    const [h,m] = sv.split(':').map(Number);
    const em = h*60+m+dur;
    document.getElementById('nm-end').value = `${String(Math.floor(em/60)%24).padStart(2,'0')}:${String(em%60).padStart(2,'0')}`;
}
function nmToggleQuick() {
    const w = document.getElementById('nm-quick-wrap');
    w.classList.toggle('open');
    if (w.classList.contains('open')) { document.getElementById('nm-customer').value=''; document.getElementById('nm-q-name').focus(); }
}
function nmCancelQuick() { document.getElementById('nm-quick-wrap').classList.remove('open'); }
async function nmSaveQuick() {
    const name  = document.getElementById('nm-q-name').value.trim();
    const phone = document.getElementById('nm-q-phone').value.trim();
    const email = document.getElementById('nm-q-email').value.trim();
    if (!name) { showToast('Informe o nome do cliente','error'); return; }
    const btn = document.getElementById('nm-q-save');
    btn.disabled = true; btn.textContent = 'Salvando...';
    try {
        const res = await apiFetch(QUICK_CUSTOMER_URL, 'POST', { name, phone, email });
        const c   = res.customer;
        document.getElementById('nm-customer').appendChild(new Option(`${c.name}${c.phone?' · '+c.phone:''}`, c.id, true, true));
        nmOptions.customers.push(c);
        document.getElementById('nm-quick-wrap').classList.remove('open');
        showToast('Cliente cadastrado!');
    } catch(e) { showToast('Erro: '+e.message,'error'); }
    finally { btn.disabled=false; btn.textContent='Salvar cliente'; }
}
async function nmSubmit() {
    const date  = document.getElementById('nm-date').value;
    const start = document.getElementById('nm-start').value;
    const end   = document.getElementById('nm-end').value;
    const cId   = document.getElementById('nm-customer').value;
    const sId   = document.getElementById('nm-service').value;
    const pId   = document.getElementById('nm-professional').value;
    const status= document.getElementById('nm-status').value;
    const notes = document.getElementById('nm-notes').value.trim();
    if (!date)  return showToast('Informe a data','error');
    if (!start) return showToast('Informe o horário de início','error');
    if (!end)   return showToast('Informe o horário de término','error');
    if (!cId)   return showToast('Selecione um cliente','error');
    if (!sId)   return showToast('Selecione um serviço','error');
    if (!pId)   return showToast('Selecione um profissional','error');
    const btn = document.getElementById('nm-save-btn');
    btn.disabled=true; btn.textContent='Salvando...';
    try {
        await apiFetch(STORE_URL, 'POST', { customer_id:parseInt(cId), service_id:parseInt(sId), professional_id:parseInt(pId), date, start_time:start, end_time:end, status, notes });
        closeNewModal();
        cgRender();
        showToast('Agendamento criado!');
    } catch(e) { showToast('Erro ao salvar: '+e.message,'error'); }
    finally { btn.disabled=false; btn.textContent='Salvar agendamento'; }
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', () => cgRender());
</script>
</body>
</html>
