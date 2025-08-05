@extends('layouts.app')

@section('title', 'Confirm Deletion')

@section('content')
    <x-ui.confirm-delete
        title="Are you sure you want to delete this contract?"
        :message="'This action cannot be undone. The contract for <strong>' . $contract->organization->name . '</strong> (' . $contract->department->name . ') will be permanently removed.'"
        :confirm-action="route('contracts.destroy', $contract)"
        :cancel-url="route('contracts.show', $contract)"
    />
@endsection
