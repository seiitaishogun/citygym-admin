@extends('backend.layouts.app')

@section('title', __('crm.order_class_title'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('crm.order_class_title')
        </div>

        <div class="card-body">
            <form action="" method="post" >
                @csrf
            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('crm.class_name')</th>
                    <th>@lang('crm.class_code')</th>
                    <th>@lang('crm.class_group')</th>
                    <th>@lang('crm.class_order')</th>
                </tr>
                </thead>
                <tbody>
                @if(count($classes) > 0 )
                    @foreach($classes as $item)
                        <tr>
                            <td>
                                #{{ $item->Id }}
                                <input type="hidden" name="id[]" value="{{ $item->Id }}" >
                            </td>
                            <td>
                                {{ $item->Name }}
                            </td>
                            <td>
                                {{ $item->Class_Code__c }}
                            </td>
                            <td>
                                {{ $item->group->Name }}
                            </td>
                            <td>
                                <input type="text" name="thutu[]" value="{{ $item->thutu }}" >
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
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <button type="submit" class="btn btn-success btn-sm">Save</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            </form>
        </div>
    </div>
@endsection
