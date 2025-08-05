@props([
    'paginator' => null,
])

<div>
    {{ $paginator->withQueryString()->links('vendor.pagination.tailwind') }}
</div>