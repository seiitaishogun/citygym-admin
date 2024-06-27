@if (
        !$user->isMasterAdmin() && // This is not the master admin
        $user->isActive() && // The account is active
        $user->id !== $logged_in_user->id && // It's not the person logged in
        // Any they have at lease one of the abilities in this dropdown
        (
            $logged_in_user->can('admin.access.user.change-password')
        )
    )
    <x-utils.link
        :href="route('admin.manage.hotResetPass', ['id' => $user->id])"
        class="btn btn-warning btn-sm"
        :text="__('Hot Reset Password')"
        icon="fas fa-key"
        permission="admin.access.user.change-password" />
@endif
