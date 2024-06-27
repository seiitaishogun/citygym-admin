@extends('backend.layouts.app')

@section('title', __('news.add_banner'))

@section('content')
    <form action="{{ route('admin.banner.add_action') }}" method="post" enctype="multipart/form-data">
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
                        <input class="form-control" id="title-input" type="text" name="title"  placeholder="@lang('news.vn_title_holder')" >
                    </div>

                    <div class="form-group">
                        <label for="file-input">@lang('news.image')</label>
                        <input id="file-input" type="file" name="image">
                    </div>

{{--                    <div class="form-group">--}}
{{--                        <label for="textarea-input">@lang('news.link_type')</label>--}}
{{--                        <select class="form-control changeType" name="link_type" >--}}
{{--                            <option value="display_url">URL</option>--}}
{{--                            <option value="content">Content</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}

                    <div class="form-group display_url" >
                        <label for="display_url-input">@lang('news.display_url')</label>
                        <input class="form-control" id="display_url-input" type="text" name="display_url"   >
                    </div>

                    <div class="form-group content"  style="display: none">
                        <label for="content-input">@lang('news.content')</label>
                        <textarea class="tmtInput" name="content" ></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" value="display_url" name="link_type">
                    <input type="hidden" value="0" name="order">
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
                        <input class="form-control" id="order-input" type="number" name="order"   >
                    </div>

                    <div class="form-group mt-3">
                        <label for="slt_status">@lang('news.status')</label>
                        <select class="form-control" id="slt_status" name="status">
                            <option value="published">@lang('news.status_published')</option>
                            <option value="draft">@lang('news.status_draft')</option>
                        </select>
                    </div>

{{--                    <div class="form-group mt-3">--}}
{{--                        <label for="slt_status">@lang('news.post_lang')</label>--}}
{{--                        <select class="form-control" id="slt_lang" name="lang_code">--}}
{{--                            <option value="vn">@lang('news.vn_lang')</option>--}}
{{--                            <option value="en">@lang('news.en_lang')</option>--}}
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
tinymce.init({
    selector:'textarea.tmtInput',
    height: 450,
    menubar:false,
    statusbar: false,
});
$(document).ready(function () {
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

    })
})
</script>
@endsection
