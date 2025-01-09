<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Core\Common\Contracts\UserData;
use App\Modules\Core\Common\Models\User;
use App\Modules\Core\Common\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private readonly UserRepository $repository)
    {
    }

    /**
     * @param int $id
     * @param UserData $dto
     *
     * @return User
     *
     * @throws AdminServiceException
     */
    public function update(int $id, UserData $dto): User
    {
        /** @var User|null $model */
        $model = $this->repository->findById($id);

        if (!$model) {
            throw new AdminServiceException('Not found');
        }

        $model->name = $dto->getName();
        $model->email = $dto->getEmail();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }

        return $model;
    }

    /**
     * @param int $id
     * @param string $newPassword
     *
     * @return void
     *
     * @throws AdminServiceException
     */
    public function changePassword(int $id, string $newPassword): void
    {
        /** @var User|null $model */
        $model = $this->repository->findById($id);

        if (!$model) {
            throw new AdminServiceException('Not found');
        }

        $model->password = Hash::make($newPassword);

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to change password');
        }
    }
}
