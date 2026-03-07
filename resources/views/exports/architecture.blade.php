@extends('exports.layout')

@section('title', 'Architecture réseau')

@section('styles')
<style>
    @page { size: A4 landscape; }
</style>
@endsection

@section('content')
    <h2>Résumé</h2>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Sites</th>
                <th>Zones</th>
                <th>Bâtiments</th>
                <th>Salles</th>
                <th>Armoires</th>
                <th>Équipements</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $counts['sites'] ?? 0 }}</td>
                <td>{{ $counts['zones'] ?? 0 }}</td>
                <td>{{ $counts['batiments'] ?? 0 }}</td>
                <td>{{ $counts['salles'] ?? 0 }}</td>
                <td>{{ $counts['coffrets'] ?? 0 }}</td>
                <td>{{ $counts['equipements'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    @foreach($sites as $site)
        <h2>{{ $site->name }} ({{ $site->code }})</h2>

        @foreach($site->zones as $zone)
            <h3>Zone : {{ $zone->name }} ({{ $zone->code }})</h3>

            @if($zone->coffrets->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Pièce</th>
                            <th>Type</th>
                            <th>Nb équipements</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zone->coffrets as $coffret)
                            <tr>
                                <td>{{ $coffret->code }}</td>
                                <td>{{ $coffret->name }}</td>
                                <td>{{ $coffret->piece ?? '-' }}</td>
                                <td>{{ $coffret->type ?? '-' }}</td>
                                <td class="text-center">{{ $coffret->equipments->count() }}</td>
                                <td>
                                    <span class="badge badge-{{ $coffret->status ?? 'inactive' }}">
                                        {{ $coffret->status ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            @if($coffret->equipments->count() > 0)
                                <tr>
                                    <td colspan="6" style="padding: 0 0 0 20px;">
                                        <table style="margin: 4px 0;">
                                            <thead>
                                                <tr>
                                                    <th style="background: #475569;">Code</th>
                                                    <th style="background: #475569;">Nom</th>
                                                    <th style="background: #475569;">Type</th>
                                                    <th style="background: #475569;">IP</th>
                                                    <th style="background: #475569;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($coffret->equipments as $eq)
                                                    <tr>
                                                        <td>{{ $eq->equipement_code }}</td>
                                                        <td>{{ $eq->name }}</td>
                                                        <td>{{ $eq->type ?? '-' }}</td>
                                                        <td>{{ $eq->ip_address ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $eq->status ?? 'inactive' }}">
                                                                {{ $eq->status ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Aucune armoire dans cette zone.</p>
            @endif
        @endforeach

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection
