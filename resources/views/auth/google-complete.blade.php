<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar cadastro — Guia Comercial</title>
    <meta name="description" content="Complete seu cadastro no Guia Comercial.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="min-h-screen bg-slate-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('logo/logo.png') }}" alt="Guia Comercial" class="mx-auto h-10 w-auto">
                </a>
                <h1 class="mt-6 text-2xl font-black text-slate-900">Complete seu cadastro</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Conectado como <strong>{{ $google['email'] }}</strong>.<br>
                    Precisamos de mais alguns dados sobre sua empresa.
                </p>
            </div>

            @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <form method="POST" action="{{ route('auth.google.complete.save') }}" x-data="nicheSelector()" @submit.prevent="$el.submit()">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-slate-700">Nome da empresa <span class="text-red-500">*</span></label>
                            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
                                   placeholder="Ex: Clínica Bem Estar"
                                   class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                                   required>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700">WhatsApp <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="(11) 99999-9999"
                                   class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Segmentos do negócio <span class="text-red-500">*</span></label>
                            <p class="mt-0.5 text-xs text-slate-500">Selecione um ou mais segmentos da mesma categoria.</p>
                            <div class="mt-2 max-h-52 overflow-y-auto rounded-xl border border-slate-300 p-3 space-y-3">
                                @foreach($niches as $category)
                                <div>
                                    <p class="text-xs font-black text-slate-500 uppercase tracking-wide mb-1.5">{{ $category['name'] }}</p>
                                    <div class="space-y-1">
                                        @foreach($category['niches'] as $niche)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox"
                                                   name="business_niche_ids[]"
                                                   value="{{ $niche['id'] }}"
                                                   @checked(in_array($niche['id'], old('business_niche_ids', [])))
                                                   x-model="selected"
                                                   @change="onNicheChange({{ $niche['niche_category_id'] }}, {{ $niche['id'] }})"
                                                   :disabled="lockedCategory !== null && lockedCategory !== {{ $niche['niche_category_id'] }}"
                                                   class="rounded accent-violet-600 disabled:opacity-30">
                                            <span class="text-sm text-slate-700">{{ $niche['name'] }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="mt-6 w-full rounded-xl bg-violet-600 px-4 py-3 text-sm font-black text-white shadow-sm hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                        Criar minha conta
                    </button>
                </form>

                <p class="mt-4 text-center text-xs text-slate-500">
                    Ao criar sua conta você concorda com nossa
                    <a href="{{ url('/politica-de-privacidade') }}" class="text-violet-600 hover:underline">Política de Privacidade</a>.
                </p>
            </div>

            <p class="mt-6 text-center text-sm text-slate-600">
                Prefere usar senha?
                <a href="{{ $panelLoginUrl }}" class="font-semibold text-violet-600 hover:underline">Voltar ao login</a>
            </p>
        </div>
    </div>

    <script>
    function nicheSelector() {
        return {
            selected: @json(old('business_niche_ids', [])),
            lockedCategory: null,
            onNicheChange(categoryId, nicheId) {
                const remaining = this.selected.filter(id => id != nicheId);
                if (this.selected.includes(nicheId) || this.selected.map(Number).includes(Number(nicheId))) {
                    // unchecked
                } else {
                    // checked
                }
                this.$nextTick(() => {
                    if (this.selected.length === 0) {
                        this.lockedCategory = null;
                    } else {
                        this.lockedCategory = categoryId;
                    }
                });
            },
        };
    }
    </script>
</body>
</html>
