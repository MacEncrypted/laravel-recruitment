<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Company Details</h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">UUID:</dt>
                                <dd class="col-sm-9"><code>{{ $company->uuid }}</code></dd>

                                <dt class="col-sm-3">Name:</dt>
                                <dd class="col-sm-9">{{ $company->name }}</dd>

                                <dt class="col-sm-3">Created:</dt>
                                <dd class="col-sm-9">{{ $company->created_at->format('Y-m-d H:i:s') }}</dd>

                                <dt class="col-sm-3">Updated:</dt>
                                <dd class="col-sm-9">{{ $company->updated_at->format('Y-m-d H:i:s') }}</dd>
                            </dl>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back to List</a>
                                <div>
                                    <a href="{{ route('companies.users.index', $company) }}" class="btn btn-success">Users</a>
                                    @can('update', $company)
                                        <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning">Edit</a>
                                    @endcan
                                    @can('delete', $company)
                                        <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
