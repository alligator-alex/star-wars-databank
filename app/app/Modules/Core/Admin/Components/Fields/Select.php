<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Components\Fields;

use Orchid\Screen\Fields\Select as OrchidSelect;

/**
 * Implement <optgroup>.
 */
class Select extends OrchidSelect
{
    protected $view = 'admin.common.fields.extended-select';

    public function render()
    {
        $value = $this->get('value');
        if (!is_array($value)) {
            return parent::render();
        }

        $options = $this->get('options');

        // send selected options to the back for them having exact sorting, which comes from controller
        $selectedOptions = [];
        foreach ($options as $key => $item) {
            if (!in_array($key, $value, true)) {
                continue;
            }

            $selectedOptions[$key] = $item;

            unset($options[$key]);
        }

        $selectedOptions = array_replace(array_flip($value), $selectedOptions);

        $this->set('options', $options + $selectedOptions);

        return parent::render();
    }
}
