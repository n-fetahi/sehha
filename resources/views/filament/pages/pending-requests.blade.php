<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading" class="flex items-center justify-between">
            <span>الطلبات </span>

            <div wire:poll.5s>
                <x-filament::badge color="warning" size="lg">
                    {{ \App\Models\User::whereIn('user_type', ['clinic', 'lab'])->where('user_status', 'pending')->count() }} طلب 
                </x-filament::badge>
            </div>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
