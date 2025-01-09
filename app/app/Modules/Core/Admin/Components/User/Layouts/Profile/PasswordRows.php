<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Components\User\Layouts\Profile;

use Orchid\Screen\Fields\Password;
use Orchid\Screen\Layouts\Rows;

class PasswordRows extends Rows
{
    public function fields(): array
    {
        return [
            Password::make('currentPassword')
                ->placeholder(__('Enter the current password'))
                ->title(__('Current password'))
                ->help('This is your password set at the moment.'),

            Password::make('newPassword')
                ->placeholder(__('Enter the password to be set'))
                ->title(__('New password')),

            Password::make('newPassword_confirmation')
                ->placeholder(__('Enter the password to be set'))
                ->title(__('Confirm new password'))
                ->help('A good password is at least 15 characters or at least 8 characters long, including a number and a lowercase letter.'),
        ];
    }
}
