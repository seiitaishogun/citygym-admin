@extends('backend.layouts.app')

@section('title', __('setting.contact_setting'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('setting.contact_setting')

            <div class="card-header-actions">
                @if($logged_in_user->id == 1)
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.member-app-config.addContact')"
                    :text="__('setting.contact_add_title')"
                />
                @endif
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
                @if(count($data) > 0 )
                    @foreach($data as $item)
                        <tr>
                            <td>
                                {{ $item->title }}: {{ $item->content }}
                            </td>

                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.member-app-config.editContact', ['id' => $item->api_name]) }}">
                                    <i class="fa fa-edit text-white"></i>
                                </a>
                                @if ($logged_in_user->id == 1)
                                    <a class="btn btn-sm btn-danger" href="{{ route('admin.member-app-config.deleteItem', ['id' => $item->api_name]) }}">
                                        <i class="fa fa-trash text-white"></i>
                                    </a>
                                @endif
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
