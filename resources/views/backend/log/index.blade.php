@extends('backend.layouts.app')

@section('title', __('news.news_title'))

@section('content')
    <div class="card">
        <div class="card-header">
            {{ __('Logs') }}

            <div class="card-header-actions">
                <input type="text" name="search" >
            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th>{{ __('id') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @if(count($logs) > 0 )
                    @foreach($logs as $item)
                        <tr>
                            <td>#{{ $item->id }}</td>
                            <td>#{{ $item->log_name  }}</td>
                            <td>#{{ $item->description  }}</td>
                            <td>
                                {{ $item->created_at }}
                            </td>
                            <td>
                                <a class="btn btn-sm btn-info" href="{{ route('admin.log.view-log', ['id' => $item->id]) }}">
                                    <i class="fa fa-search text-white"></i>
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

        </div>
    </div>
@endsection
