@extends('backend.layouts.app')

@section('title', __('setting.member_btn_setting'))

@section('content')
    <form method="post" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4>{{ __('setting.add_new_setting') }}</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="title-input">@lang('setting.title')</label>
                    <input class="form-control @error('title') is-invalid @enderror" id="title-input" type="text" name="title"
                           value="{{ old('title') }}" placeholder="@lang('setting.btn_iframe_title_holder')" >
                </div>

                <div class="form-group">
                    <label for="name-input">@lang('setting.api_name')</label>
                    <input class="form-control" id="name-input" type="text" name="name" value="{{ old('name') }}" required placeholder="@lang('setting.btn_iframe_apiname_holder')" >
                </div>

                <div class="form-group">
                    <label for="url-input">@lang('setting.setting_data')</label>
                    <input class="form-control @error('value') is-invalid @enderror" id="url-input" type="text" name="value"
                           value="{{ old('value') }}"  placeholder="@lang('setting.setting_data_holder')" >
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
