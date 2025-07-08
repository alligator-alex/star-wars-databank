<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Helpers;

class DescriptionHelper
{
    public static function beautify(string $description, string $entityName): string
    {
        $strongNamePosition = mb_strpos($description, '<strong>' . $entityName);
        if ($strongNamePosition === false) {
            $position = mb_strpos($description, $entityName);
            if ($position !== false) {
                $startString = mb_substr($description, 0, $position);
                $endString = mb_substr($description, $position + mb_strlen($entityName));

                $description = $startString . '<strong>' . $entityName . '</strong>' . $endString;
            }
        }

        $nonBreakingSpace = '&nbsp;';

        $description = str_replace([
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
        ], $description);

        return $description;
    }
}
