<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Warehouse – {{ $company->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('companies.warehouse.items.create', $company) }}" class="btn btn-dark">Add item</a>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back to Companies</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif

            <div class="card">
                @if ($items->isEmpty())
                    <div class="card-body text-center text-muted py-5">
                        No items in the warehouse yet.
                    </div>
                @else
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Pieces</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
