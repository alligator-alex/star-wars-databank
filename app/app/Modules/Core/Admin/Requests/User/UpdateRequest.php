<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Requests\User;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Core\Common\Contracts\UserData;
use App\Modules\Core\Common\Models\User;
use Illuminate\Validation\Rule;

/**
 * @property-read string $name
 * @property-read string $email
 */
class UpdateRequest extends AdminFormRequest implements UserData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::tableName(), 'email')->ignore($this->user()),
            ],
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
