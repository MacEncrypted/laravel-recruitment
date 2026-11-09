<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Warehouse – {{ $company->name }}</h2>
            <div class="d-flex gap-2">
                {{-- TODO: podpiąć dodawanie przedmiotu --}}
                <button type="button" class="btn btn-dark">Add item</button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back to Companies</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    No items in the warehouse yet.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
