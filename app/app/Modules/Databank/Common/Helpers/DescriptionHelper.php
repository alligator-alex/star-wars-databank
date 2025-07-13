<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Helpers;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Illuminate\Support\Str;

class DescriptionHelper
{
    public static function beautify(Vehicle|Droid $model): void
    {
        $namePlural = Str::plural($model->name);

        $hasStrongNamePlural = str_contains($model->description, '<strong>' . $namePlural);
        $hasStrongNameSingular = str_contains($model->description, '<strong>' . $model->name);

        if (!$hasStrongNamePlural && !$hasStrongNameSingular) {
            $positionPlural = mb_strpos($model->description, $namePlural);
            $positionSingular = mb_strpos($model->description, $model->name);

            if ($positionPlural !== false) {
                $startString = mb_substr($model->description, 0, $positionPlural);
                $endString = mb_substr($model->description, $positionPlural + mb_strlen($namePlural));

                $model->description = $startString . '<strong>' . $namePlural . '</strong>' . $endString;
            } elseif ($positionSingular !== false) {
                $startString = mb_substr($model->description, 0, $positionSingular);
                $endString = mb_substr($model->description, $positionSingular + mb_strlen($model->name));

                $model->description = $startString . '<strong>' . $model->name . '</strong>' . $endString;
            }
        }

        $nonBreakingSpace = '&nbsp;';

        $model->description = str_replace([
            'a ',
            'an ',
            'as ',
            'by ',
            'for ',
            'in ',
            'of ',
            'the ',
            'to ',
        ], [
            'a' . $nonBreakingSpace,
            'an' . $nonBreakingSpace,
            'as' . $nonBreakingSpace,
            'by' . $nonBreakingSpace,
            'for' . $nonBreakingSpace,
            'in' . $nonBreakingSpace,
            'of' . $nonBreakingSpace,
            'the' . $nonBreakingSpace,
            'to' . $nonBreakingSpace,
        ], $model->description);
    }

    public static function injectRelatedUrls(Vehicle|Droid $model): void
    {
        foreach (self::vehiclesUrls() as $relatedName => $relatedUrl) {
            self::injectRelatedUrlOrSkip($model, $relatedName, $relatedUrl);
        }

        foreach (self::droidsUrls() as $relatedName => $relatedUrl) {
            self::injectRelatedUrlOrSkip($model, $relatedName, $relatedUrl);
        }
    }

    private static function injectRelatedUrlOrSkip(
        Vehicle|Droid $model,
        string $relatedName,
        string $relatedUrl,
    ): void {
        if ($model->name === $relatedName) {
            return;
        }

        $relatedNamePlural = Str::plural($relatedName);
        if (str_contains('<a href="' . $relatedUrl . '">' . $relatedNamePlural, $model->description)
            || str_contains('<a href="' . $relatedUrl . '">' . $relatedName . '</a>', $model->description)) {
            return;
        }

        if (str_contains($model->description, $relatedNamePlural)) {
            $model->description = str_replace(
                $relatedNamePlural,
                '<a href="' . $relatedUrl . '">' . $relatedNamePlural . '</a>',
                $model->description
            );
        } elseif (str_contains($model->description, $relatedName)) {
            $model->description = str_replace(
                $relatedName,
                '<a href="' . $relatedUrl . '">' . $relatedName . '</a>',
                $model->description
            );
        }

        // get rid of url inside "<strong>" tag with model's name
        $selfNameLinkRegex = '#<strong>((?:.+)?<a href="' . $relatedUrl . '">' . $relatedName . '</a>(?:.+)?)</strong>#';
        if (preg_match($selfNameLinkRegex, $model->description) === 1) {
            $model->description = preg_replace(
                $selfNameLinkRegex,
                '<strong>' . $model->name . '</strong>',
                $model->description
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private static function vehiclesUrls(): array
    {
        static $vehiclesUrlsCache = null;

        if ($vehiclesUrlsCache === null) {
            $vehiclesUrlsCache = [];

            $repository = app()->make(VehicleRepository::class);
            $repository->queryBuilder()->lazyById()->each(
                static function (Vehicle $model) use (&$vehiclesUrlsCache): void {
                    $vehiclesUrlsCache[$model->name] = route(VehicleRouteName::DETAIL, ['slug' => $model->slug], false);
                }
            );
        }
        return $vehiclesUrlsCache;
    }

    /**
     * @return array<string, string>
     */
    private static function droidsUrls(): array
    {
        static $droidsUrlsCache = null;

        if ($droidsUrlsCache === null) {
            $droidsUrlsCache = [];

            $repository = app()->make(DroidRepository::class);
            $repository->queryBuilder()->lazyById()->each(
                static function (Droid $model) use (&$droidsUrlsCache): void {
                    $droidsUrlsCache[$model->name] = route(DroidRouteName::DETAIL, ['slug' => $model->slug], false);
                }
            );
        }

        return $droidsUrlsCache;
    }
}
