@extends('backend.layouts.app')

@section('title', __('news.add_new'))

@section('content')
    <form action="{{ route('admin.banner.edit_action', ['id' => $data->banner_id]) }}" method="post" enctype="multipart/form-data">
        @csrf
    <div class="row">
        <div class="col-8">
            <div class="card card-accent-primary">
                <div class="card-header">
                    @lang('news.add_banner')
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title-input">@lang('news.title')</label>
                        <input class="form-control" id="title-input" type="text" name="title"
                               value="{{ $data->meta->title??'' }}" placeholder="@lang('news.vn_title_holder')" >
                    </div>

                    <div class="form-group">
                        <label for="file-input">@lang('news.image')</label>
                        <input id="file-input" type="file" name="image">

                        <div class="row">
                            @if(!empty($data->meta->image))
                                <div class="col-12 mt-xxl-2">
                                    <img src='{{ asset('storage'.$data->meta->image->full_img) }}' class="img-thumbnail" style="max-width: 450px">
                                </div>
                            @endif
                        </div>
                    </div>

{{--                    <div class="form-group">--}}
{{--                        <label for="textarea-input">@lang('news.link_type')</label>--}}
{{--                        <select class="form-control changeType" name="link_type" data-item="{{$data->meta->link_type??''}}" >--}}
{{--                            <option {{ ( isset($data->meta->link_type) && $data->meta->link_type == 'display_url') ? 'selected' : '' }}--}}
{{--                                    value="display_url">URL</option>--}}
{{--                            <option {{ ( isset($data->meta->link_type) && $data->meta->link_type == 'content') ? 'selected' : '' }}--}}
{{--                                    value="content">Content</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}

                    <div class="form-group display_url" >
                        <label for="display_url-input">@lang('news.display_url')</label>
                        <input class="form-control" id="display_url-input" type="text" name="display_url" value="{{$data->meta->display_url??''}}"  >
                    </div>

                    <div class="form-group content"  style="display: none">
                        <label for="content-input">@lang('news.content')</label>
                        <textarea class="tmtInput" name="content" >{{$data->meta->content??''}}</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                    <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card card-accent-primary">
                <div class="card-header">
                    @lang('news.general_info')
                </div>
                <div class="card-body">

                    <div class="form-group display_url" >
                        <label for="order-input">@lang('news.cat_order')</label>
                        <input class="form-control" id="order-input" type="number" name="order" value="{{ $data->order }}"  >
                    </div>

                    <div class="form-group mt-3">
                        <label for="slt_status">@lang('news.status')</label>
                        <select class="form-control" id="slt_status" name="status">
                            <option {{ ($data->status == 'published') ? 'selected' : '' }} value="published">@lang('news.status_published')</option>
                            <option {{ ($data->status == 'draft') ? 'selected' : '' }} value="draft">@lang('news.status_draft')</option>
                        </select>
                    </div>

{{--                    <div class="form-group mt-3">--}}
{{--                        <label for="slt_lang">@lang('news.post_lang')</label>--}}
{{--                        <select class="form-control" id="slt_lang" name="lang_code">--}}
{{--                            <option {{ ( $lang == 'vn') ? 'selected' : '' }}  value="vn">@lang('news.vn_lang')</option>--}}
{{--                            <option {{ ( $lang == 'en') ? 'selected' : '' }}  value="en">@lang('news.en_lang')</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>

    </div>
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('node_modules/tinymce/tinymce.js') }}"></script>
    <script>
        var currentUrl = '{{Request::url()}}';
        tinymce.init({
            selector:'textarea.tmtInput',
            height: 450,
            menubar:false,
            statusbar: false,
        });
        $(document).ready(function () {
            var selCurrent = $('.changeType').attr('data-item');
            if ( selCurrent == 'display_url' ) {
                $('.changeType').parent().parent().find('.display_url').show();
                $('.changeType').parent().parent().find('.content').hide();
            }
            if ( selCurrent == 'content' ) {
                $('.changeType').parent().parent().find('.display_url').hide();
                $('.changeType').parent().parent().find('.content').show();
            }

            $('.changeType').change(function (e) {
                var inpType = $('.changeType').val();

                if ( inpType == 'display_url' ) {
                    $(this).parent().parent().find('.display_url').show();
                    $(this).parent().parent().find('.content').hide();
                }
                if ( inpType == 'content' ) {
                    $(this).parent().parent().find('.display_url').hide();
                    $(this).parent().parent().find('.content').show();
                }

            });

            $('#slt_lang').change(function (e) {
                var lang = $('#slt_lang').val();
                window.location.replace(currentUrl+'?lang='+lang);
            })
        })
    </script>
@endsection
