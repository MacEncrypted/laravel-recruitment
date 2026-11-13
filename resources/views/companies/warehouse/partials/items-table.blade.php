
<div class="card">
    <div class="card-body">
        @if ($items->isEmpty())
            <div class="text-center text-muted">
                No items in the warehouse yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Assigned to</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td><span class="badge bg-primary">{{ $item->quantity }}</span></td>
                                <td>
                                    {{ $item->currentUser->first()?->name ?? 'Unassigned' }}
                                </td>
                                <td>
                                    <a href="{{ route('companies.warehouse.show', [$company, $item]) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
