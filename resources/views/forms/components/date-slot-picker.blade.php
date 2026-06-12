@php
    $statePath   = $getStatePath();
    $slotOptions = $getSlotOptions();
    $ptDaysShort = ['DOM','SEG','TER','QUA','QUI','SEX','SÁB'];
    $ptMonths    = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $durations   = [30 => '30 min', 45 => '45 min', 60 => '1 hora', 90 => '1h30', 120 => '2 horas'];
@endphp

<style>
/* ── layout ── */
.dsp-root{display:grid;grid-template-columns:230px 1fr;gap:20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;color:#1e293b}
.dsp-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px}
.dsp-section-title{font-size:13px;font-weight:700;color:#374151;margin-bottom:12px}

/* ── calendário ── */
.dsp-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.dsp-cal-month{font-size:14px;font-weight:700;color:#1e293b}
.dsp-cal-arrow{background:none;border:1px solid #e2e8f0;border-radius:6px;width:26px;height:26px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:14px;line-height:1}
.dsp-cal-arrow:hover{background:#f8fafc}
.dsp-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px}
.dsp-cal-dh{text-align:center;font-size:10px;font-weight:700;color:#94a3b8;padding:4px 0;margin-bottom:2px}
.dsp-cal-cell{display:flex;align-items:center;justify-content:center;aspect-ratio:1;cursor:pointer;position:relative}
.dsp-cal-cell.empty{cursor:default}
.dsp-cal-num{width:80%;aspect-ratio:1;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;transition:background .12s}
.dsp-cal-cell:not(.empty):hover .dsp-cal-num{background:#f1f5f9}
.dsp-cal-cell.selected .dsp-cal-num{background:#0ea5e9;color:#fff;font-weight:700}
.dsp-cal-cell.has-slots .dsp-cal-num{background:#dcfce7;color:#166534}
.dsp-cal-cell.selected.has-slots .dsp-cal-num{background:#0ea5e9;color:#fff}

/* ── legenda ── */
.dsp-legend{display:flex;flex-direction:column;gap:6px;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9}
.dsp-legend-item{display:flex;align-items:center;gap:7px;font-size:11px;color:#64748b}
.dsp-legend-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0}

/* ── painel de horários ── */
.dsp-times-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;min-height:140px;color:#94a3b8;font-size:13px;text-align:center;gap:8px}
.dsp-times-empty svg{opacity:.35}
.dsp-times-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.dsp-time-btn{border:1px solid #e2e8f0;border-radius:8px;padding:9px 8px;font-size:13px;font-weight:600;cursor:pointer;background:#fff;color:#374151;text-align:center;transition:all .12s;white-space:nowrap;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.dsp-time-btn:hover{border-color:#0ea5e9;color:#0ea5e9;background:#f0f9ff}
.dsp-time-btn.on{background:#0ea5e9;border-color:#0ea5e9;color:#fff;box-shadow:0 2px 6px rgba(14,165,233,.3)}
.dsp-time-btn.on:hover{background:#0284c7;border-color:#0284c7}

/* ── mini-modal duração ── */
.dsp-dur-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;display:flex;align-items:center;justify-content:center}
.dsp-dur-box{background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.18);padding:22px 24px;min-width:260px;animation:dspPop .15s ease}
@keyframes dspPop{from{opacity:0;transform:scale(.93)}to{opacity:1;transform:scale(1)}}
.dsp-dur-title{font-size:14px;font-weight:700;color:#1e293b;margin-bottom:3px}
.dsp-dur-sub{font-size:12px;color:#94a3b8;margin-bottom:16px}
.dsp-dur-btns{display:flex;flex-wrap:wrap;gap:8px}
.dsp-dur-btn{border:1px solid #e2e8f0;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;background:#fff;color:#374151;transition:all .12s}
.dsp-dur-btn:hover{background:#0ea5e9;border-color:#0ea5e9;color:#fff}
.dsp-dur-cancel{margin-top:12px;width:100%;background:none;border:none;color:#94a3b8;font-size:12px;cursor:pointer;padding:6px}
.dsp-dur-cancel:hover{color:#64748b}

/* ── resumo chips ── */
.dsp-chips-wrap{margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9}
.dsp-chips-label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px}
.dsp-chips{display:flex;flex-wrap:wrap;gap:6px}
.dsp-chip{display:inline-flex;align-items:center;gap:5px;background:#f0f9ff;color:#0369a1;font-size:12px;font-weight:500;padding:4px 10px;border-radius:99px;border:1px solid #bae6fd}
.dsp-chip-rm{background:none;border:none;cursor:pointer;color:#7dd3fc;font-size:15px;line-height:1;padding:0}
.dsp-chip-rm:hover{color:#ef4444}
</style>

<div
    x-data="{
        statePath: @js($statePath),
        slotOptions: @js($slotOptions),
        durations: @js(array_map(fn($k,$v)=>['value'=>$k,'label'=>$v], array_keys($durations), array_values($durations))),
        ptMonths: @js($ptMonths),
        year: new Date().getFullYear(),
        month: new Date().getMonth(),
        selectedDate: null,
        slots: {},
        pendingSlot: null,

        init() {
            let raw = $wire.get(this.statePath);
            if (raw && raw !== '{}') {
                try { this.slots = JSON.parse(raw); } catch(e) { this.slots = {}; }
            }
        },

        toMin(t) { let [h,m]=t.split(':').map(Number); return h*60+m; },

        get daysInMonth() { return new Date(this.year, this.month+1, 0).getDate(); },
        get firstDayOfWeek() { return new Date(this.year, this.month, 1).getDay(); },
        get monthLabel() { return this.ptMonths[this.month] + ' ' + this.year; },

        prevMonth() {
            if(this.month===0){this.month=11;this.year--;}else this.month--;
            this.selectedDate=null;
        },
        nextMonth() {
            if(this.month===11){this.month=0;this.year++;}else this.month++;
            this.selectedDate=null;
        },

        dateKey(day) {
            return this.year+'-'+String(this.month+1).padStart(2,'0')+'-'+String(day).padStart(2,'0');
        },

        selectDate(day) {
            let k=this.dateKey(day);
            this.selectedDate=k;
            if(!this.slots[k]) this.slots={...this.slots,[k]:[]};
        },

        hasSlots(day) {
            let k=this.dateKey(day);
            return this.slots[k]&&this.slots[k].length>0;
        },

        isSelected(day) { return this.selectedDate===this.dateKey(day); },

        findSlot(time) {
            if(!this.selectedDate) return null;
            return (this.slots[this.selectedDate]||[]).find(s=>s.time===time)||null;
        },

        isSlotOn(time) { return !!this.findSlot(time); },

        isSlotBlocked(time) {
            if(!this.selectedDate) return false;
            let tMin=this.toMin(time);
            for(let s of (this.slots[this.selectedDate]||[])) {
                let start=this.toMin(s.time), end=start+s.duration;
                if(tMin>start&&tMin<end) return true;
            }
            return false;
        },

        slotLabel(time) {
            let s=this.findSlot(time);
            if(!s) return time;
            let d=s.duration;
            let dl=d>=60?(d===60?'1h':d===90?'1h30':(d/60)+'h'):d+'m';
            return time+' · '+dl;
        },

        clickSlot(time) {
            if(!this.selectedDate||this.isSlotBlocked(time)) return;
            if(this.isSlotOn(time)) {
                let arr=(this.slots[this.selectedDate]||[]).filter(s=>s.time!==time);
                this.slots={...this.slots,[this.selectedDate]:arr};
                this.syncToWire();
            } else {
                this.pendingSlot=time;
            }
        },

        confirmDuration(duration) {
            if(!this.selectedDate||!this.pendingSlot) return;
            let arr=[...(this.slots[this.selectedDate]||[])];
            arr.push({time:this.pendingSlot,duration:duration});
            arr.sort((a,b)=>a.time.localeCompare(b.time));
            this.slots={...this.slots,[this.selectedDate]:arr};
            this.pendingSlot=null;
            this.syncToWire();
        },

        cancelPending() { this.pendingSlot=null; },

        removeDate(k) {
            let s={...this.slots}; delete s[k]; this.slots=s;
            if(this.selectedDate===k) this.selectedDate=null;
            this.syncToWire();
        },

        syncToWire() { $wire.set(this.statePath, JSON.stringify(this.slots)); },

        get configuredDates() {
            return Object.keys(this.slots).filter(k=>this.slots[k]&&this.slots[k].length>0).sort();
        },

        labelDate(dk) {
            let [y,m,d]=dk.split('-').map(Number);
            let mn=['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
            return String(d).padStart(2,'0')+'/'+mn[m-1];
        },

        selectedLabel() {
            if(!this.selectedDate) return '';
            let [y,m,d]=this.selectedDate.split('-').map(Number);
            let mn=['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
            return String(d).padStart(2,'0')+'/'+mn[m-1]+'/'+y;
        },

        get visibleSlots() {
            return this.slotOptions.filter(s=>!this.isSlotBlocked(s));
        },
    }"
    x-init="init()"
>
    <div class="dsp-root">

        {{-- ── COLUNA ESQUERDA: Calendário ── --}}
        <div>
            <div class="dsp-section-title">Selecionar Data</div>
            <div class="dsp-card">
                {{-- Navegação --}}
                <div class="dsp-cal-nav">
                    <span class="dsp-cal-month" x-text="monthLabel"></span>
                    <div style="display:flex;gap:4px">
                        <button type="button" class="dsp-cal-arrow" x-on:click="prevMonth()">‹</button>
                        <button type="button" class="dsp-cal-arrow" x-on:click="nextMonth()">›</button>
                    </div>
                </div>

                {{-- Grade --}}
                <div class="dsp-cal-grid">
                    {{-- Cabeçalho --}}
                    @foreach($ptDaysShort as $dh)
                        <div class="dsp-cal-dh">{{ $dh }}</div>
                    @endforeach

                    {{-- Células vazias --}}
                    <template x-for="i in firstDayOfWeek" :key="'e'+i">
                        <div class="dsp-cal-cell empty"></div>
                    </template>

                    {{-- Dias --}}
                    <template x-for="day in daysInMonth" :key="day">
                        <div
                            class="dsp-cal-cell"
                            :class="{ 'selected': isSelected(day), 'has-slots': hasSlots(day) }"
                            x-on:click="selectDate(day)"
                        >
                            <div class="dsp-cal-num" x-text="day"></div>
                        </div>
                    </template>
                </div>

                {{-- Legenda --}}
                <div class="dsp-legend">
                    <div class="dsp-legend-item">
                        <div class="dsp-legend-dot" style="background:#dcfce7;border:1px solid #86efac"></div>
                        <span>Com horários</span>
                    </div>
                    <div class="dsp-legend-item">
                        <div class="dsp-legend-dot" style="background:#0ea5e9"></div>
                        <span>Selecionado</span>
                    </div>
                </div>
            </div>

            {{-- Chips de datas configuradas --}}
            <div class="dsp-chips-wrap" x-show="configuredDates.length > 0" x-transition>
                <div class="dsp-chips-label">Configuradas</div>
                <div class="dsp-chips">
                    <template x-for="dk in configuredDates" :key="dk">
                        <span class="dsp-chip">
                            <span x-text="labelDate(dk) + ' · ' + (slots[dk] ? slots[dk].length : 0) + ' slots'"></span>
                            <button type="button" class="dsp-chip-rm" x-on:click="removeDate(dk)">×</button>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── COLUNA DIREITA: Horários ── --}}
        <div>
            <div class="dsp-section-title">Selecionar Horários</div>
            <div class="dsp-card" style="min-height:200px">

                {{-- Placeholder quando nenhuma data selecionada --}}
                <div class="dsp-times-empty" x-show="selectedDate === null">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Selecione um dia<br>no calendário</span>
                </div>

                {{-- Grade de horários --}}
                <div x-show="selectedDate !== null" x-transition>
                    <div style="font-size:12px;color:#94a3b8;margin-bottom:10px" x-text="selectedLabel()"></div>
                    <div class="dsp-times-grid">
                        <template x-for="slot in visibleSlots" :key="slot">
                            <button
                                type="button"
                                class="dsp-time-btn"
                                :class="isSlotOn(slot) ? 'on' : ''"
                                x-on:click="clickSlot(slot)"
                                x-text="slotLabel(slot)"
                            ></button>
                        </template>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Mini-modal de duração ── --}}
    <div class="dsp-dur-overlay" x-show="pendingSlot !== null" x-transition x-on:click.self="cancelPending()">
        <div class="dsp-dur-box">
            <div class="dsp-dur-title">Duração do atendimento</div>
            <div class="dsp-dur-sub">Início: <strong x-text="pendingSlot"></strong></div>
            <div class="dsp-dur-btns">
                @foreach($durations as $min => $lbl)
                    <button type="button" class="dsp-dur-btn" x-on:click="confirmDuration({{ $min }})">{{ $lbl }}</button>
                @endforeach
            </div>
            <button type="button" class="dsp-dur-cancel" x-on:click="cancelPending()">Cancelar</button>
        </div>
    </div>

</div>
