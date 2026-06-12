<x-filament-panels::page>
<div> {{-- root único exigido pelo Livewire --}}

{{-- ══════════════════════════════════════════
     Calendário — sem iframe, direto na page
══════════════════════════════════════════ --}}
@push('styles')
<style>
/* ── CSS Variables — light & dark ── */
:root {
    --cg-bg:          #ffffff;
    --cg-bg-subtle:   #f8fafc;
    --cg-bg-muted:    #f1f5f9;
    --cg-border:      #e2e8f0;
    --cg-border-sub:  #f1f5f9;
    --cg-text:        #334155;
    --cg-text-strong: #0f172a;
    --cg-text-muted:  #64748b;
    --cg-text-subtle: #94a3b8;
    --cg-today-bg:    rgba(99,102,241,.03);
    --cg-shadow:      0 1px 3px rgba(0,0,0,.07),0 4px 12px rgba(0,0,0,.04);
}
html.dark {
    --cg-bg:          #1e293b;
    --cg-bg-subtle:   #0f172a;
    --cg-bg-muted:    #0f172a;
    --cg-border:      #334155;
    --cg-border-sub:  #1e293b;
    --cg-text:        #cbd5e1;
    --cg-text-strong: #f1f5f9;
    --cg-text-muted:  #94a3b8;
    --cg-text-subtle: #475569;
    --cg-today-bg:    rgba(99,102,241,.08);
    --cg-shadow:      0 1px 3px rgba(0,0,0,.3),0 4px 12px rgba(0,0,0,.2);
}

/* ── Container ── */
#cg-app, #cg-app * { box-sizing: border-box; }
#cg-app {
    display: flex; flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', sans-serif;
    font-size: 13px; color: var(--cg-text);
    background: transparent; border-radius: 12px; overflow: hidden;
}

/* ── Topbar ── */
#cg-topbar {
    background: var(--cg-bg); border-bottom: 1px solid var(--cg-border);
    padding: 10px 16px; display: flex; align-items: center; gap: 12px; flex-shrink: 0;
    border-radius: 12px 12px 0 0;
}
#cg-topbar .tb-nav { display: flex; align-items: center; gap: 4px; }
.tb-btn {
    border: 1px solid var(--cg-border); background: var(--cg-bg); border-radius: 7px;
    padding: 5px 12px; font-size: 12px; font-weight: 600; cursor: pointer;
    color: var(--cg-text-muted); transition: all .15s; line-height: 1.4;
}
.tb-btn:hover { background: var(--cg-bg-subtle); border-color: var(--cg-border); }
.tb-btn.active { background: #6366f1 !important; border-color: #6366f1 !important; color: #fff !important; }
.tb-icon-btn {
    border: 1px solid var(--cg-border); background: var(--cg-bg); border-radius: 7px;
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--cg-text-muted); transition: all .15s;
}
.tb-icon-btn:hover { background: var(--cg-bg-subtle); }
#tb-title { font-size: 15px; font-weight: 700; color: var(--cg-text-strong); flex: 1; text-align: center; }
.tb-sep { width: 1px; height: 20px; background: var(--cg-border); margin: 0 4px; }

/* ── Stats bar ── */
#cg-statsbar {
    background: var(--cg-bg); border-bottom: 1px solid var(--cg-border);
    padding: 8px 16px; display: flex; align-items: center; gap: 8px;
    flex-shrink: 0; overflow-x: auto;
}
.stat-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600;
    white-space: nowrap; border: 1px solid transparent;
}
.stat-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
#stats-total { color: var(--cg-text-muted); font-size: 11px; font-weight: 500; margin-left: auto; white-space: nowrap; }

/* ── Cal wrap ── */
#cg-cal-wrap { flex: 1; overflow: hidden; padding: 12px 16px; min-height: 0; }

/* ── Custom Grid (Week / Day) ── */
.cg-outer {
    height: 100%; background: var(--cg-bg); border-radius: 12px;
    box-shadow: var(--cg-shadow); overflow: hidden; display: flex; flex-direction: column;
}
.cg-scroll { flex: 1; overflow-y: auto; min-height: 0; }
.cg-head-fixed {
    flex-shrink: 0; overflow-y: hidden; scrollbar-gutter: stable;
    background: var(--cg-bg-subtle);
}
.cg-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.cg-col-time { width: 54px; }
.cg-head-row { }
.cg-th-time { background: var(--cg-bg-subtle); border-bottom: 2px solid var(--cg-border); width: 54px; padding: 0; }
.cg-th-day {
    background: var(--cg-bg-subtle); border-bottom: 2px solid var(--cg-border);
    border-left: 1px solid var(--cg-border-sub); padding: 8px 4px; text-align: center;
    font-size: 11px; color: var(--cg-text-muted); font-weight: 600;
    text-transform: uppercase; letter-spacing: .04em;
}
.cg-th-day.is-today { background: rgba(99,102,241,.08); }
.cg-dow { display: block; }
.cg-dnum {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    font-size: 13px; font-weight: 700; color: var(--cg-text); margin-top: 3px;
}
.cg-dnum.is-today { background: #6366f1; color: #fff; }
.cg-td-time {
    padding: 0 8px 0 4px; font-size: 10px; color: var(--cg-text-subtle);
    text-align: right; vertical-align: top; padding-top: 3px;
    border-right: 1px solid var(--cg-border); white-space: nowrap; width: 54px;
}
.cg-row { border-top: 1px solid var(--cg-border); }
.cg-row.half { border-top: 1px dashed var(--cg-border-sub); }
.cg-row.now-row > .cg-td-time { color: #f43f5e; font-weight: 800; }
.cg-row.now-row { border-top: 2px solid rgba(244,63,94,.5); }
.cg-td-cell {
    border-left: 1px solid var(--cg-border-sub); padding: 2px 3px;
    vertical-align: top; cursor: pointer; min-height: 22px;
}
.cg-td-cell:hover { background: rgba(99,102,241,.04); }
.cg-td-cell.is-today { background: var(--cg-today-bg); }
.cg-td-cell.drag-over { background: rgba(99,102,241,.1) !important; outline: 2px dashed #6366f1; outline-offset: -2px; }
.cg-card {
    border-radius: 6px; padding: 5px 8px; margin-bottom: 3px;
    cursor: grab; color: #fff; user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.cg-card:last-child { margin-bottom: 0; }
.cg-card:hover { filter: brightness(1.1); }
.cg-card-time { font-size: 10px; opacity: .85; white-space: nowrap; }
.cg-card-customer { font-size: 12px; font-weight: 700; }
.cg-card-service { font-size: 10px; opacity: .85; }
.cg-card-prof { font-size: 10px; opacity: .7; }

/* ── Month Grid ── */
.cgm-outer {
    height: 100%; background: var(--cg-bg); border-radius: 12px;
    box-shadow: var(--cg-shadow); overflow: auto; display: flex; flex-direction: column;
}
.cgm-table { width: 100%; border-collapse: collapse; flex: 1; }
.cgm-th {
    padding: 8px; font-size: 11px; font-weight: 700; color: var(--cg-text-muted);
    text-align: center; background: var(--cg-bg-subtle); border-bottom: 2px solid var(--cg-border);
    text-transform: uppercase; letter-spacing: .04em; position: sticky; top: 0; z-index: 5;
}
.cgm-cell { padding: 6px; border: 1px solid var(--cg-border-sub); vertical-align: top; cursor: pointer; min-height: 90px; }
.cgm-cell:hover { background: rgba(99,102,241,.04); }
.cgm-cell.is-today { background: var(--cg-today-bg); }
.cgm-cell.other-month { background: var(--cg-bg-subtle); color: var(--cg-text-subtle); }
.cgm-dnum {
    font-size: 12px; font-weight: 600; color: var(--cg-text-muted); margin-bottom: 4px;
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
.cgl-outer { height: 100%; background: var(--cg-bg); border-radius: 12px; box-shadow: var(--cg-shadow); overflow-y: auto; padding: 4px 0; }
.cgl-date-hdr { padding: 10px 16px 5px; font-size: 11px; font-weight: 700; color: var(--cg-text-muted); text-transform: uppercase; letter-spacing: .05em; border-top: 1px solid var(--cg-border-sub); }
.cgl-date-hdr:first-child { border-top: none; }
.cgl-item { display: flex; align-items: flex-start; gap: 12px; padding: 8px 16px; cursor: pointer; border-left: 4px solid transparent; margin: 1px 0; }
.cgl-item:hover { background: var(--cg-bg-subtle); }
.cgl-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
.cgl-body { flex: 1; }
.cgl-time { font-size: 10px; color: var(--cg-text-subtle); }
.cgl-customer { font-size: 13px; font-weight: 700; color: var(--cg-text-strong); }
.cgl-meta { font-size: 11px; color: var(--cg-text-muted); }
.cgl-empty { padding: 40px; text-align: center; color: var(--cg-text-subtle); font-size: 14px; }

/* ── New Appointment Modal ── */
#cg-nm-backdrop {
    display: none; position: fixed; inset: 0; z-index: 9000;
    background: rgba(15,23,42,.5); backdrop-filter: blur(3px);
    align-items: center; justify-content: center;
}
#cg-nm-backdrop.open { display: flex; }
#cg-nm-modal {
    background: var(--cg-bg); border-radius: 16px; width: 520px; max-width: calc(100vw - 32px);
    max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.3);
    display: flex; flex-direction: column; border: 1px solid var(--cg-border);
}
#cg-nm-modal * { box-sizing: border-box; }
#nm-header { padding: 20px 24px 0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
#nm-title { font-size: 16px; font-weight: 800; color: var(--cg-text-strong); }
#nm-close {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--cg-border);
    background: var(--cg-bg-subtle); cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--cg-text-muted); font-size: 16px; line-height: 1;
}
#nm-close:hover { background: var(--cg-bg-muted); }
#nm-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
.nm-row { display: flex; gap: 12px; }
.nm-group { display: flex; flex-direction: column; gap: 5px; flex: 1; }
.nm-label { font-size: 11px; font-weight: 700; color: var(--cg-text-muted); text-transform: uppercase; letter-spacing: .05em; }
.nm-label span { color: #ef4444; margin-left: 2px; }
.nm-input, .nm-select, .nm-textarea {
    border: 1px solid var(--cg-border) !important; border-radius: 8px; padding: 8px 12px;
    font-size: 13px; color: var(--cg-text-strong); background: var(--cg-bg) !important;
    width: 100%; outline: none; transition: border-color .15s; font-family: inherit;
}
.nm-input:focus, .nm-select:focus, .nm-textarea:focus { border-color: #6366f1 !important; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
.nm-textarea { resize: vertical; min-height: 72px; }
.nm-select { appearance: auto; }
.nm-divider { height: 1px; background: var(--cg-border-sub); margin: 4px 0; }
#nm-quick-wrap { background: var(--cg-bg-subtle); border: 1px solid var(--cg-border); border-radius: 10px; padding: 14px; display: none; flex-direction: column; gap: 10px; margin-top: 4px; }
#nm-quick-wrap.open { display: flex; }
.nm-quick-title { font-size: 12px; font-weight: 700; color: var(--cg-text); margin-bottom: 2px; }
.nm-quick-row { display: flex; gap: 8px; }
#nm-footer { padding: 14px 24px; border-top: 1px solid var(--cg-border-sub); display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; }
.nm-btn-cancel { font-size: 13px; color: var(--cg-text-muted); background: var(--cg-bg) !important; border: 1px solid var(--cg-border) !important; border-radius: 8px; padding: 8px 16px; cursor: pointer; }
.nm-btn-cancel:hover { background: var(--cg-bg-subtle) !important; }
.nm-btn-save { font-size: 13px; font-weight: 700; color: #fff !important; background: #6366f1 !important; border: none !important; border-radius: 8px; padding: 8px 20px; cursor: pointer; }
.nm-btn-save:hover { background: #4f46e5 !important; }
.nm-btn-save:disabled { opacity: .6; cursor: not-allowed; }

/* ── Drawer ── */
#cg-drawer-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.4); z-index: 8900; backdrop-filter: blur(2px); }
#cg-drawer-backdrop.open { display: block; }
#cg-drawer {
    position: fixed; top: 0; right: -400px; width: 360px; height: 100%;
    background: var(--cg-bg); border-left: 1px solid var(--cg-border);
    z-index: 8901; box-shadow: -8px 0 32px rgba(0,0,0,.15);
    transition: right .28s cubic-bezier(.4,0,.2,1); display: flex; flex-direction: column;
}
#cg-drawer * { box-sizing: border-box; }
#cg-drawer.open { right: 0; }
#drawer-header { padding: 20px 20px 16px; flex-shrink: 0; }
#drawer-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; margin-bottom: 10px; }
#drawer-customer { font-size: 18px; font-weight: 800; color: var(--cg-text-strong); line-height: 1.2; }
#drawer-service { font-size: 13px; color: var(--cg-text-muted); margin-top: 3px; }
#drawer-body { padding: 0 20px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 0; }
.dr-divider { height: 1px; background: var(--cg-border-sub); margin: 12px 0; }
.dr-row { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--cg-text); padding: 6px 0; }
.dr-row svg { color: var(--cg-text-subtle); flex-shrink: 0; }
.dr-label { font-size: 11px; font-weight: 600; color: var(--cg-text-subtle); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
.dr-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.dr-act-btn { display: inline-flex; align-items: center; gap: 4px; color: #fff !important; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 7px; border: none !important; cursor: pointer; transition: opacity .15s; }
.dr-act-btn:hover { opacity: .85; }
#drawer-footer { padding: 14px 20px; border-top: 1px solid var(--cg-border-sub); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
#drawer-close-btn { font-size: 12px; color: var(--cg-text-muted); background: transparent !important; border: 1px solid var(--cg-border) !important; border-radius: 7px; padding: 6px 12px; cursor: pointer; }
#drawer-close-btn:hover { background: var(--cg-bg-subtle) !important; }
#drawer-edit-btn { font-size: 12px; font-weight: 700; color: #6366f1 !important; background: rgba(99,102,241,.1) !important; border: 1px solid rgba(99,102,241,.2) !important; border-radius: 7px; padding: 6px 14px; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 4px; }
#drawer-edit-btn:hover { background: rgba(99,102,241,.18) !important; }

/* ── Busca de cliente ── */
.nm-cust-wrap { position: relative; }
#nm-customer-dropdown {
    display: none; position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 200;
    background: var(--cg-bg); border: 1px solid var(--cg-border); border-radius: 8px;
    max-height: 210px; overflow-y: auto;
    box-shadow: 0 6px 20px rgba(0,0,0,.15);
}
.nm-cust-item {
    padding: 9px 12px; cursor: pointer;
    border-bottom: 1px solid var(--cg-border-sub);
    transition: background .1s;
}
.nm-cust-item:last-child { border-bottom: none; }
.nm-cust-item:hover { background: rgba(99,102,241,.08); }
.nm-cust-name { font-size: 13px; font-weight: 700; color: var(--cg-text-strong); }
.nm-cust-meta { font-size: 11px; color: var(--cg-text-muted); margin-top: 2px; }
.nm-cust-empty { padding: 10px 12px; font-size: 13px; color: var(--cg-text-muted); }

@keyframes cgToastIn { from{opacity:0;transform:translateX(-50%) translateY(10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }

/* ── Recurring ── */
.nm-recur-toggle { display:flex; align-items:center; gap:8px; padding:10px 12px; background:var(--cg-bg-subtle); border:1px solid var(--cg-border); border-radius:8px; cursor:pointer; }
.nm-recur-toggle input[type=checkbox] { width:16px; height:16px; cursor:pointer; accent-color:#6366f1; flex-shrink:0; }
.nm-recur-toggle .nm-recur-label { font-size:13px; font-weight:600; color:var(--cg-text); flex:1; }
.nm-recur-toggle .nm-recur-plan { font-size:11px; color:#6366f1; white-space:nowrap; }
#nm-recur-options { display:none; flex-direction:column; gap:10px; padding:12px; background:var(--cg-bg-subtle); border:1px solid var(--cg-border); border-radius:0 0 8px 8px; border-top:none; }
#nm-recur-options.open { display:flex; }
.cg-recur-badge { font-size:9px; background:rgba(99,102,241,.18); color:#6366f1; border-radius:3px; padding:1px 4px; margin-top:2px; display:inline-block; }
.dr-recur-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:12px; font-weight:700; background:rgba(99,102,241,.1); color:#6366f1; margin-bottom:8px; }
.dr-cancel-series { display:flex; gap:6px; flex-wrap:wrap; }
.dr-cancel-btn { font-size:11px; font-weight:700; padding:5px 10px; border-radius:6px; border:1px solid #ef4444; color:#ef4444; background:transparent; cursor:pointer; }
.dr-cancel-btn:hover { background:#fee2e2; }

/* ── Responsivo ── */
@media (max-width: 767px) {
    /* Topbar: empilha em duas linhas */
    #cg-topbar { flex-wrap: wrap; padding: 8px 10px; gap: 6px; }
    #tb-title { font-size: 13px; order: -1; flex-basis: 100%; text-align: left; }
    .tb-sep { display: none; }
    .tb-btn { padding: 4px 8px; font-size: 11px; }
    .tb-icon-btn { width: 28px; height: 28px; }

    /* Stats bar */
    #cg-statsbar { padding: 5px 8px; gap: 5px; }
    .stat-pill { padding: 2px 6px; font-size: 10px; }

    /* Cal wrap */
    #cg-cal-wrap { padding: 6px; }

    /* Drawer: largura total em telas pequenas */
    #cg-drawer { width: 100vw; right: -100vw; border-left: none; }

    /* Modal novo agendamento: colunas para linhas */
    .nm-row { flex-direction: column; gap: 8px; }
    .nm-quick-row { flex-direction: column; gap: 8px; }
    #nm-footer { flex-direction: column-reverse; gap: 6px; }
    .nm-btn-save, .nm-btn-cancel { width: 100%; text-align: center; }
}

@media (min-width: 768px) and (max-width: 1023px) {
    /* Tablet: drawer levemente mais estreito */
    #cg-drawer { width: 320px; right: -340px; }
}
</style>
@endpush

{{-- ── Calendar container ── --}}
<div id="cg-app">

    {{-- Topbar --}}
    <div id="cg-topbar">
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
            onclick="cgOpenNewModal('','')">+ Agendamento</button>
    </div>

    {{-- Stats bar --}}
    <div id="cg-statsbar">
        <div class="stat-pill" style="background:#fef3c7;border-color:#fde68a;color:#92400e"><span class="stat-dot" style="background:#f59e0b"></span><span id="stat-pending">0</span> Agendados</div>
        <div class="stat-pill" style="background:#dbeafe;border-color:#bfdbfe;color:#1e40af"><span class="stat-dot" style="background:#3b82f6"></span><span id="stat-confirmed">0</span> Confirmados</div>
        <div class="stat-pill" style="background:#ede9fe;border-color:#ddd6fe;color:#5b21b6"><span class="stat-dot" style="background:#8b5cf6"></span><span id="stat-arrived">0</span> Chegaram</div>
        <div class="stat-pill" style="background:#ffedd5;border-color:#fed7aa;color:#9a3412"><span class="stat-dot" style="background:#f97316"></span><span id="stat-inservice">0</span> Em atend.</div>
        <div class="stat-pill" style="background:#dcfce7;border-color:#bbf7d0;color:#166534"><span class="stat-dot" style="background:#10b981"></span><span id="stat-completed">0</span> Concluídos</div>
        <div class="stat-pill" style="background:#fee2e2;border-color:#fecaca;color:#991b1b"><span class="stat-dot" style="background:#ef4444"></span><span id="stat-cancelled">0</span> Cancelados</div>
        <span id="stats-total"></span>
    </div>

    {{-- Grid area --}}
    <div id="cg-cal-wrap"></div>

</div>

@php
$nmSlots = [];
for ($m = 6 * 60; $m < 24 * 60; $m += 30) {
    $nmSlots[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
}
@endphp

{{-- ── New Appointment Modal ── --}}
<div id="cg-nm-backdrop" onclick="cgNmBackdropClick(event)">
    <div id="cg-nm-modal">
        <div id="nm-header">
            <span id="nm-title">Novo Agendamento</span>
            <button id="nm-close" onclick="cgCloseNewModal()">✕</button>
        </div>
        <div id="nm-body">
            <div class="nm-row">
                <div class="nm-group"><label class="nm-label">Data<span>*</span></label><input type="date" id="nm-date" class="nm-input"></div>
                <div class="nm-group">
                    <label class="nm-label">Início<span>*</span></label>
                    <select id="nm-start" class="nm-select" onchange="nmStartChanged()">
                        <option value="">--:--</option>
                        @foreach($nmSlots as $slot)
                            <option value="{{ $slot }}">{{ $slot }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="nm-group">
                    <label class="nm-label">Término<span>*</span></label>
                    <select id="nm-end" class="nm-select">
                        <option value="">--:--</option>
                        @foreach($nmSlots as $slot)
                            <option value="{{ $slot }}">{{ $slot }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="nm-divider"></div>
            <div class="nm-group">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <label class="nm-label">Cliente<span>*</span></label>
                    <button type="button" onclick="nmToggleQuick()" style="font-size:11px;font-weight:700;color:#6366f1;background:none;border:none;cursor:pointer;padding:0">+ Novo cliente</button>
                </div>
                <div class="nm-cust-wrap">
                    <input type="text" id="nm-customer-search" class="nm-input"
                           placeholder="Buscar por nome, CPF ou telefone…"
                           autocomplete="off"
                           oninput="nmSearchCustomer()"
                           onfocus="nmSearchCustomer()"
                           onblur="nmHideDropdown()">
                    <input type="hidden" id="nm-customer">
                    <div id="nm-customer-dropdown"></div>
                </div>
            </div>
            <div id="nm-quick-wrap">
                <div class="nm-quick-title">Cadastro rápido de cliente</div>
                <div class="nm-quick-row"><div class="nm-group"><label class="nm-label">Nome<span>*</span></label><input type="text" id="nm-q-name" class="nm-input" placeholder="Nome completo"></div></div>
                <div class="nm-quick-row">
                    <div class="nm-group"><label class="nm-label">Telefone<span>*</span></label><input type="tel" id="nm-q-phone" class="nm-input" placeholder="(11) 99999-9999"></div>
                    <div class="nm-group"><label class="nm-label">E-mail</label><input type="email" id="nm-q-email" class="nm-input" placeholder="email@exemplo.com"></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" onclick="nmCancelQuick()" class="nm-btn-cancel" style="font-size:12px;padding:6px 12px">Cancelar</button>
                    <button type="button" onclick="nmSaveQuick()" id="nm-q-save" style="font-size:12px;font-weight:700;color:#fff;background:#6366f1;border:none;border-radius:7px;padding:6px 14px;cursor:pointer">Salvar cliente</button>
                </div>
            </div>
            <div class="nm-group"><label class="nm-label">Serviço</label><select id="nm-service" class="nm-select" onchange="nmOnServiceChange()"><option value="">Selecione um serviço...</option></select></div>
            <div class="nm-group" id="nm-professional-group"><label class="nm-label">Profissional<span>*</span></label><select id="nm-professional" class="nm-select"><option value="">Selecione um profissional...</option></select></div>
            <div class="nm-group">
                <label class="nm-label">Status</label>
                <select id="nm-status" class="nm-select">
                    <option value="pending">Agendado</option><option value="confirmed">Confirmado</option>
                    <option value="arrived">Chegou</option><option value="in_service">Em atendimento</option>
                    <option value="completed">Concluído</option><option value="cancelled">Cancelado</option>
                    <option value="no_show">Não compareceu</option>
                </select>
            </div>
            <div class="nm-group"><label class="nm-label">Observações</label><textarea id="nm-notes" class="nm-textarea" placeholder="Observações opcionais..."></textarea></div>
            <div id="nm-recur-wrap">
                <div class="nm-recur-toggle" style="{{ $isPaidPlan ? '' : 'opacity:.55;cursor:not-allowed;pointer-events:none;user-select:none' }}" @if($isPaidPlan) onclick="nmToggleRecur()" @endif>
                    <input type="checkbox" id="nm-recur-check" @if(!$isPaidPlan) disabled @endif @if($isPaidPlan) onclick="event.stopPropagation();nmToggleRecur()" @endif>
                    <span class="nm-recur-label">Repetir semanalmente</span>
                    @if($isPaidPlan)
                        <span style="font-size:10px;color:var(--cg-text-subtle)">semanal</span>
                    @else
                        <span class="nm-recur-plan">Planos pagos</span>
                    @endif
                </div>
                @if($isPaidPlan)
                <div id="nm-recur-options">
                    <div class="nm-group">
                        <label class="nm-label">Modo de repetição</label>
                        <select id="nm-recur-mode" class="nm-select" onchange="nmRecurModeChange()">
                            <option value="count">Número de sessões</option>
                            <option value="end_date">Até a data</option>
                        </select>
                    </div>
                    <div class="nm-group" id="nm-recur-count-group">
                        <label class="nm-label">Quantidade de sessões<span>*</span></label>
                        <input type="number" id="nm-recur-count" class="nm-input" min="2" max="52" value="4" placeholder="Ex: 8">
                    </div>
                    <div class="nm-group" id="nm-recur-end-group" style="display:none">
                        <label class="nm-label">Repetir até<span>*</span></label>
                        <input type="date" id="nm-recur-end-date" class="nm-input">
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div id="nm-footer">
            <button class="nm-btn-cancel" onclick="cgCloseNewModal()">Cancelar</button>
            <button class="nm-btn-save" id="nm-save-btn" onclick="nmSubmit()">Salvar agendamento</button>
        </div>
    </div>
</div>

{{-- ── Drawer ── --}}
<div id="cg-drawer-backdrop" onclick="cgCloseDrawer()"></div>
<div id="cg-drawer">
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
        <div id="dr-recur-section" style="display:none">
            <div class="dr-divider"></div>
            <div class="dr-label">Série recorrente</div>
            <div class="dr-recur-badge">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Agendamento recorrente
            </div>
            <div class="dr-cancel-series">
                <button class="dr-cancel-btn" onclick="cgCancelSeries('this')">Cancelar este</button>
                <button class="dr-cancel-btn" onclick="cgCancelSeries('future')">Este e futuros</button>
            </div>
        </div>
    </div>
    <div id="drawer-footer">
        <button id="drawer-close-btn" onclick="cgCloseDrawer()">Fechar</button>
        <div style="display:flex;gap:8px;align-items:center">
            <button id="drawer-wa-btn" onclick="cgSendConfirmation()" title="Enviar confirmação via WhatsApp"
                style="display:none;font-size:12px;font-weight:700;color:#16a34a;background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);border-radius:7px;padding:6px 12px;cursor:pointer;align-items:center;gap:5px">
                <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.528 5.855L0 24l6.335-1.506A11.957 11.957 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818c-1.9 0-3.677-.514-5.2-1.41l-.373-.221-3.76.894.949-3.657-.243-.386A9.796 9.796 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182c5.43 0 9.818 4.388 9.818 9.818 0 5.43-4.388 9.818-9.818 9.818z"/></svg>
                <span id="drawer-wa-label">Enviar confirmação</span>
            </button>
            <a id="drawer-edit-btn" href="#">Editar <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></a>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ── URLs ── */
const EVENTS_URL            = '{{ route('calendario.events') }}';
const STATUS_URL            = '{{ route('calendario.status') }}';
const RESCHEDULE_URL        = '{{ route('calendario.reschedule') }}';
const OPTIONS_URL           = '{{ route('calendario.options') }}';
const STORE_URL             = '{{ route('calendario.store') }}';
const QUICK_CUSTOMER_URL    = '{{ route('calendario.quick-customer') }}';
const WA_LINK_URL           = '{{ route('calendario.wa-link') }}';
const RECURRING_URL         = '{{ route('calendario.recurring') }}';
const CANCEL_SERIES_URL     = '{{ route('calendario.cancel-series') }}';
const IS_PAID_PLAN          = {{ $isPaidPlan ? 'true' : 'false' }};
const CSRF                  = '{{ csrf_token() }}';

/* ── Container height ── */
(function fitCgApp() {
    const el = document.getElementById('cg-app');
    if (!el) return;
    const top   = el.getBoundingClientRect().top + window.scrollY;
    const avail = window.innerHeight - top - 20;
    el.style.height = Math.max(500, avail) + 'px';
})();
window.addEventListener('resize', () => {
    const el = document.getElementById('cg-app');
    if (!el) return;
    const top   = el.getBoundingClientRect().top + window.scrollY;
    const avail = window.innerHeight - top - 20;
    el.style.height = Math.max(500, avail) + 'px';
});

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
    t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:${type==='error'?'#ef4444':'#10b981'};color:#fff;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2);animation:cgToastIn .2s ease`;
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
const STATUS_ALL    = [
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

function cgOpenDrawer(props, id) {
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
        .map(([s,c,l]) => `<button class="dr-act-btn" style="background:${c}" onclick="cgDoStatus('${s}')">${l}</button>`).join('');
    const waBtn = document.getElementById('drawer-wa-btn');
    waBtn.style.display = props.customer_phone ? 'inline-flex' : 'none';
    document.getElementById('drawer-wa-label').textContent = 'Enviar confirmação';
    waBtn.disabled = false;
    document.getElementById('dr-recur-section').style.display = props.recurrence_group_id ? '' : 'none';
    document.getElementById('cg-drawer-backdrop').classList.add('open');
    document.getElementById('cg-drawer').classList.add('open');
}
function cgCloseDrawer() {
    document.getElementById('cg-drawer-backdrop').classList.remove('open');
    document.getElementById('cg-drawer').classList.remove('open');
    currentEvent = null;
}
async function cgSendConfirmation() {
    if (!currentEvent) return;
    const btn   = document.getElementById('drawer-wa-btn');
    const label = document.getElementById('drawer-wa-label');
    btn.disabled = true;
    label.textContent = 'Abrindo…';
    try {
        const data = await apiFetch(WA_LINK_URL, 'POST', { appointment_id: currentEvent.id });
        window.open(data.url, '_blank');
        label.textContent = 'Enviar confirmação';
        btn.disabled = false;
    } catch (e) {
        label.textContent = 'Enviar confirmação';
        btn.disabled = false;
        showToast(e.message || 'Erro ao gerar link.', 'error');
    }
}
async function cgDoStatus(status) {
    if (!currentEvent) return;
    await apiFetch(STATUS_URL, 'POST', { id: currentEvent.id, status });
    cgCloseDrawer();
    cgRender();
}

/* ── Escape ── */
document.addEventListener('keydown', e => { if (e.key === 'Escape') { cgCloseNewModal(); cgCloseDrawer(); } });

/* ═══════════════════════════════════════════
   CUSTOM CALENDAR GRID
═══════════════════════════════════════════ */
const SLOTS = [];
for (let m = 6*60; m < 24*60; m += 30)
    SLOTS.push(`${String(Math.floor(m/60)).padStart(2,'0')}:${String(m%60).padStart(2,'0')}`);

const DOW  = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
const MONS = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];

/* Em mobile, começa em 'day' para evitar grade com múltiplas colunas */
const cgIsMobile = () => window.innerWidth < 768;
let cgMode = cgIsMobile() ? 'day' : 'week';
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
    /* Em mobile, 'week' é substituída por 'day' para evitar grade larga */
    if (mode === 'week' && cgIsMobile()) mode = 'day';
    cgMode = mode;
    document.querySelectorAll('.tb-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    /* Sincronizar o botão correto quando houve redirecionamento */
    const modeMap = { week:'btn-week', day:'btn-day', month:'btn-month', list:'btn-list' };
    if (modeMap[mode]) document.getElementById(modeMap[mode])?.classList.add('active');
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
    return `${String(Math.floor(min/60)).padStart(2,'0')}:${String(min%60).padStart(2,'00')}`;
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

        const idx = {};
        events.forEach(ev => {
            const d    = ev.start.substring(0,10);
            const slot = startSlot(ev.start.substring(11,16));
            if (!idx[d]) idx[d] = {};
            if (!idx[d][slot]) idx[d][slot] = [];
            idx[d][slot].push(ev);
        });

        const now    = new Date();
        const nowSlot = fmtDate(now) === today
            ? startSlot(`${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`)
            : null;

        let colg = `<col class="cg-col-time">`;
        days.forEach(() => colg += `<col>`);

        let html = `<div class="cg-outer">`;
        html += `<div class="cg-head-fixed"><table class="cg-table"><colgroup>${colg}</colgroup><thead><tr class="cg-head-row"><th class="cg-th-time"></th>`;
        days.forEach(d => {
            const ds = fmtDate(d);
            const it = ds === today;
            html += `<th class="cg-th-day${it?' is-today':''}">
                <span class="cg-dow">${DOW[d.getDay()]}</span>
                <span class="cg-dnum${it?' is-today':''}">${d.getDate()}</span>
            </th>`;
        });
        html += `</tr></thead></table></div>`;
        html += `<div class="cg-scroll"><table class="cg-table"><colgroup>${colg}</colgroup><tbody>`;

        SLOTS.forEach(slot => {
            const isHalf = slot.endsWith(':30');
            const isNow  = slot === nowSlot;
            html += `<tr class="cg-row${isHalf?' half':''}${isNow?' now-row':''}">
                <td class="cg-td-time">${isHalf ? '' : slot}</td>`;
            days.forEach(d => {
                const ds  = fmtDate(d);
                const it  = ds === today;
                const evs = (idx[ds]||{})[slot] || [];
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
                        ${p.recurrence_group_id ? '<span class="cg-recur-badge">&#8635; recorrente</span>' : ''}
                    </div>`;
                });
                html += `</td>`;
            });
            html += `</tr>`;
        });

        html += `</tbody></table></div></div>`;
        document.getElementById('cg-cal-wrap').innerHTML = html;

        document.querySelectorAll('.cg-td-cell').forEach(td => {
            td.addEventListener('dragover', e => { e.preventDefault(); td.classList.add('drag-over'); });
            td.addEventListener('dragleave', () => td.classList.remove('drag-over'));
            td.addEventListener('drop', e => cgDrop(e, td));
        });

        const scroll = document.querySelector('.cg-scroll');
        if (scroll) {
            const rowH = scroll.scrollHeight / SLOTS.length;
            scroll.scrollTop = rowH * 2;
        }

    } else if (cgMode === 'month') {
        const yr  = cgDate.getFullYear();
        const mo  = cgDate.getMonth();
        document.getElementById('tb-title').textContent =
            MONS[mo].charAt(0).toUpperCase() + MONS[mo].slice(1) + ' de ' + yr;

        const first  = new Date(yr, mo, 1);
        const last   = new Date(yr, mo+1, 0);
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
                const ds  = fmtDate(cur);
                const it  = ds === today;
                const om  = cur.getMonth() !== mo;
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
        document.getElementById('cg-cal-wrap').innerHTML = html;

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
        document.getElementById('cg-cal-wrap').innerHTML = html;
    }
}

function cgOpenCard(el) {
    cgOpenDrawer(JSON.parse(el.dataset.props), el.dataset.id);
}
function cgCellClick(ev, date, time) {
    if (ev.target.closest('.cg-card')) return;
    cgOpenNewModal(date, time);
}

/* ── Drag-and-drop ── */
function cgDragStart(ev, el) {
    _dragData = { id: el.dataset.id, start: el.dataset.start, end: el.dataset.end };
    ev.dataTransfer.effectAllowed = 'move';
}
async function cgDrop(ev, td) {
    ev.preventDefault();
    td.classList.remove('drag-over');
    if (!_dragData) return;
    const newDate    = td.dataset.date;
    const newTime    = td.dataset.time;
    const origStart  = new Date(_dragData.start);
    const origEnd    = new Date(_dragData.end);
    const dur        = origEnd - origStart;
    const ns         = new Date(`${newDate}T${newTime}:00`);
    const ne         = new Date(ns.getTime() + dur);
    const localDT    = d => fmtDate(d) + 'T'
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

async function cgOpenNewModal(date, time) {
    document.getElementById('nm-date').value  = date || '';
    document.getElementById('nm-start').value = time || '';
    document.getElementById('nm-end').value   = '';
    document.getElementById('nm-notes').value = '';
    document.getElementById('nm-status').value = 'pending';
    document.getElementById('nm-customer').value        = '';
    document.getElementById('nm-customer-search').value = '';
    document.getElementById('nm-customer-dropdown').style.display = 'none';
    document.getElementById('nm-service').value  = '';
    document.getElementById('nm-professional').value = '';
    document.getElementById('nm-quick-wrap').classList.remove('open');
    document.getElementById('nm-q-name').value  = '';
    document.getElementById('nm-q-phone').value = '';
    document.getElementById('nm-q-email').value = '';
    document.getElementById('nm-save-btn').disabled = false;
    const recurChk = document.getElementById('nm-recur-check');
    if (recurChk) { recurChk.checked = false; }
    const recurOpts = document.getElementById('nm-recur-options');
    if (recurOpts) recurOpts.classList.remove('open');
    document.getElementById('cg-nm-backdrop').classList.add('open');

    if (!nmOptionsLoaded) {
        try { nmOptions = await apiFetch(OPTIONS_URL, 'GET'); nmOptionsLoaded = true; }
        catch(e) { showToast('Erro ao carregar opções: '+e.message,'error'); return; }
    }

    document.getElementById('nm-service').innerHTML = '<option value="">Selecione um serviço...</option>' +
        nmOptions.services.map(s=>`<option value="${s.id}" data-dur="${s.duration_minutes}">${esc(s.name)} (${s.duration_minutes}min)</option>`).join('');

    const profSel   = document.getElementById('nm-professional');
    const profGroup = document.getElementById('nm-professional-group');
    profSel.innerHTML = '<option value="">Selecione um profissional...</option>' +
        nmOptions.professionals.map(p=>`<option value="${p.id}">${esc(p.name)}${p.specialty?' · '+p.specialty:''}</option>`).join('');

    if (nmOptions.professionals.length <= 1) {
        profGroup.style.display = 'none';
        profSel.value = nmOptions.professionals.length === 1 ? nmOptions.professionals[0].id : '';
    } else {
        profGroup.style.display = '';
    }

    nmAutoEnd();
}
function cgCloseNewModal() { document.getElementById('cg-nm-backdrop').classList.remove('open'); }
function cgNmBackdropClick(e) { if (e.target===document.getElementById('cg-nm-backdrop')) cgCloseNewModal(); }
function nmOnServiceChange() { nmAutoEnd(); }

/* Atualiza as opções do select de término para só mostrar horários após o início */
function nmUpdateEndSlots() {
    const start  = document.getElementById('nm-start').value;
    const endSel = document.getElementById('nm-end');
    const prev   = endSel.value;

    endSel.innerHTML = '<option value="">--:--</option>';
    SLOTS.forEach(slot => {
        if (!start || slot > start) {
            const o = document.createElement('option');
            o.value = o.textContent = slot;
            if (slot === prev) o.selected = true;
            endSel.appendChild(o);
        }
    });

    /* Se o término anterior ficou inválido, limpa */
    if (start && prev && prev <= start) endSel.value = '';
}

function nmStartChanged() {
    nmUpdateEndSlots();
    nmAutoEnd();
}

function nmAutoEnd() {
    const sv  = document.getElementById('nm-start').value;
    const opt = document.getElementById('nm-service').options[document.getElementById('nm-service').selectedIndex];
    const dur = opt ? parseInt(opt.dataset.dur || 0) : 0;
    if (!sv || !dur) return;
    const [h, m] = sv.split(':').map(Number);
    const em  = h * 60 + m + dur;
    /* Arredonda o término para o slot de 30 min mais próximo (acima) */
    const snap = Math.ceil(em / 30) * 30;
    const hh   = String(Math.floor(snap / 60) % 24).padStart(2, '0');
    const mm   = String(snap % 60).padStart(2, '0');
    document.getElementById('nm-end').value = `${hh}:${mm}`;
}
/* ── Busca de cliente ── */
function nmSearchCustomer() {
    const raw = document.getElementById('nm-customer-search').value.trim();
    const q   = raw.toLowerCase();
    const dd  = document.getElementById('nm-customer-dropdown');

    /* Limpar seleção ao digitar */
    document.getElementById('nm-customer').value = '';

    const norm = s => (s || '').replace(/\D/g, '');
    const qNum = norm(q);

    const results = q.length === 0
        ? nmOptions.customers.slice(0, 8)
        : nmOptions.customers.filter(c =>
            (c.name     || '').toLowerCase().includes(q)        ||
            (qNum && norm(c.phone).includes(qNum))               ||
            (qNum && norm(c.document).includes(qNum))
          ).slice(0, 10);

    if (results.length === 0) {
        dd.innerHTML = '<div class="nm-cust-empty">Nenhum cliente encontrado.</div>';
    } else {
        dd.innerHTML = results.map(c => {
            const meta = [c.phone, c.document].filter(Boolean).join(' · ');
            return `<div class="nm-cust-item" onmousedown="nmSelectCustomer(${c.id},'${esc(c.name)}')" data-id="${c.id}">
                <div class="nm-cust-name">${esc(c.name)}</div>
                ${meta ? `<div class="nm-cust-meta">${esc(meta)}</div>` : ''}
            </div>`;
        }).join('');
    }
    dd.style.display = 'block';
}

function nmSelectCustomer(id, name) {
    document.getElementById('nm-customer').value        = id;
    document.getElementById('nm-customer-search').value = name;
    document.getElementById('nm-customer-dropdown').style.display = 'none';
    document.getElementById('nm-quick-wrap').classList.remove('open');
}

function nmHideDropdown() {
    /* onmousedown no item dispara antes do onblur — o timeout preserva o clique */
    setTimeout(() => { document.getElementById('nm-customer-dropdown').style.display = 'none'; }, 150);
}

function nmToggleQuick() {
    const w = document.getElementById('nm-quick-wrap');
    w.classList.toggle('open');
    if (w.classList.contains('open')) {
        document.getElementById('nm-customer').value        = '';
        document.getElementById('nm-customer-search').value = '';
        document.getElementById('nm-customer-dropdown').style.display = 'none';
        document.getElementById('nm-q-name').focus();
    }
}
function nmCancelQuick() { document.getElementById('nm-quick-wrap').classList.remove('open'); }
async function nmSaveQuick() {
    const name  = document.getElementById('nm-q-name').value.trim();
    const phone = document.getElementById('nm-q-phone').value.trim();
    const email = document.getElementById('nm-q-email').value.trim();
    if (!name)  { showToast('Informe o nome do cliente','error'); return; }
    if (!phone) { showToast('Informe o telefone do cliente','error'); return; }
    const btn = document.getElementById('nm-q-save');
    btn.disabled = true; btn.textContent = 'Salvando...';
    try {
        const res = await apiFetch(QUICK_CUSTOMER_URL, 'POST', { name, phone, email });
        const c   = res.customer;
        nmOptions.customers.unshift(c);
        nmSelectCustomer(c.id, c.name);
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
    if (!date)        return showToast('Informe a data','error');
    if (!start)       return showToast('Informe o horário de início','error');
    if (!end)         return showToast('Informe o horário de término','error');
    if (end <= start) return showToast('O término deve ser após o início','error');
    if (!cId)         return showToast('Selecione um cliente','error');
    const profVisible = document.getElementById('nm-professional-group').style.display !== 'none';
    if (profVisible && !pId) return showToast('Selecione um profissional','error');
    const btn = document.getElementById('nm-save-btn');

    const isRecur = IS_PAID_PLAN && document.getElementById('nm-recur-check')?.checked;
    if (isRecur) {
        const mode = document.getElementById('nm-recur-mode').value;
        const body = { customer_id:parseInt(cId), service_id: sId ? parseInt(sId) : null,
            professional_id: pId ? parseInt(pId) : null, date, start_time:start, end_time:end,
            status, notes, recurrence_mode: mode };
        if (mode === 'count') {
            const cnt = parseInt(document.getElementById('nm-recur-count').value || '0');
            if (!cnt || cnt < 2) return showToast('Informe a quantidade de sessões (mín. 2)','error');
            body.recurrence_count = cnt;
        } else {
            const endD = document.getElementById('nm-recur-end-date').value;
            if (!endD) return showToast('Informe a data final da série','error');
            body.recurrence_end_date = endD;
        }
        btn.disabled=true; btn.textContent='Criando série...';
        try {
            const res = await apiFetch(RECURRING_URL, 'POST', body);
            cgCloseNewModal();
            cgRender();
            showToast(res.message || 'Série criada!');
        } catch(e) { showToast('Erro ao salvar: '+e.message,'error'); }
        finally { btn.disabled=false; btn.textContent='Salvar agendamento'; }
        return;
    }

    btn.disabled=true; btn.textContent='Salvando...';
    try {
        await apiFetch(STORE_URL, 'POST', { customer_id:parseInt(cId), service_id: sId ? parseInt(sId) : null, professional_id:parseInt(pId), date, start_time:start, end_time:end, status, notes });
        cgCloseNewModal();
        cgRender();
        showToast('Agendamento criado!');
    } catch(e) { showToast('Erro ao salvar: '+e.message,'error'); }
    finally { btn.disabled=false; btn.textContent='Salvar agendamento'; }
}

/* ── Recurring ── */
function nmToggleRecur() {
    if (!IS_PAID_PLAN) { showToast('Disponível apenas nos planos pagos.', 'error'); return; }
    const chk  = document.getElementById('nm-recur-check');
    chk.checked = !chk.checked;
    const opts = document.getElementById('nm-recur-options');
    if (opts) opts.classList.toggle('open', chk.checked);
}
function nmRecurModeChange() {
    const mode = document.getElementById('nm-recur-mode').value;
    document.getElementById('nm-recur-count-group').style.display = mode === 'count'    ? '' : 'none';
    document.getElementById('nm-recur-end-group').style.display   = mode === 'end_date' ? '' : 'none';
}
async function cgCancelSeries(scope) {
    if (!currentEvent) return;
    const label = scope === 'future' ? 'este e os futuros' : 'este agendamento';
    if (!confirm('Cancelar ' + label + ' da série recorrente?')) return;
    try {
        const res = await apiFetch(CANCEL_SERIES_URL, 'POST', { appointment_id: parseInt(currentEvent.id), scope });
        showToast(res.cancelled + ' agendamento(s) cancelado(s).');
        cgCloseDrawer();
        cgRender();
    } catch(e) { showToast('Erro: ' + e.message, 'error'); }
}

/* ── Init ── */
/* Sincronizar botão ativo com o modo real (mobile começa em 'day') */
(function syncInitBtn() {
    const modeMap = { week:'btn-week', day:'btn-day', month:'btn-month', list:'btn-list' };
    document.querySelectorAll('.tb-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(modeMap[cgMode])?.classList.add('active');
})();
cgRender();

/* Ao redimensionar para mobile enquanto na view de semana, mudar para dia */
window.addEventListener('resize', () => {
    if (cgIsMobile() && cgMode === 'week') {
        cgSwitch('day', null);
    }
});
</script>
@endpush

</div> {{-- /root único --}}
</x-filament-panels::page>
