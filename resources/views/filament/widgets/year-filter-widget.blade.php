<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <label for="year-filter" class="text-sm font-medium text-gray-900 dark:text-white">
                Viendo estadísticas para:
            </label>
            
            <x-filament::input.wrapper class="w-48">
                <x-filament::input.select
                    id="year-filter"
                    wire:model.live="selectedYear">
                    
                    <option value="all">Global (Todos los Años)</option>

                    @foreach($this->getYearsProperty() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                    
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>