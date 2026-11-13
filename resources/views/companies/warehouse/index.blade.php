@include('companies.warehouse.partials.create-item-modal')
@if ($errors->createItem->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(
                document.getElementById('createItemModal')
            ).show();
        });
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Warehouse – {{ $company->name }}</h2>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createItemModal">Add item</button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back to Companies</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            @include('companies.warehouse.partials.items-table')
        </div>
    </div>
</x-app-layout>
