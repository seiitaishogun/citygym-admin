@extends('backend.layouts.app')

@section('title', __('setting.member_setting'))

@section('content')
    <form action="{{ route('admin.member-app-config.editBtnIframe', ['id' => $item->api_name]) }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4>{{ __('setting.edit_setting') }}</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="title-input">@lang('setting.title')</label>
                    <input class="form-control @error('title') is-invalid @enderror" id="title-input" type="text" name="title" value="{{ $item->title }}"
                           placeholder="@lang('setting.btn_iframe_title_holder')" >
                </div>

                <div class="form-group">
                    <label for="name-input">@lang('setting.api_name')</label>
                    <input class="form-control" id="name-input" type="text" name="name" required value="{{ $item->api_name }}"
                           placeholder="@lang('setting.btn_iframe_apiname_holder')" disabled >
                </div>

                <div class="form-group">
                    <label for="url-input">@lang('setting.setting_data')</label>
                    <input class="form-control @error('value') is-invalid @enderror" id="url-input" type="text" name="value" required value="{{ $item->value }}"
                           placeholder="@lang('setting.setting_data_holder')" >
                </div>

            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
@endsection
