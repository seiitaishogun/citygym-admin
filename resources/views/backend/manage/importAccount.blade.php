@extends('backend.layouts.app')

@section('title', __('Import Files'))

@section('content')
    <form action="{{ route('admin.import.import_account') }}" method="post" enctype="multipart/form-data">
        @csrf
    <x-backend.card>
        <x-slot name="header">
            @lang('Import Account')
        </x-slot>

        <x-slot name="body">
            <p>Upload file Excel chỉ có email user lên để đổi password cho toàn bộ account có email trùng với email trong file excel đã chọn</p>
            <code style="font-size: 18px">Tải file Excel mẫu <a href="{{ asset('storage/ImportCTGUserTest.xlsx') }}" >tại đây</a></code>
            <div class="form-group row">
                <label class="col-md-3 col-form-label" for="file-input">File input</label>
                <div class="col-md-9">
                    <input id="file-input" type="file" name="file">
                </div>
            </div>

            @if(isset($result) && !empty($result))
                <div class="text-mute text-success">
                    @foreach($result as $item)
                        <p>{{$item[0]}} - {{$item[1]}}</p>
                    @endforeach
                </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
        </x-slot>
    </x-backend.card>
    </form>
@endsection
