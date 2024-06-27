@extends('backend.layouts.app')

@section('title', __('Command Management'))

@section('content')
    <x-forms.post :action="route('admin.manage.excute_command')">
        @csrf
    <x-backend.card>
        <x-slot name="header">
            @lang('Command Management')
        </x-slot>

        <x-slot name="body">
            @if ( isset($result) )
                <div class="row">
                    <code>
                        <pre>@php( print_r($result) )</pre>
                    </code>
                </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <x-utils.link class="btn btn-info btn-sm" :href="route('admin.clear_cache')" :text="__('Clear Cache')" />
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-group row">
                        <label for="command" class="col-md-2 col-form-label">@lang('Command')</label>
                        <input type="text" name="command" id="command" class="form-control" placeholder="{{ __('Command') }}" value="{{ old('command') }}" />
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group row">
                        <label for="type" class="col-md-2 col-form-label">@lang('Type')</label>
                        <select name="type" id="type" class="form-control" >
                            <option value="artisan">@lang('Artisan')</option>
                            <option value="composer">@lang('Composer')</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <button class="btn btn-sm btn-primary" type="submit"> Excute</button>
        </x-slot>
    </x-backend.card>
    </x-forms.post>
@endsection
