@extends('backend.layouts.app')

@section('title', __('setting.memo_setting'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('setting.memo_setting')

            <div class="card-header-actions">
{{--                <x-utils.link--}}
{{--                    icon="c-icon cil-plus"--}}
{{--                    class="card-header-action"--}}
{{--                    :href="route('admin.member-app-config.addNewBtnIframe')"--}}
{{--                    :text="__('setting.add_new_btn')"--}}
{{--                />--}}
            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th width="80%">@lang('setting.memo_content')</th>
                    <th width="10%">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @if(count($memoData) > 0 )
                    @foreach($memoData as $index => $item)
                        <tr>
                            <td>
                                {{ $item }}
                            </td>

                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.member-app-config.editMemo', ['id' => $index]) }}">
                                    <i class="fa fa-edit text-white"></i>
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
