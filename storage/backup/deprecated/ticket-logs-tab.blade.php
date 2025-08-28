<div>
    <table class="w-full text-left text-sm">
        <thead>
            <tr>
                <th class="px-2 py-1">Time</th>
                <th class="px-2 py-1">Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr class="border-t">
                    <td class="px-2 py-1">{{ $log->created_at }}</td>
                    <td class="px-2 py-1">{{ $log->message }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
