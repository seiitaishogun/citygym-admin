@extends('backend.layouts.app')

@section('title', __('news.manual_title'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('news.manual_title')

            <div class="card-header-actions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.manual.create')"
                    :text="__('news.add_manual')"
                />
            </div>
        </div>
        <div class="card-body">
            <div class="d-inline-flex float-right">
                <form name="search" method="get">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Tìm kiếm..." aria-label="Recipient's username" name="search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-sm" type="submit" ><span class="fa fa-search"></span> </button>
                        </div>
                    </div>
                </form>
            </div>
            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th>@lang('news.new_id')</th>
                    <th>@lang('news.title')</th>
                    <th>@lang('news.created_at')</th>
                    <th>@lang('news.status')</th>
                    <th>{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @if(count($news) > 0 )
                    @foreach($news as $item)
                        <tr>
                            <td>#{{ $item->new_id }}</td>
                            <td>
                                <a href="{{ route('admin.manual.edit', ['id' => $item->new_id]) }}">
                                    {{ $item->title }}
                                </a>
                            </td>
                            <td>
                                {{ $item->created_date }}
                            </td>
                            <td>
                                @lang("news.status_{$item->status}")
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.manual.edit', ['id' => $item->new_id]) }}">
                                    <i class="fa fa-edit text-white"></i>
                                </a>
                                <a class="btn btn-sm btn-danger" href="{{ route('admin.manual.delete', ['id' => $item->new_id]) }}">
                                    <i class="fa fa-trash-alt text-white"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7">
                            {{ __('No Record Found') }}
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>

            {{ $news->links() }}
        </div>
    </div>
@endsection
