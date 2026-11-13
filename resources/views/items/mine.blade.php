
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4">My items</h2>
    </x-slot>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Company</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->company->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No items assigned to you.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
