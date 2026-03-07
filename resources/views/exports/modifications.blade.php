@extends('exports.layout')

@section('title', "Rapport des modifications du {$from} au {$to}")

@section('content')
    <h2>Résumé</h2>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total modifications</th>
                @foreach($summary['by_action'] as $action => $count)
                    <th>{{ ucfirst($action) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $summary['total'] }}</td>
                @foreach($summary['by_action'] as $count)
                    <td>{{ $count }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    @if(count($summary['by_user']) > 0)
        <h3>Par utilisateur</h3>
        <table>
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th class="text-center">Nombre</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['by_user'] as $userName => $count)
                    <tr>
                        <td>{{ $userName }}</td>
                        <td class="text-center">{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Détail chronologique</h2>
    @foreach($logsByDate as $date => $logs)
        <h3>{{ $date }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Type</th>
                    <th>ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('H:i:s') }}</td>
                        <td>{{ $log->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $log->action === 'created' ? 'active' : ($log->action === 'deleted' ? 'inactive' : 'maintenance') }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>{{ class_basename($log->entity_type) }}</td>
                        <td>{{ $log->entity_id }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
@endsection
