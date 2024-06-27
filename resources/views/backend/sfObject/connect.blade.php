@extends('backend.layouts.app')

@section('title', __('sfObject.api_infor'))

@section('content')
    <form action="{{ route('admin.sf-object.connect') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card card-accent-primary">
                    <div class="card-header">
                        @lang('sfObject.api_infor')
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="client_id">@lang('sfObject.client_id') <span class="text-danger">*</span> </label>
                                    <input class="form-control" id="client_id" type="password" name="client_id"
                                           value="{{ $api_settings->client_id }}" placeholder="@lang('sfObject.client_id')" >
                                </div>

                                <div class="form-group">
                                    <label for="client_secret">@lang('sfObject.client_secret') <span class="text-danger">*</span></label>
                                    <input class="form-control" id="client_secret" type="password" name="client_secret"
                                           value="{{ $api_settings->client_secret }}" placeholder="@lang('sfObject.client_secret')" >
                                </div>

                                <div class="form-group">
                                    <label for="is_sandbox">@lang('sfObject.is_sandbox')</label>
                                    <input type="checkbox" id="is_sandbox" name="is_sandbox" value="1" {!! $api_settings->is_sandbox == 1 ? 'checked' : '' !!} >
                                </div>


                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="client_id">@lang('sfObject.api_user')  </label>
                                    <input class="form-control" id="api_user" type="text" name="api_user"
                                           value="{{ $api_settings->api_user??'' }}" placeholder="@lang('sfObject.api_user')" >
                                </div>

                                <div class="form-group">
                                    <label for="client_secret">@lang('sfObject.api_password') </label>
                                    <input class="form-control" id="api_password" type="password" name="api_password"
                                           value="{{ $api_settings->api_password??'' }}" placeholder="@lang('sfObject.api_password')" >
                                </div>

                                <div class="form-group">
                                    <label for="client_secret">@lang('sfObject.security_token') </label>
                                    <input class="form-control" id="api_password" type="password" name="security_token"
                                           value="{{ $api_settings->security_token??'' }}" placeholder="@lang('sfObject.security_token')" >
                                </div>

                                @if($id > 0 )
                                    <input type="hidden" name="id" value="{{ $id }}" >
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label class="col-3" for="is_sandbox">@lang('sfObject.connect_status')</label>
                                    <p class="col-9">
                                        @if(!empty($api_settings->refresh_token))
                                            <span class="text-success">@lang('sfObject.connect_status_success')</span>
                                        @else
                                            <span class="text-danger">@lang('sfObject.connect_status_not_connected')</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="float-left">
                            <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                            <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                        </div>
                        <div class="float-right">
                            @if(!empty($api_settings->client_id) && !empty($api_settings->client_secret))
                            <a class="btn btn-behance btn-sm" href="{{ route('admin.sf-object.onConnect') }}">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection()
