<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Users in {{ $company->name }}</h2>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back to Companies</a>
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

            <!-- Formularz dodawania użytkownika -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Assign User to Company</h5>
                </div>
                <div class="card-body">
                    @if($availableUsers->count() > 0)
                        <form action="{{ route('companies.users.attach', $company) }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-9">
                                <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User...</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark w-100">Assign User</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted mb-0">All users are already assigned to this company.</p>
                    @endif
                </div>
            </div>

            <!-- Lista przypisanych użytkowników -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Assigned At</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->pivot->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form action="{{ route('companies.users.detach', [$company, $user]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this user from company?')">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No users assigned to this company yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
