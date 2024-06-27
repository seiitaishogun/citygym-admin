@extends('backend.layouts.app')

@section('title', __('news.add_banner'))

@section('content')
    <form action="{{ route('admin.banner.add_action') }}" method="post" enctype="multipart/form-data">
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
                                    <input class="form-control" id="title-input" type="text" name="vn[title]"  placeholder="@lang('news.vn_title_holder')" >
                                </div>

                                <div class="form-group">
                                    <label for="file-input">@lang('news.image')</label>
                                    <input id="file-input" type="file" name="vn[image]">
                                </div>

                                <div class="form-group">
                                    <label for="textarea-input">@lang('acp.noti_sentType')</label>
                                    <select class="form-control changeType" name="vn[banner_type]" >
                                        <option value="display_url">URL</option>
                                        <option value="content">Content</option>
                                    </select>
                                </div>

                                <div class="form-group display_url" >
                                    <label for="display_url-input">@lang('news.display_url')</label>
                                    <input class="form-control" id="display_url-input" type="text" name="vn[display_url]"   >
                                </div>

                                <div class="form-group content"  style="display: none">
                                    <label for="content-input">@lang('news.content')</label>
                                    <textarea class="tmtInput" name="vn[content]" ></textarea>
                                </div>

                            </div>
                            <div class="tab-pane" id="banner-en" role="tabpanel">
                                <div class="form-group">
                                    <label for="title-input">@lang('news.title')</label>
                                    <input class="form-control" id="title-input" type="text" name="en[title]"  placeholder="@lang('news.en_title_holder')" >
                                </div>

                                <div class="form-group">
                                    <label for="file-input">@lang('news.image')</label>
                                    <input id="file-input" type="file" name="en[image]">
                                </div>

                                <div class="form-group">
                                    <label for="textarea-input">@lang('acp.noti_sentType')</label>
                                    <select class="form-control changeType" name="en[banner_type]" >
                                        <option value="display_url">URL</option>
                                        <option value="content">Content</option>
                                    </select>
                                </div>

                                <div class="form-group display_url" >
                                    <label for="display_url-input">@lang('news.display_url')</label>
                                    <input class="form-control" id="display_url-input" type="text" name="en[display_url]"   >
                                </div>

                                <div class="form-group content"  style="display: none">
                                    <label for="content-input">@lang('news.content')</label>
                                    <textarea class="tmtInput" name="en[content]" ></textarea>
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
