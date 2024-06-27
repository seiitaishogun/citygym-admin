@extends('backend.layouts.app')

@section('title', __('Import Files'))

@section('content')
    <form action="{{ route('admin.import.import_csv') }}" method="post" enctype="multipart/form-data">
        @csrf
    <x-backend.card>
        <x-slot name="header">
            @lang('Import Files')
        </x-slot>

        <x-slot name="body">

            <div class="form-group row">
                <label class="col-md-3 col-form-label" for="file-input">File input</label>
                <div class="col-md-9">
                    <input id="file-input" type="file" name="file">
                </div>
            </div>

        </x-slot>
        <x-slot name="footer">
            <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
        </x-slot>
    </x-backend.card>
    </form>
@endsection
