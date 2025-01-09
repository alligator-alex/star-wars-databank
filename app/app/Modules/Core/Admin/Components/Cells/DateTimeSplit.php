<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Components\Cells;

use DateTimeZone;
use Orchid\Screen\Components\Cells\DateTimeSplit as OrchidDateTimeSplit;

class DateTimeSplit extends OrchidDateTimeSplit
{
    public function __construct(
        protected mixed $value,
        protected string $upperFormat = 'M j, Y',
        protected string $lowerFormat = 'D, H:i:s',
        protected DateTimeZone|null|string $tz = null
    ) {
        parent::__construct($this->value, $this->upperFormat, $this->lowerFormat, $this->tz);
    }

    public function render(): string
    {
        if (!$this->value) {
            return '';
        }

        return parent::render();
    }
}
