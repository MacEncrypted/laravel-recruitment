
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Assignment History</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Assigned At</th>
                        <th>Unassigned At</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($item->users->sortByDesc('pivot.assigned_at') as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->pivot->assigned_at }}</td>
                            <td>
                                {{ $user->pivot->unassigned_at ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No assignment history yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
