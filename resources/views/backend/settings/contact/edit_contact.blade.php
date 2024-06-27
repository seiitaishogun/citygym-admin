@extends('backend.layouts.app')

@section('title', __('setting.contact_edit_title'))

@section('content')
    <form action="{{ route('admin.member-app-config.editContact', ['id' => $item->api_name]) }}" method="post" >
        @csrf
        <div class="card">
            <div class="card-header">
                <h4>{{ __('setting.contact_edit_title') }}</h4>
            </div>
            <div class="card-body">

                <div class="form-group row">
                    <label for="title-input" class="col-2">@lang('setting.btn_iframe_title')</label>
                    <div class="col-10">
                        <input class="form-control" id="title-input" type="text" name="title" required value="{{ $item->title }}" >
                    </div>
                </div>

                @if ($logged_in_user->id == 1)
                <div class="form-group row">
                    <label for="name-input" class="col-2">@lang('setting.btn_iframe_apiname')</label>
                    <div class="col-10">
                        <input class="form-control" id="name-input" type="text" name="name" required value="{{ $item->api_name }}" >
                    </div>
                </div>
                @endif

                <div class="form-group row">
                    <label for="content-input" class="col-2">@lang('setting.memo_content')</label>
                    <div class="col-10">
                        <input class="form-control" id="content-input" type="text" name="content" required value="{{ $item->content }}" >
                    </div>
                </div>



            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
@endsection
