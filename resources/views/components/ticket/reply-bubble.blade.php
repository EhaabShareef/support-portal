<div class="flex gap-3">
    <div class="flex-1">
        <div class="text-sm text-neutral-700 dark:text-neutral-300">{{ $item->message }}</div>
        <span class="text-xs text-neutral-500">{{ $item->created_at }}</span>
        @if($item->attachments && $item->attachments->count())
            <ul class="mt-2 text-xs text-neutral-600">
                @foreach($item->attachments as $att)
                    <li><a href="{{ route('attachments.download', $att->uuid) }}" class="underline">{{ $att->original_name }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
