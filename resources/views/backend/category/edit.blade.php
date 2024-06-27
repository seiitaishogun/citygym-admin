@extends('backend.layouts.app')

@section('title', __('news.edit_category'))

@section('content')
    <x-forms.patch action="{{ route('admin.category.edit_action', $cat->cat_id) }}" >
        @method('post')
        <x-backend.card>
            <x-slot name="header">
                @lang('news.edit_category')
            </x-slot>

            <x-slot name="body">

                <div class="form-group">
                    <label for="title-input">@lang('news.title')</label>
                    <input class="form-control" id="title-input" type="text" name="cat_name"
                           value="{{ $cat->getNameByLocate($cat->cat_id, 'vn') }}" placeholder="@lang('news.vn_title_holder')" >
                </div>

                <div class="form-group mt-3">
                    <label for="slt_status">@lang('news.cat_parent')</label>
                    <select class="form-control" id="slt_status" name="parent_id">
                        @if(count($cat_parent) > 0 )
                            <option value="0">@lang('news.select_cat')</option>
                            @foreach($cat_parent as $item)
                                <option value="{{ $item->cat_id }}" {!! $cat->parent_id == $item->cat_id ? "selected='selected'" : "" !!} >{{ $item->cat_name }}</option>
                            @endforeach
                        @else
                            <option value="0">@lang('news.empty_cat')</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="thutu">@lang('news.cat_order')</label>
                    <input class="form-control" id="thutu" type="text" name="thutu" value="{{ $cat->thutu }}" >
                </div>

                <div class="form-group">
                    <label for="slt_status">@lang('news.status')</label>
                    <select class="form-control" id="slt_status" name="status">
                        <option value="published" {!! $cat->status == 'published' ? "selected='selected'" : "" !!} >@lang('news.status_published')</option>
                        <option value="draft" {!! $cat->status == 'draft' ? "selected='selected'" : "" !!} >@lang('news.status_draft')</option>
                    </select>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary" type="submit"> {{ __('Save') }}</button>
                <a class="btn btn-sm btn-danger text-white" href="" > {{ __('Cancel') }}</a>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection
