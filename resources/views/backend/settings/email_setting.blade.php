@extends('backend.layouts.app')

@section('title', __('setting.email_setting_title'))

@section('content')
    <form action="{{ route('admin.config-email.saveSetting') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card card-accent-primary">
                    <div class="card-header">
                        @lang('setting.email_setting_title')
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="host">@lang('setting.email_st_host') <span class="text-danger">*</span> </label>
                                    <input class="form-control" id="host" type="text" name="host"
                                           value="{{ $email_settings->host }}"  >
                                </div>

                                <div class="form-group">
                                    <label for="username">@lang('setting.email_st_user') <span class="text-danger">*</span> </label>
                                    <input class="form-control" id="username" type="text" name="username"
                                           value="{{ $email_settings->username }}" >
                                </div>

                                <div class="form-group">
                                    <label for="is_sandbox">@lang('setting.email_st_password') <span class="text-danger">*</span></label>
                                    <input class="form-control" id="password" type="password" name="password"
                                           value="{{ $email_settings->password }}"  >
                                </div>


                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="port">@lang('setting.email_st_port') <span class="text-danger">*</span> </label>
                                    <input class="form-control" id="port" type="number" name="port"
                                           value="{{ $email_settings->port }}"  >
                                </div>

                                <div class="form-group">
                                    <label for="encryption">@lang('setting.email_st_encryption') <span class="text-danger">*</span> </label>
                                    <input class="form-control" id="encryption" type="text" name="encryption"
                                           value="{{ $email_settings->encryption }}"  >
                                </div>

                                @if($id > 0 )
                                    <input type="hidden" name="id" value="{{ $id }}" >
                                @endif
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <div class="float-left">
                            <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                            <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                        </div>
                        <div class="float-right">
                            @if(!empty($email_settings->client_id) && !empty($email_settings->client_secret))
                                <a class="btn btn-behance btn-sm" href="{{ route('admin.sf-object.onConnect') }}">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection()
