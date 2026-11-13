

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                {{ $item->name }} ({{ $item->quantity }} pcs)
            </h2>

            <a href="{{ route('companies.warehouse.index', $company) }}"
               class="btn btn-secondary">
                Back to Warehouse
            </a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            @include('companies.warehouse.partials.assign-item-form')

            @include('companies.warehouse.partials.assignment-history')
        </div>
    </div>
</x-app-layout>
