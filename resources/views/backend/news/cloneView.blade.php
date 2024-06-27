@extends('backend.layouts.app')

@section('title', __('Clone Language Content'))

@section('content')

    <form action="{{ route('admin.news.cloneContent') }}" method="get" enctype="multipart/form-data">
        <div class="row">
            <div class="col-12">
                <div class="card card-accent-primary">
                    <div class="card-header">
                        Clone Content
                    </div>
                    <div class="card-body">
                        <p>Nếu có nhiều ID thì mỗi ID cách nhau bởi dấu ;</p>
                        <div class="form-group">
                            <label for="title-input">News ID</label>
                            <input class="form-control @error('ids') is-invalid @enderror" id="ids-input" type="text"
                                   name="ids" value="{{ old('ids') }}" >
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                        <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
