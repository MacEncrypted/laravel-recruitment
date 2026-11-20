<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">My items</h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card">
                @if ($assignments->isEmpty())
                    <div class="card-body text-center text-muted py-5">
                        You have no items assigned right now.
                    </div>
                @else
                    <div class="card-body">
                        <div class="table-responsive position-relative">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Pieces</th>
                                    <th scope="col">Since</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->item->name }}</td>
                                        <td>{{ $assignment->item->company->name }}</td>
                                        <td>{{ $assignment->quantity }}</td>
                                        <td class="text-nowrap">{{ $assignment->assigned_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
