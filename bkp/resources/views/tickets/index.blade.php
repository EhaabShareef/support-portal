@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    {{-- Top bar --}}
    <x-ui.page-header title="Tickets" subtitle="View and manage all support tickets" icon="heroicon-o-ticket"
        :create-route="route('tickets.create')" />

    {{-- Filters & List (Livewire) --}}
    <livewire:ticket-filters />

    {{-- Alert Notification --}}
    @if (session('alert'))
        <x-alert :type="session('alert')['type']" :title="session('alert')['title']" :message="session('alert')['message']" />
    @endif
@endsection
