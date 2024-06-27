@extends('backend.layouts.app')

@section('title', __('news.banner_page'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('news.banner_page')

            <div class="card-header-actions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.banner.add')"
                    :text="__('news.add_banner')"
                />
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banner.index') }}" method="get" >
                <div class="float-right" style="width: 450px">
                <div class="form-group ">
                    <div class="input-group">
                        <input class="form-control" type="text" name="s" placeholder="Tìm kiếm" >
                        <span class="input-group-append">
                                <button class="btn btn-primary" type="submit" id="tmtSearch" >Submit</button>
                            </span>
                    </div>
                </div>
                </div>

            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th>@lang('news.banner_id')</th>
                    <th>
                        @if( isset($order) && $order == 'desc')
                            <a href="{{ route('admin.banner.index', ['order' => 'asc']) }}">
                                <i class="c-icon cil-arrow-thick-bottom"></i>
                                @lang('news.banner_title')
                            </a>
                        @else
                            <a href="{{ route('admin.banner.index', ['order' => 'desc']) }}">
                                <i class="c-icon cil-arrow-thick-top"></i>
                                @lang('news.banner_title')
                            </a>
                        @endif
                    </th>
                    <th>@lang('news.banner_status')</th>
                    <th>@lang('news.banner_image')</th>
                    <th>@lang('news.cat_order')</th>
                    <th>@lang('news.created_at')</th>
                    <th>@lang('news.updated_at')</th>
                    <th>{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @if(count($data) > 0 )
                    @foreach($data as $item)
                        <tr>
                            <td>#{{ $item->banner_id }}</td>
                            <td>
                                <a href="{{ route('admin.banner.edit', ['id' => $item->banner_id]) }}">
                                    {{ $item->title }}
                                </a>
                            </td>
                            <td>
                                @lang("news.status_{$item->status}")
                            </td>
                            <td>
                                <img src='{{ asset('storage'.(is_object($item->image)?$item->image->full_img:$item->image)) }}' class="img-thumbnail" style="max-width: 300px">
                            </td>
                            <td>
                                {{ $item->order }}
                            </td>
                            <td>
                                {{ $item->created_date }}
                            </td>
                            <td>
                                {{ $item->updated_at }}
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.banner.edit', ['id' => $item->banner_id]) }}">
                                    <i class="fa fa-edit text-white"></i>
                                </a>
                                <a class="btn btn-sm btn-danger" href="{{ route('admin.banner.delete', ['id' => $item->banner_id]) }}">
                                    <i class="fa fa-trash-alt text-white"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6">
                            {{ __('No Record Found') }}
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
            </form>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
