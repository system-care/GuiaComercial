@if ($hasGeo ?? false)
<div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-violet-700">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
    </svg>
    <span>Ordenando por proximidade ·
        <a href="{{ $removeUrl }}" class="underline hover:text-violet-900">Remover localização</a>
    </span>
</div>
@endif
