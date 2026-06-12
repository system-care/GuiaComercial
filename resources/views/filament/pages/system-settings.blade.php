<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button wire:click="save">
                Salvar configurações
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
