@extends('backend.layouts.app')

@section('title', __('news.news_title'))

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            Log info : <strong>{{ $log->log_name }}</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed mb-0">
                    <tbody>
                    <tr>
                        <td>Description :</td>
                        <td colspan="7">{{ $log->description }}</td>
                    </tr>
                    <tr>
                        <td>Subject ID :</td>
                        <td>
                            <span class="badge badge-primary">{{ $log->subject_id }}</span>
                        </td>
                        <td>Subject Type  :</td>
                        <td>
                            <span class="badge badge-primary">{{ $log->subject_type }}</span>
                        </td>
                        <td>Object Call :</td>
                        <td>
                            <span class="badge badge-primary">{{ $log->causer_type }}</span>
                        </td>
                        <td>Created at :</td>
                        <td>
                            <span class="badge badge-primary">{{ $log->created_at }}</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div>
            <p>API Data :</p>
            <code>{{ print_r($log->properties) }}</code>
            </div>
        </div>
    </div>
@endsection
