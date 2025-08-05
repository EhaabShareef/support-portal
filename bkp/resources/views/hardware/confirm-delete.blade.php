@extends('layouts.app')

@section('title', 'Confirm Hardware Deletion')

@section('content')
    <x-ui.confirm-delete
        title="Are you sure you want to delete this hardware record?"
        :message="'This action cannot be undone. The hardware <strong>' . $hardware->hardware_model . '</strong> (Serial #: ' . $hardware->serial_number . ') will be permanently removed.'"
        :confirm-action="route('hardware.destroy', $hardware)"
        :cancel-url="route('hardware.index')"
    />
@endsection