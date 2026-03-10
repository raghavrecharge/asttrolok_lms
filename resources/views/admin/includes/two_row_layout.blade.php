@php
    $leftColumnClass = $leftColumnClass ?? 'col-12 col-lg-8';
    $rightColumnClass = $rightColumnClass ?? 'col-12 col-lg-4';
@endphp

<div class="row">
    <!-- Left Column -->
    <div class="{{ $leftColumnClass }}">
        @isset($leftCard)
            <div class="compact-form-card">
                @isset($leftCard['title'])
                    <h2 class="compact-section-heading">
                        @isset($leftCard['icon'])
                            <span class="material-symbols-outlined text-primary">{{ $leftCard['icon'] }}</span>
                        @endif
                        {{ $leftCard['title'] }}
                    </h2>
                @endif
                <div class="{{ $leftCard['contentClass'] ?? 'space-y-4' }}">
                    {{ $leftCard['content'] }}
                </div>
            </div>
        @else
            {{ $leftContent ?? '' }}
        @endif
    </div>

    <!-- Right Column -->
    <div class="{{ $rightColumnClass }}">
        @isset($rightTopCard)
            <div class="compact-form-card">
                @isset($rightTopCard['title'])
                    <h2 class="compact-section-heading">
                        @isset($rightTopCard['icon'])
                            <span class="material-symbols-outlined text-primary">{{ $rightTopCard['icon'] }}</span>
                        @endif
                        {{ $rightTopCard['title'] }}
                    </h2>
                @endif
                <div class="{{ $rightTopCard['contentClass'] ?? 'space-y-4' }}">
                    {{ $rightTopCard['content'] }}
                </div>
            </div>
        @endif

        @isset($rightBottomCard)
            <div class="compact-form-card">
                @isset($rightBottomCard['title'])
                    <h2 class="compact-section-heading">
                        @isset($rightBottomCard['icon'])
                            <span class="material-symbols-outlined text-primary">{{ $rightBottomCard['icon'] }}</span>
                        @endif
                        {{ $rightBottomCard['title'] }}
                    </h2>
                @endif
                <div class="{{ $rightBottomCard['contentClass'] ?? 'space-y-4' }}">
                    {{ $rightBottomCard['content'] }}
                </div>
            </div>
        @endif
    </div>
</div>
