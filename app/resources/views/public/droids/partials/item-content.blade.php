@php
use App\Modules\Databank\Public\Helpers\ViewHelper;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;

/**
 * @var int|null $index
 * @var bool|null $isPreviewTarget
 * @var Droid $model
 */

$settings = $model->getPageSettings()->getForIndex();
@endphp
<div class="entity-list__item-wrapper wow fadeInUp"
     @isset($index) data-wow-delay="{{ (($index + 1) * 100) }}ms" @endisset>
    <a href="{{ route(DroidRouteName::DETAIL, $model->slug, false) }}"
       class="entity-list__item wow fadeInUp @if ($settings->isCardLarge()) entity-list__item--large @endif"
       @if (isset($isPreviewTarget) && $isPreviewTarget) data-preview-target="true" @endif>
        <div class="entity-list__image @if ($settings->isImageCovered()) entity-list__image--covered @endif">
            @if ($model->factions->isNotEmpty())
                <div class="entity-list__factions">
                    @if ($model->mainFaction)
                        <div class="entity-list__faction-emblem entity-list__faction-emblem--main faction-emblem faction-emblem--{{ $model->mainFaction->slug }}"
                             title="{{ __('Mainly used by the') }} {{ $model->mainFaction->name }}">
                            <svg>
                                <use xlink:href="#emblem-{{ $model->mainFaction->slug }}"></use>
                            </svg>
                        </div>
                    @endif
                    @foreach ($model->otherFactions as $faction)
                        <div class="entity-list__faction-emblem faction-emblem faction-emblem--{{ $faction->slug }}"
                             title="{{ $model->mainFaction ? __('Also used by the') :  __('Used by the') }} {{ $faction->name }}">
                            <svg>
                                <use xlink:href="#emblem-{{ $faction->slug }}"></use>
                            </svg>
                        </div>
                    @endforeach
                </div>
            @endif
            <picture>
                @if ($model->image)
                    <img src="{{ $model->image->medium_url }}"
                         loading="lazy"
                         alt="{{ $model->name }}"
                         style="{{ ViewHelper::indexPageSettingsImageStyle($settings) }}">
                @else
                    <img src="/images/static{{ ViewHelper::imagePlaceholderRandomSuffix() }}.gif"
                         class="is-dummy"
                         loading="lazy"
                         alt="{{ $model->name }}">
                @endif
            </picture>
        </div>
        <div class="entity-list__info">
            <div class="entity-list__name" title="{{ $model->name }}">{{ $model->name }}</div>

            <div class="rogue-links">
                @if ($model->line)
                    <span class="rogue-links__item rogue-links__item--line" title="{{ __('Line') }}">
                        <span class="rogue-links__icon rogue-icon"><noindex>f4</noindex></span>
                        <span class="rogue-links__value">{{ $model->line->name }}</span>
                    </span>
                @endif

                @if ($model->model)
                    <span class="rogue-links__item rogue-links__item--model" title="{{ __('Model') }}">
                        <span class="rogue-links__icon rogue-icon"><noindex>1</noindex></span>
                        <span class="rogue-links__value">{{ $model->model->name }}</span>
                    </span>
                @endif

                @if ($model->class)
                    <span class="rogue-links__item rogue-links__item--class" title="{{ __('Class') }}">
                        <span class="rogue-links__icon rogue-icon"><noindex>e</noindex></span>
                        <span class="rogue-links__value">{{ $model->class->name }}</span>
                    </span>
                @endif
            </div>
        </div>
    </a>
</div>
