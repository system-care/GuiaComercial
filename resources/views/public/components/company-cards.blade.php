@php
    $emptyTitle = $emptyTitle ?? 'Nenhuma empresa encontrada';
    $emptyText = $emptyText ?? 'Tente buscar por outro serviço, cidade ou bairro.';
    $emptyActionLabel = $emptyActionLabel ?? 'Limpar filtros';
    $emptyActionUrl = $emptyActionUrl ?? url('/buscar');
    $secondaryActionLabel = $secondaryActionLabel ?? 'Voltar para a home';
    $secondaryActionUrl = $secondaryActionUrl ?? url('/');
@endphp

@if (empty($companies))
    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-center shadow-sm sm:p-10">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-xl font-black text-violet-700">GC</div>
        <h3 class="mt-4 text-xl font-black text-slate-950">{{ $emptyTitle }}</h3>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $emptyText }}</p>
        <div class="mt-6 grid gap-3 sm:mx-auto sm:max-w-md sm:grid-cols-2">
            <a href="{{ $emptyActionUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-600 px-5 py-3 text-sm font-black text-white hover:bg-violet-700">{{ $emptyActionLabel }}</a>
            <a href="{{ $secondaryActionUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">{{ $secondaryActionLabel }}</a>
        </div>
    </div>
@else
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($companies as $company)
            <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-xl">
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        @if ($company['logo_url'])
                            <img src="{{ $company['logo_url'] }}" alt="Logo {{ $company['name'] }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover ring-1 ring-slate-200">
                        @else
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl {{ $company['accent'] }} text-lg font-black">{{ $company['initials'] }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-2">
                                @if ($company['has_online_booking'])
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">Agenda online</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">Perfil publicado</span>
                                @endif
                            </div>
                            <h3 class="mt-3 truncate text-xl font-black text-slate-950">{{ $company['name'] }}</h3>
                            <p class="mt-1 text-sm font-semibold text-violet-700">{{ $company['category'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                @if (!empty($company['distance_label'] ?? null))
                                    <span class="font-semibold text-violet-600">{{ $company['distance_label'] }}</span>
                                    <span class="mx-1 text-slate-300">·</span>
                                @endif
                                {{ $company['location'] }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-5 line-clamp-3 text-sm leading-6 text-slate-600">{{ $company['description'] }}</p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @forelse ($company['services'] as $service)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $service }}</span>
                        @empty
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Serviços sob consulta</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-auto grid gap-2 border-t border-slate-100 bg-slate-50 p-4 sm:grid-cols-2">
                    <a href="{{ $company['profile_url'] }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-black text-slate-800 hover:border-violet-200 hover:text-violet-700">Ver perfil</a>
                    <a href="{{ $company['booking_url'] }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-600 px-4 py-3 text-center text-sm font-black text-white hover:bg-violet-700">Agendar</a>
                </div>
            </article>
        @endforeach
    </div>
@endif
