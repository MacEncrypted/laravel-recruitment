
<div class="modal fade" id="createItemModal" tabindex="-1"
     aria-labelledby="createItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"
                  action="{{ route('companies.warehouse.store', $company) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="createItemModalLabel">
                        Add item
                    </h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="itemName" class="form-label">
                            Name
                        </label>
                        <input type="text" id="itemName" name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               required maxlength="255">
                    </div>

                    <div>
                        <label for="itemQuantity" class="form-label">
                            Quantity
                        </label>
                        <input type="number" id="itemQuantity"
                               name="quantity" class="form-control"
                               value="{{ old('quantity', 1) }}"
                               min="1" step="1" required>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-dark">
                        Add item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
