@extends('layouts.app')

@section('title', 'Confirm Deletion')

@section('content')
    <x-ui.confirm-delete 
        title="Are you sure you want to delete this organization?" 
        :message="'This action cannot be undone. The organization <strong>' . $organization->name . '</strong> and all related data will be permanently removed.'"
        :confirm-action="route('organizations.destroy', $organization)"
        :cancel-url="route('organizations.show', $organization)" />
@endsection
