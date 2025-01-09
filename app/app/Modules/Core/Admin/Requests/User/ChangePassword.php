<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Requests\User;

use App\Modules\Core\Admin\Requests\AdminFormRequest;

/**
 * @property-read string $newPassword
 */
class ChangePassword extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $guard = config('platform.guard', 'web');

        return [
            'currentPassword' => ['required', 'current_password:' . $guard],
            'newPassword' => ['required', 'confirmed', 'different:currentPassword'],
        ];
    }
}
