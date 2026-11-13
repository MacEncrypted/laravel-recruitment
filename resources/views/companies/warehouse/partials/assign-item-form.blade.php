
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Assign Item To Company Member</h5>
    </div>

    <div class="card-body">
        @if ($users->isNotEmpty())
            <form method="POST"
                  action="{{ route('companies.warehouse.assign', [$company, $item]) }}"
                  class="row g-3">
                @csrf

                <div class="col-md-9">
                    <select name="user_id"
                            class="form-select @error('user_id') is-invalid @enderror"
                            required>
                        <option value="">Select Company Member...</option>

                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                @selected(old('user_id', $item->currentUser->first()?->id) == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>

                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark w-100">
                        {{ $item->currentUser->isEmpty() ? 'Assign Item' : 'Reassign Item' }}
                    </button>
                </div>
            </form>
            
            @if ($item->currentUser->isNotEmpty())
                <form method="POST"
                    action="{{ route('companies.warehouse.unassign', [$company, $item]) }}"
                    class="mt-3">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-outline-danger"
                            onclick="return confirm('Return this item to warehouse?')">
                        Unassign Item
                    </button>
                </form>
            @endif
        @else
            <p class="text-muted mb-0">
                No users available in this company.
            </p>
        @endif
    </div>
</div>
