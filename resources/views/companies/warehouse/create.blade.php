<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Add item – {{ $company->name }}</h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('companies.warehouse.items.store', $company) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           maxlength="255"
                                           @error('name') aria-describedby="name-error" @enderror
                                           required>
                                    @error('name')
                                    <div id="name-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Pieces</label>
                                    <input type="number"
                                           class="form-control @error('quantity') is-invalid @enderror"
                                           id="quantity"
                                           name="quantity"
                                           value="{{ old('quantity', 1) }}"
                                           min="1"
                                           max="100000"
                                           step="1"
                                           @error('quantity') aria-describedby="quantity-error" @enderror
                                           required>
                                    @error('quantity')
                                    <div id="quantity-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('companies.warehouse.index', $company) }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-dark">Add item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
