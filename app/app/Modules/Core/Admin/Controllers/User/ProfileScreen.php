<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Controllers\User;

use App\Modules\Core\Admin\Components\User\Layouts\Profile\PasswordRows;
use App\Modules\Core\Admin\Components\User\Layouts\Profile\MainRows;
use App\Modules\Core\Admin\Enums\UserRouteName;
use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Core\Admin\Requests\User\ChangePassword;
use App\Modules\Core\Admin\Requests\User\Update;
use App\Modules\Core\Admin\Services\UserService;
use App\Modules\Core\Common\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProfileScreen extends Screen
{
    public function __construct(private readonly UserService $service)
    {
    }

    public function name(): ?string
    {
        return __('Profile');
    }

    /**
     * @param Request $request
     *
     * @return array<string, User>
     */
    public function query(Request $request): iterable
    {
        return [
            'model' => $request->user(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            /** @phpstan-ignore-next-line */
            Button::make('Sign out')
                ->novalidate()
                ->icon('bs.box-arrow-left')
                ->class('btn icon-link rounded')
                ->route('platform.logout')
                ->type(Color::DANGER)
                ->confirm(__('All unsaved data will be lost.')),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(MainRows::class)
                ->title(__('Main Information'))
                ->commands(
                    Button::make(__('Save'))
                        ->type(Color::PRIMARY)
                        ->icon('bs.check-circle')
                        ->route(name: UserRouteName::UPDATE->value, absolute: false)
                ),

            Layout::block(PasswordRows::class)
                ->title(__('Change password'))
                ->description(__('Ensure your account is using a long, random password to stay secure'))
                ->commands(
                    Button::make(__('Change password'))
                        ->type(Color::INFO)
                        ->icon('bs.check-circle')
                        ->route(name: UserRouteName::CHANGE_PASSWORD->value, absolute: false)
                ),
        ];
    }

    public function update(Update $request): RedirectResponse
    {
        try {
            $model = $this->service->update($request->user()->id, $request);

            Toast::success('"' . $model->name . '" ' . __('has been updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }

        return redirect()->refresh();
    }

    public function changePassword(ChangePassword $request): RedirectResponse
    {
        try {
            $this->service->changePassword($request->user()->id, $request->newPassword);

            Toast::success(__('Password updated'));
        } catch (AdminServiceException $e) {
            Alert::error($e->getMessage());
        }

        return redirect()->route(UserRouteName::PROFILE);
    }
}
