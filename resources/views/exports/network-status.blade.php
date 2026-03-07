@extends('exports.layout')

@section('title', "Rapport d'état global du réseau")

@section('content')
    <h2>État global du réseau</h2>

    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th class="text-center">Zones (total)</th>
                <th class="text-center">Zones actives</th>
                <th class="text-center">Armoires (total)</th>
                <th class="text-center">Armoires actives</th>
                <th class="text-center">Équipements (total)</th>
                <th class="text-center">Équipements actifs</th>
                <th class="text-center">Ports (total)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siteStats as $stat)
                <tr>
                    <td><strong>{{ $stat['name'] }}</strong></td>
                    <td class="text-center">{{ $stat['zones_total'] }}</td>
                    <td class="text-center">{{ $stat['zones_active'] }}</td>
                    <td class="text-center">{{ $stat['coffrets_total'] }}</td>
                    <td class="text-center">{{ $stat['coffrets_active'] }}</td>
                    <td class="text-center">{{ $stat['equipements_total'] }}</td>
                    <td class="text-center">{{ $stat['equipements_active'] }}</td>
                    <td class="text-center">{{ $stat['ports_total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
