@extends('backend.layouts.app')

@section('title', __('news.add_new'))

@section('content')
    <form action="{{ route('admin.banner.edit_action', ['id' => $data->banner_id]) }}" method="post" enctype="multipart/form-data">
        @csrf
    <div class="row">
        <div class="col-12">
            <x-backend.card>
                <x-slot name="body">
                    <div class="nav-tabs-boxed">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#banner-vn" role="tab" aria-controls="banner-vn-lang">@lang('news.vn_lang')</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#banner-en" role="tab" aria-controls="banner-en-lang">@lang('news.en_lang')</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="banner-vn" role="tabpanel">
                                <div class="form-group">
                                    <label for="title-input">@lang('news.title')</label>
                                    <input class="form-control" id="title-input" type="text" name="vn[title]"
                                           value="{{ $data->getNameByLobannere('vn') }}" placeholder="@lang('news.vn_title_holder')" >
                                </div>

                                <div class="form-group">
                                    <label for="file-input">@lang('news.image')</label>
                                    <input id="file-input" type="file" name="vn[image]">

                                    <div class="row">
                                        @if(!empty($data->meta['vn']->image))
                                            <div class="col-12 mt-xxl-2">
                                                <img src='{{ asset('storage'.$data->meta['vn']->image->full_img) }}' class="img-thumbnail" style="max-width: 450px">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="tab-pane" id="banner-en" role="tabpanel">
                                <div class="form-group">
                                    <label for="title-input">@lang('news.title')</label>
                                    <input class="form-control" id="title-input" type="text" name="en[title]"
                                           value="{{ $data->getNameByLobannere('en') }}" placeholder="@lang('news.en_title_holder')" >
                                </div>

                                <div class="form-group">
                                    <label for="file-input">@lang('news.image')</label>
                                    <input id="file-input" type="file" name="en[image]">

                                    <div class="row">
                                        @if(!empty($data->meta['en']->image))
                                            <div class="col-12 mt-xxl-2">
                                                <img src='{{ asset('storage'.$data->meta['en']->image->full_img) }}' class="img-thumbnail" style="max-width: 450px">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="slt_status">@lang('news.status')</label>
                        <select class="form-control" id="slt_status" name="status">
                            <option value="published">@lang('news.status_published')</option>
                            <option value="draft">@lang('news.status_draft')</option>
                        </select>
                    </div>

                </x-slot>
                <x-slot name="footer">
                    <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                    <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                </x-slot>
            </x-backend.card>
        </div>
    </div>
    </form>
@endsection
