@extends('backend.layouts.app')

@section('title', __('news.add_manual'))

@section('content')
    <form action="{{ route('admin.manual.add_action') }}" method="post" enctype="multipart/form-data">
        @csrf
    <div class="row">
        <div class="col-8">
            <div class="card card-accent-primary">
                <div class="card-body">
                    <div class="form-group">
                        <label for="title-input">@lang('news.title') <span style="color: red">*</span></label>
                        <input class="form-control" id="title-input" type="text" name="title"  placeholder="@lang('news.vn_title_holder')" >
                    </div>

{{--                    <div class="form-group">--}}
{{--                        <label for="file-input">@lang('news.image')</label>--}}
{{--                        <input id="file-input" type="file" name="image">--}}
{{--                    </div>--}}

                    <div class="form-group">
                        <label for="textarea-input">@lang('news.content')</label>
                        <textarea class="content" id="textarea-input" name="content" rows="9" placeholder="@lang('news.vn_content_holder')"></textarea>
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
                        <label for="slt_status">@lang('news.status')</label>
                        <select class="form-control" id="slt_status" name="status">
                            <option value="published">@lang('news.status_published')</option>
                            <option value="draft">@lang('news.status_draft')</option>
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
