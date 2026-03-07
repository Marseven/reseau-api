@extends('exports.layout')

@section('title', "Rapport des interventions" . (isset($from) ? " du {$from} au {$to}" : ''))

@section('content')
    <h2>Résumé par technicien</h2>
    <table>
        <thead>
            <tr>
                <th>Technicien</th>
                <th class="text-center">Total</th>
                <th class="text-center">Terminées</th>
                <th class="text-center">En cours</th>
                <th class="text-center">Planifiées</th>
            </tr>
        </thead>
        <tbody>
            @foreach($technicienSummaries as $ts)
                <tr>
                    <td><strong>{{ $ts['name'] }}</strong></td>
                    <td class="text-center">{{ $ts['total'] }}</td>
                    <td class="text-center">{{ $ts['terminee'] }}</td>
                    <td class="text-center">{{ $ts['en_cours'] }}</td>
                    <td class="text-center">{{ $ts['planifiee'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($technicienSummaries as $ts)
        @if(count($ts['maintenances']) > 0)
            <h2>{{ $ts['name'] }}</h2>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Priorité</th>
                        <th>Status</th>
                        <th>Site</th>
                        <th>Date prévue</th>
                        <th>Durée (min)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ts['maintenances'] as $m)
                        <tr>
                            <td>{{ $m->code }}</td>
                            <td>{{ $m->title }}</td>
                            <td>{{ $m->type }}</td>
                            <td>{{ $m->priority }}</td>
                            <td>
                                <span class="badge badge-{{ $m->status }}">
                                    {{ $m->status }}
                                </span>
                            </td>
                            <td>{{ $m->site->name ?? '-' }}</td>
                            <td>{{ $m->scheduled_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-center">{{ $m->duration_minutes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
@endsection
