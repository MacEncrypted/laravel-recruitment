<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Companies</h2>
            @can('create', App\Models\Company::class)
                <a href="{{ route('companies.create') }}" class="btn btn-dark">Add Company</a>
            @endcan
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>UUID</th>
                                <th>Users</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($companies as $company)
                                <tr>
                                    <td>{{ $company->name }}</td>
                                    @can('view', $company)
                                        {{-- Członek firmy widzi szczegóły --}}
                                        <td><code>{{ $company->uuid }}</code></td>
                                        <td><span class="badge bg-primary">{{ $company->users_count }}</span></td>
                                        <td>{{ $company->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('companies.users.index', $company) }}" class="btn btn-sm btn-success">Users</a>
                                                <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info">View</a>
                                                @can('manageWarehouse', $company)
                                                    <a href="{{ route('companies.warehouse.index', $company) }}" class="btn btn-sm btn-secondary">Warehouse</a>
                                                @endcan
                                                @can('update', $company)
                                                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning">Edit</a>
                                                @endcan
                                                @can('delete', $company)
                                                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    @else
                                        {{-- Osoba spoza firmy widzi tylko nazwę --}}
                                        <td colspan="4" class="text-muted small">You are not a member of this company.</td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No companies found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $companies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
