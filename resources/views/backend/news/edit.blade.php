@extends('backend.layouts.app')

@section('title', __('news.edit_new'))

@section('content')
    <form action="{{ route('admin.news.edit_action', [$item->new_id]) }}" method="post" enctype="multipart/form-data">
        @csrf
    <div class="row">
        <div class="col-8">
            <div class="card card-accent-primary">
                <div class="card-body">
                    <div class="form-group">
                        <label for="title-input">@lang('news.title')</label>
                        <input class="form-control" id="title-input" type="text" name="title"
                               value="{{ $item->meta->title ??''}}" placeholder="@lang('news.vn_title_holder')" >
                    </div>

                    <div class="form-group">
                        <label for="file-input">@lang('news.image')</label>
                        <input id="file-input" type="file" name="image">
                        <div class="row">
                            @if(!empty($item->meta->image))
                                <div class="col-12 mt-xxl-2">
                                    <img src='{{ asset('storage'.$item->meta->image->thumb) }}' class="img-thumbnail" style="max-width: 450px">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="display_url-input">@lang('news.display_url')</label>
                        <input class="form-control" id="display_url-input" type="text" name="display_url"
                               value="{{ $item->meta->display_url }}" placeholder="@lang('news.vn_display_url_holder')" >
                    </div>

                    <div class="form-group">
                        <label for="textarea-input">@lang('news.content')</label>
                        <textarea class="content" id="textarea-input" name="content" rows="9" placeholder="@lang('news.vn_content_holder')">{{ $item->meta->content }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    @lang('news.general_info')
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="form-check checkbox">
                            <input class="form-check-input" id="chkurl_display" type="checkbox" name="is_url_display"
                                   {!! $item->is_url_display == 1 ? "checked='checked'" : "" !!} value="1">
                            <label class="form-check-label" for="chkurl_display">@lang('news.is_url_display')</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="slt_status">@lang('news.status')</label>
                        <select class="form-control" id="slt_status" name="status">
                            <option {!! $item->status == 'published' ? "selected='selected'" : "" !!} value="published">@lang('news.status_published')</option>
                            <option {!! $item->status == 'draft' ? "selected='selected'" : "" !!} value="draft">@lang('news.status_draft')</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="slt_status">@lang('news.categories')</label>
                        <select class="form-control" id="slt_status" name="cat_id">
                            @if(count($cat_data) > 0 )
                                <option value="0">@lang('news.select_cat')</option>
                                @foreach($cat_data as $cat)
                                    <option {!! $item->cat_id == $cat->cat_id ? "selected='selected'" : "" !!} value="{{ $cat->cat_id }}" >{{ $cat->cat_name }}</option>
                                @endforeach
                            @else
                                <option value="0">@lang('news.empty_cat')</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class="card">
            <div class="card-footer">
                <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('node_modules/tinymce/tinymce.js') }}"></script>
    <script>
        var base_url = '{{ URL::to('/') }}/admin';
        tinymce.init({
            selector:'textarea.content',
            height: 450,
            menubar:false,
            statusbar: false,
            plugins: [
                'advlist autolink lists link image media charmap print preview anchor',
                'searchreplace visualblocks code fullscreen hr emoticons',
                'insertdatetime media table paste help imagetools wordcount',
            ],
            toolbar: 'undo redo | bold italic blockquote forecolor backcolor | alignleft aligncenter alignright alignjustify hr emoticons | bullist numlist outdent indent | link image media | removeformat | code | help',
            image_caption: true,
            image_advtab: true,
            image_uploadtab: true,
            images_upload_credentials: true,
            images_upload_handler: function (blobInfo, success, failure, progress) {
                var xhr, formData;
                let uploadUrl = base_url+"/attach/mceUpload";
                xhr = new XMLHttpRequest();

                xhr.withCredentials = false;
                xhr.open('POST', uploadUrl, true);
                xhr.setRequestHeader("x-csrf-token", "{{ csrf_token() }}");

                xhr.upload.onprogress = function (e) {
                    progress(e.loaded / e.total * 100);
                };

                xhr.onload = function() {
                    var json;

                    if (xhr.status < 200 || xhr.status >= 300) {
                        failure('HTTP Error: ' + xhr.status);
                        return;
                    }

                    json = JSON.parse(xhr.responseText);

                    if ( json.error ) {
                        failure(json.errMess);
                        return;
                    }
                    if (!json || typeof json.location != 'string') {
                        failure('Invalid JSON: ' + xhr.responseText);
                        return;
                    }

                    success(json.location);
                };

                xhr.onerror = function () {
                    failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                };

                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                xhr.send(formData);
            },
            relative_urls: false,
            remove_script_host: false,
        });
    </script>
@endsection
