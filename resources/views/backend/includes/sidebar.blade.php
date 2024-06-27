<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
    <div class="c-sidebar-brand d-lg-down-none">
{{--        <svg class="c-sidebar-brand-full" width="118" height="46" alt="CoreUI Logo">--}}
{{--            <use xlink:href="{{ asset('img/brand/coreui.svg#full') }}"></use>--}}
{{--        </svg>--}}
{{--        <svg class="c-sidebar-brand-minimized" width="46" height="46" alt="CoreUI Logo">--}}
{{--            <use xlink:href="{{ asset('img/brand/coreui.svg#signet') }}"></use>--}}
{{--        </svg>--}}
        <img src="{{ asset('img/brand/citigym.png') }}" style="max-width: 120px">
    </div><!--c-sidebar-brand-->

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                :href="route('admin.dashboard')"
                :active="activeClass(Route::is('admin.dashboard'), 'c-active')"
                icon="c-sidebar-nav-icon cil-speedometer"
                :text="__('Dashboard')" />
        </li>

        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.news.*') || Route::is('admin.auth.category.*') || Route::is('admin.banner.*'), 'c-open c-show') }}">
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-file"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('news.news_title')" />

            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.news.index')"
                        class="c-sidebar-nav-link"
                        :text="__('news.list_new')"
                        :active="activeClass(Route::is('admin.news.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.category.index')"
                        class="c-sidebar-nav-link"
                        :text="__('news.list_cat')"
                        :active="activeClass(Route::is('admin.category.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.banner.index')"
                        class="c-sidebar-nav-link"
                        :text="__('news.banner_page')"
                        :active="activeClass(Route::is('admin.banner.*'), 'c-active')" />
                </li>
            </ul>
        </li>

        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                :href="route('admin.manual.index')"
                :active="activeClass(Route::is('admin.manual.index'), 'c-active')"
                icon="c-sidebar-nav-icon cil-speedometer"
                :text="__('news.manual_title')" />
        </li>

        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.config-email.*')
                                        || Route::is('admin.member-app-config.memoSetting')
                                        || Route::is('admin.member-app-config.mbAppCf')
                                        || Route::is('admin.sf-object.connect'),
                                        'c-open c-show') }}">
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-cog"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('acp.menu_config')" />

            <ul class="c-sidebar-nav-dropdown-items">
                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('admin.member-app-config.mbAppCf')"
                    :active="activeClass(Route::is('admin.member-app-config.mbAppCf'), 'c-active')"
                    :text="__('acp.config_button')" />

                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('admin.member-app-config.memoSetting')"
                    :active="activeClass(Route::is('admin.member-app-config.memoSetting'), 'c-active')"
                    :text="__('acp.memo')" />

                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('admin.member-app-config.contactSetting')"
                    :active="activeClass(Route::is('admin.member-app-config.contactSetting'), 'c-active')"
                    :text="__('acp.menu_contact_config')" />

                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('admin.sf-object.connect')"
                    :active="activeClass(Route::is('admin.sf-object.connect'), 'c-active')"
                    :text="__('acp.menu_sfapi_config')" />

            </ul>
        </li>


        @if (
            $logged_in_user->hasAllAccess() ||
            (
                $logged_in_user->can('admin.access.user.list') ||
                $logged_in_user->can('admin.access.user.deactivate') ||
                $logged_in_user->can('admin.access.user.reactivate') ||
                $logged_in_user->can('admin.access.user.clear-session') ||
                $logged_in_user->can('admin.access.user.impersonate') ||
                $logged_in_user->can('admin.access.user.change-password')
            )
        )
            <!--<li class="c-sidebar-nav-item">
                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('admin.member-app-config.mbAppCf')"
                    :active="activeClass(Route::is('admin.member-app-config.mbAppCf'), 'c-active')"
                    icon="c-sidebar-nav-icon cil-soccer"
                    :text="__('setting.member_btn_setting')" />
            </li>

            <li class="c-sidebar-nav-title">@lang('System')</li>

            <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.auth.user.*') || Route::is('admin.auth.role.*'), 'c-open c-show') }}">
                <x-utils.link
                    href="#"
                    icon="c-sidebar-nav-icon cil-user"
                    class="c-sidebar-nav-dropdown-toggle"
                    :text="__('Access')" />

                <ul class="c-sidebar-nav-dropdown-items">
                    @if (
                        $logged_in_user->hasAllAccess() ||
                        (
                            $logged_in_user->can('admin.access.user.list') ||
                            $logged_in_user->can('admin.access.user.deactivate') ||
                            $logged_in_user->can('admin.access.user.reactivate') ||
                            $logged_in_user->can('admin.access.user.clear-session') ||
                            $logged_in_user->can('admin.access.user.impersonate') ||
                            $logged_in_user->can('admin.access.user.change-password')
                        )
                    )
                        <li class="c-sidebar-nav-item">
                            <x-utils.link
                                :href="route('admin.auth.user.index')"
                                class="c-sidebar-nav-link"
                                :text="__('User Management')"
                                :active="activeClass(Route::is('admin.auth.user.*'), 'c-active')" />
                        </li>
                    @endif

                    @if ($logged_in_user->hasAllAccess())
                        <li class="c-sidebar-nav-item">
                            <x-utils.link
                                :href="route('admin.auth.role.index')"
                                class="c-sidebar-nav-link"
                                :text="__('Role Management')"
                                :active="activeClass(Route::is('admin.auth.role.*'), 'c-active')" />
                        </li>
                    @endif
                </ul>
            </li>-->
        @endif

        @if ($logged_in_user->hasAllAccess())
            <!--<li class="c-sidebar-nav-item">
                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('admin.config-email.configEmail')"
                    :active="activeClass(Route::is('admin.config-email.configEmail'), 'c-active')"
                    icon="c-sidebar-nav-icon cil-envelope-closed"
                    :text="__('setting.email_setting_menu')" />
            </li>-->
            <!--<li class="c-sidebar-nav-dropdown">
                <x-utils.link
                    href="#"
                    icon="c-sidebar-nav-icon cil-list"
                    class="c-sidebar-nav-dropdown-toggle"
                    :text="__('Logs')" />

                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('log-viewer::dashboard')"
                            class="c-sidebar-nav-link"
                            :text="__('Dashboard')" />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('log-viewer::logs.list')"
                            class="c-sidebar-nav-link"
                            :text="__('Logs')" />
                    </li>
                </ul>
            </li>-->
        @endif
    </ul>

    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
</div><!--sidebar-->
