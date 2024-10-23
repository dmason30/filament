@props([
    'alpineDisabled' => null,
    'alpineValid' => null,
    'disabled' => false,
    'inlinePrefix' => false,
    'inlineSuffix' => false,
    'prefix' => null,
    'prefixActions' => [],
    'prefixIcon' => null,
    'prefixIconColor' => 'gray',
    'prefixIconAlias' => null,
    'suffix' => null,
    'suffixActions' => [],
    'suffixIcon' => null,
    'suffixIconColor' => 'gray',
    'suffixIconAlias' => null,
    'valid' => true,
])

@php
    $prefixActions = array_filter(
        $prefixActions,
        fn (\Filament\Actions\Action $prefixAction): bool => $prefixAction->isVisible(),
    );

    $suffixActions = array_filter(
        $suffixActions,
        fn (\Filament\Actions\Action $suffixAction): bool => $suffixAction->isVisible(),
    );

    $hasPrefix = count($prefixActions) || $prefixIcon || filled($prefix);
    $hasSuffix = count($suffixActions) || $suffixIcon || filled($suffix);

    $hasAlpineDisabledClasses = filled($alpineDisabled);
    $hasAlpineValidClasses = filled($alpineValid);
    $hasAlpineClasses = $hasAlpineDisabledClasses || $hasAlpineValidClasses;

    $getIconClasses = fn (string | array $color = 'gray'): string => \Illuminate\Support\Arr::toCssClasses([
        'fi-input-wrp-icon',
        match ($color) {
            'gray' => '',
            default => 'fi-color-custom',
        },
        is_string($color) ? "fi-color-{$color}" : null,
    ]);

    $getIconStyles = fn (string | array $color = 'gray'): string => \Illuminate\Support\Arr::toCssStyles([
        \Filament\Support\get_color_css_variables(
            $color,
            shades: [500],
            alias: 'input-wrapper.icon',
        ) => $color !== 'gray',
    ]);

    $wireTarget = $attributes->whereStartsWith(['wire:target'])->first();

    $hasLoadingIndicator = filled($wireTarget);

    if ($hasLoadingIndicator) {
        $loadingIndicatorTarget = html_entity_decode($wireTarget, ENT_QUOTES);
    }
@endphp

<div
    @if ($hasAlpineClasses)
        x-bind:class="{
            {{ $hasAlpineDisabledClasses ? "'fi-disabled': {$alpineDisabled}," : null }}
            {{ $hasAlpineValidClasses ? "'fi-invalid': ! ({$alpineValid})," : null }}
        }"
    @endif
    {{
        $attributes
            ->except(['wire:target', 'tabindex'])
            ->class([
                'fi-input-wrp',
                'fi-disabled' => (! $hasAlpineClasses) && $disabled,
                'fi-invalid' => (! $hasAlpineClasses) && (! $valid),
            ])
    }}
>
    @if ($hasPrefix || $hasLoadingIndicator)
        <div
            @if (! $hasPrefix)
                wire:loading.delay.{{ config('filament.livewire_loading_delay', 'default') }}.flex
                wire:target="{{ $loadingIndicatorTarget }}"
                wire:key="{{ \Illuminate\Support\Str::random() }}" {{-- Makes sure the loading indicator gets hidden again. --}}
            @endif
            @class([
                'fi-input-wrp-prefix',
                'fi-input-wrp-prefix-has-content' => $hasPrefix,
                'fi-inline' => $inlinePrefix,
                'fi-input-wrp-prefix-has-label' => filled($prefix),
            ])
        >
            @if (count($prefixActions))
                <div class="fi-input-wrp-actions">
                    @foreach ($prefixActions as $prefixAction)
                        {{ $prefixAction }}
                    @endforeach
                </div>
            @endif

            {{
                \Filament\Support\generate_icon_html($prefixIcon, $prefixIconAlias, (new \Illuminate\View\ComponentAttributeBag)
                    ->merge([
                        'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => $hasLoadingIndicator,
                        'wire:target' => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
                    ], escape: false)
                    ->class([$getIconClasses($prefixIconColor)])
                    ->style([$getIconStyles($prefixIconColor)]))
            }}

            @if ($hasLoadingIndicator)
                <x-filament::loading-indicator
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new \Illuminate\View\ComponentAttributeBag([
                                'wire:loading.delay.' . config('filament.livewire_loading_delay', 'default') => $hasPrefix,
                                'wire:target' => $hasPrefix ? $loadingIndicatorTarget : null,
                            ])
                        )->class([$getIconClasses()])
                    "
                />
            @endif

            @if (filled($prefix))
                <span class="fi-input-wrp-label">
                    {{ $prefix }}
                </span>
            @endif
        </div>
    @endif

    <div
        @if ($hasLoadingIndicator && (! $hasPrefix))
            @if ($inlinePrefix)
                wire:loading.delay.{{ config('filament.livewire_loading_delay', 'default') }}.class.remove="ps-3"
            @endif

            wire:target="{{ $loadingIndicatorTarget }}"
        @endif
        @class([
            'fi-input-wrp-content-ctn',
            'fi-input-wrp-content-ctn-ps' => $hasLoadingIndicator && (! $hasPrefix) && $inlinePrefix,
        ])
    >
        {{ $slot }}
    </div>

    @if ($hasSuffix)
        <div
            @class([
                'fi-input-wrp-suffix',
                'fi-inline' => $inlineSuffix,
                'fi-input-wrp-suffix-has-label' => filled($suffix),
            ])
        >
            @if (filled($suffix))
                <span class="fi-input-wrp-label">
                    {{ $suffix }}
                </span>
            @endif

            {{
                \Filament\Support\generate_icon_html($suffixIcon, $suffixIconAlias, (new \Illuminate\View\ComponentAttributeBag)
                    ->merge([
                        'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => $hasLoadingIndicator,
                        'wire:target' => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
                    ], escape: false)
                    ->class([$getIconClasses($suffixIconColor)])
                    ->style([$getIconStyles($suffixIconColor)]))
            }}

            @if (count($suffixActions))
                <div class="fi-input-wrp-actions">
                    @foreach ($suffixActions as $suffixAction)
                        {{ $suffixAction }}
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
