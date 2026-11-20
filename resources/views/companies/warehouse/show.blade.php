<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ $item->name }} – {{ $company->name }}</h2>
            <a href="{{ route('companies.warehouse.index', $company) }}" class="btn btn-secondary">Back to Warehouse</a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif

            @if ($errors->has('assignment') || $errors->reassign->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first('assignment') ?: $errors->reassign->first() }}
                </div>
            @endif

            @php($available = $item->available())

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Assign pieces</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">{{ $available }} of {{ $item->quantity }} pieces available.</p>

                    @if ($available > 0)
                        <form action="{{ route('companies.warehouse.assignments.store', [$company, $item]) }}" method="POST" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">Person</label>
                                <select id="user_id" name="user_id"
                                        class="form-select @error('user_id') is-invalid @enderror"
                                        @error('user_id') aria-describedby="user_id-error" @enderror
                                        required>
                                    <option value="">Select a member…</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                <div id="user_id-error" class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="quantity" class="form-label">Pieces</label>
                                <input type="number" id="quantity" name="quantity"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity', 1) }}" min="1" max="{{ $available }}" step="1"
                                       @error('quantity') aria-describedby="quantity-error" @enderror
                                       required>
                                @error('quantity')
                                <div id="quantity-error" class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark w-100">Assign</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted mb-0">All pieces are assigned. Return some to the warehouse to assign them again.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Currently assigned</h3>
                </div>
                <div class="card-body">
                    @if ($item->openAssignments->isEmpty())
                        <p class="text-muted mb-0">No pieces are assigned.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Person</th>
                                    <th scope="col">Pieces</th>
                                    <th scope="col">Since</th>
                                    <th scope="col">Reassign to</th>
                                    <th scope="col"><span class="visually-hidden">Return</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($item->openAssignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->user->name }}</td>
                                        <td>{{ $assignment->quantity }}</td>
                                        <td>{{ $assignment->assigned_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <form action="{{ route('companies.warehouse.assignments.update', [$company, $item, $assignment]) }}" method="POST" class="d-flex gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <label for="reassign-{{ $assignment->id }}" class="visually-hidden">
                                                    Reassign {{ $assignment->quantity }} pieces held by {{ $assignment->user->name }} to
                                                </label>
                                                <select id="reassign-{{ $assignment->id }}" name="user_id" class="form-select form-select-sm" required>
                                                    <option value="">Select a member…</option>
                                                    @foreach ($members as $member)
                                                        @continue($member->id === $assignment->user_id)
                                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-dark">Reassign</button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('companies.warehouse.assignments.return', [$company, $item, $assignment]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">
                                                    Return<span class="visually-hidden"> {{ $assignment->quantity }} pieces held by {{ $assignment->user->name }}</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
