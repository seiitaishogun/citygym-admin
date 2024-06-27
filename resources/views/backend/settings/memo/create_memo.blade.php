@extends('backend.layouts.app')

@section('title', __('setting.memo_add_title'))

@section('content')
    <form action="{{ route('admin.member-app-config.addMemo') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4>{{ __('setting.memo_add_title') }}</h4>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label for="content-input">@lang('setting.memo_content')</label>
                    <textarea class="form-control" id="content-input" name="content" ></textarea>
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
