@extends('backend.layouts.app')

@section('title', __('setting.member_setting'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('setting.member_setting')

            <div class="card-header-actions">
{{--                <x-utils.link--}}
{{--                    icon="c-icon cil-plus"--}}
{{--                    class="card-header-action"--}}
{{--                    :href="route('admin.member-app-config.addNewBtnIframe')"--}}
{{--                    :text="__('setting.add_new_setting')"--}}
{{--                />--}}
            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th>@lang('setting.title')</th>
                    <th>@lang('setting.api_name')</th>
                    <th>@lang('setting.setting_data')</th>
                    <th>{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @if(count($btnData) > 0 )
                    @foreach($btnData as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>
                                {{ $item->api_name }}
                            </td>
                            <td>
                                <code>{{ $item->value }}</code>
                            </td>

                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.member-app-config.editSetting', ['id' => $item->api_name]) }}">
                                    <i class="fa fa-edit text-white"></i>
                                </a>
                                <a class="btn btn-sm btn-danger" href="{{ route('admin.member-app-config.deleteBtnIframe', ['id' => $item->api_name]) }}">
                                    <i class="fa fa-trash-alt text-white"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7">
                            {{ __('No Record Found') }}
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
