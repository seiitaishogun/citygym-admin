@extends('backend.layouts.app')

@section('title', __('User Management'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('User Management')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="btn btn-sm btn-success" :href="route('admin.export.export_duplicate_user')" :text="__('Xuất Excel User Trùng')" />
        </x-slot>

        <x-slot name="body">
            <livewire:backend.crm-users-table />
        </x-slot>
    </x-backend.card>
@endsection
