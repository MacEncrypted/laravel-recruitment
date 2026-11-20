<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
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
                        <div class="table-responsive position-relative">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Pieces</th>
                                    <th scope="col">Available</th>
                                    <th scope="col">Held by</th>
                                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->available() }}</td>
                                        <td>
                                            @forelse ($item->openAssignments as $assignment)
                                                <div>{{ $assignment->user->name }} <span class="text-muted">× {{ $assignment->quantity }}</span></div>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('companies.warehouse.items.show', [$company, $item]) }}" class="btn btn-sm btn-dark">
                                                Manage<span class="visually-hidden"> {{ $item->name }}</span>
                                            </a>
                                        </td>
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
