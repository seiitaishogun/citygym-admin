@extends('backend.layouts.app')

@section('title', __('news.news_title'))

@section('content')
    <div class="row">
        <div class="col-12 col-sm-5">
            <form action="{{ route('admin.category.add_action') }}" method="post" >
                @csrf
            <div class="card">
                <div class="card-header">
                    @lang('news.create_category')
                </div>
                <div class="card-body">

                    <div class="tab-pane active" id="new-vn" role="tabpanel">
                        <div class="form-group">
                            <label for="title-input">@lang('news.title') <span style="color: red">*</span> </label>
                            <input class="form-control" id="title-input" type="text" name="cat_name" placeholder="@lang('news.vn_title_holder')" >
                        </div>

                    </div>

                    <div class="form-group mt-3">
                        <label for="slt_status">@lang('news.cat_parent')</label>
                        <select class="form-control" id="slt_status" name="parent_id">
                            @if(count($cat_parent) > 0 )
                                <option value="0">@lang('news.select_cat')</option>
                                @foreach($cat_parent as $cat)
                                    <option value="{{ $cat->cat_id }}">{{ $cat->cat_name }}</option>
                                @endforeach
                            @else
                            <option value="0">@lang('news.empty_cat')</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="thutu">@lang('news.cat_order')</label>
                        <input class="form-control" id="thutu" type="text" name="thutu" value="0" >
                    </div>

                    <div class="form-group">
                        <label for="slt_status">@lang('news.status')</label>
                        <select class="form-control" id="slt_status" name="status">
                            <option value="published">@lang('news.status_published')</option>
                            <option value="draft">@lang('news.status_draft')</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-primary" type="submit"> Submit</button>
                    <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                </div>
            </div>
            </form>
        </div>

        <div class="col-12 col-sm-7">
            <div class="card">
                <div class="card-header"> @lang('news.list_category')</div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-striped">
                        <thead>
                        <tr>
                            <th>@lang('news.cat_id')</th>
                            <th>@lang('news.cat_name')</th>
                            <th>@lang('news.cat_parent')</th>
                            <th>@lang('news.cat_order')</th>
                            <th>@lang('news.cat_status')</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($cat_data) > 0 )
                            @foreach($cat_data as $cat)
                                <tr>
                                    <td>#{{ $cat->cat_id }}</td>
                                    <td>
                                        <a href="{{ route('admin.category.edit', ['id' => $cat->cat_id]) }}">
                                        {{ $cat->cat_name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($cat->parent_id > 0 )
                                        {{ $cat->getParentName(app()->getLocale()) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $cat->thutu }}
                                    </td>
                                    <td>
                                        @lang("news.cat_status_{$cat->status}")
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.category.edit', ['id' => $cat->cat_id]) }}">
                                            <i class="fa fa-edit text-white"></i>
                                        </a>
                                        <a class="btn btn-sm btn-danger" href="{{ route('admin.category.delete', ['id' => $cat->cat_id]) }}">
                                            <i class="fa fa-trash-alt text-white"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                        <tr>
                            <td colspan="5">
                                {{ __('No Record Found') }}
                            </td>
                        </tr>
                        @endif
                        </tbody>
                    </table>
{{--                    <ul class="pagination">--}}
{{--                        <li class="page-item"><a class="page-link" href="#">Prev</a></li>--}}
{{--                        <li class="page-item active"><a class="page-link" href="#">1</a></li>--}}
{{--                        <li class="page-item"><a class="page-link" href="#">2</a></li>--}}
{{--                        <li class="page-item"><a class="page-link" href="#">3</a></li>--}}
{{--                        <li class="page-item"><a class="page-link" href="#">4</a></li>--}}
{{--                        <li class="page-item"><a class="page-link" href="#">Next</a></li>--}}
{{--                    </ul>--}}
                </div>
            </div>
        </div>
    </div>
@endsection
