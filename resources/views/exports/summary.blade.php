@extends('exports.layout')

@section('title', 'Rapport de synthèse')

@section('content')
    <h2>Rapport de synthèse — du {{ $from }} au {{ $to }}</h2>

    <table class="summary-table">
        <thead>
            <tr>
                <th class="text-center">Sites</th>
                <th class="text-center">Modifications</th>
                <th class="text-center">Interventions</th>
                <th class="text-center">Terminées</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $sitesCount }}</td>
                <td>{{ $modifications['total'] }}</td>
                <td>{{ $interventions['total'] }}</td>
                <td>{{ $interventions['by_status']['terminee'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Détail des modifications par action</h2>

    <table>
        <thead>
            <tr>
                <th>Action</th>
                <th class="text-center">Nombre</th>
            </tr>
        </thead>
        <tbody>
            @forelse($modifications['by_action'] as $action => $count)
                <tr>
                    <td>
                        @switch($action)
                            @case('created') Création @break
                            @case('updated') Modification @break
                            @case('deleted') Suppression @break
                            @default {{ $action }}
                        @endswitch
                    </td>
                    <td class="text-center">{{ $count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">Aucune modification sur la période</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Détail des interventions par statut</h2>

    <table>
        <thead>
            <tr>
                <th>Statut</th>
                <th class="text-center">Nombre</th>
            </tr>
        </thead>
        <tbody>
            @forelse($interventions['by_status'] as $status => $count)
                <tr>
                    <td>
                        <span class="badge badge-{{ $status }}">
                            @switch($status)
                                @case('planifiee') Planifiée @break
                                @case('en_cours') En cours @break
                                @case('terminee') Terminée @break
                                @case('annulee') Annulée @break
                                @default {{ $status }}
                            @endswitch
                        </span>
                    </td>
                    <td class="text-center">{{ $count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">Aucune intervention sur la période</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
