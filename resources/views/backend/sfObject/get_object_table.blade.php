@extends('backend.layouts.app')

@section('title', __('sfObject.get_object'))

@section('content')
    <form action="{{ route('admin.sf-object.getObject') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card card-accent-primary">
                    <div class="card-header">
                        @lang('sfObject.get_object')
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="object">@lang('sfObject.object_name') <span class="text-danger">*</span> </label>
                                    <input class="form-control" id="client_id" type="text" name="object"
                                            placeholder="@lang('sfObject.object_name')" >
                                </div>

                                <div class="form-group">
                                    <label for="is_display">@lang('sfObject.view_object')</label>
                                    <input type="checkbox" id="is_display" name="is_display" value="1" checked >
                                </div>


                            </div>

                        </div>


                    </div>

                    <div class="card-footer">
                        <div class="float-left">
                            <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                            <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-12">
            @if(isset($data['result']))
            <code><pre>{{ print_r($data['result'], true) }}</pre></code>
            @endif
        </div>
    </div>
@endsection()
