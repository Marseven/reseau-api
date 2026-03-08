@extends('exports.layout')

@section('title', 'Étiquettes ' . $entityType)

@section('styles')
<style>
    .labels-grid {
        display: table;
        width: 100%;
        border-spacing: 4px;
    }
    .labels-row {
        display: table-row;
    }
    .label-cell {
        display: table-cell;
        vertical-align: top;
        padding: 4px;
    }
    .label {
        border: 1px dashed #94a3b8;
        border-radius: 4px;
        overflow: hidden;
    }
    .label-inner {
        display: table;
        width: 100%;
    }
    .label-qr {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        padding: 4px;
    }
    .label-info {
        display: table-cell;
        vertical-align: middle;
        padding: 4px 6px;
    }
    .label-code {
        font-weight: bold;
        font-size: 11px;
        color: #1e293b;
        word-break: break-all;
    }
    .label-name {
        font-size: 10px;
        color: #334155;
        margin-top: 2px;
    }
    .label-detail {
        font-size: 8px;
        color: #64748b;
        margin-top: 1px;
    }

    /* Small format: 3 per row */
    .format-small .label-cell { width: 33.33%; }
    .format-small .label { height: 60px; }
    .format-small .label-qr img { width: 40px; height: 40px; }

    /* Medium format: 2 per row */
    .format-medium .label-cell { width: 50%; }
    .format-medium .label { height: 100px; }
    .format-medium .label-qr img { width: 70px; height: 70px; }

    /* Large format: 1 per row */
    .format-large .label-cell { width: 100%; }
    .format-large .label { height: 160px; }
    .format-large .label-qr img { width: 110px; height: 110px; }
    .format-large .label-code { font-size: 14px; }
    .format-large .label-name { font-size: 12px; }
    .format-large .label-detail { font-size: 10px; }
</style>
@endsection

@section('content')
@php
    $perRow = match($format) {
        'small' => 3,
        'medium' => 2,
        'large' => 1,
        default => 2,
    };
    $chunks = $items->chunk($perRow);
@endphp

<div class="labels-grid format-{{ $format }}">
    @foreach ($chunks as $row)
        <div class="labels-row">
            @foreach ($row as $item)
                <div class="label-cell">
                    <div class="label">
                        <div class="label-inner">
                            <div class="label-qr">
                                <img src="{{ $item['qr'] }}" alt="QR">
                            </div>
                            <div class="label-info">
                                <div class="label-code">{{ $item['code'] }}</div>
                                @if ($format !== 'small')
                                    <div class="label-name">{{ $item['name'] }}</div>
                                    @if ($item['zone'])
                                        <div class="label-detail">{{ $item['zone'] }}</div>
                                    @endif
                                @endif
                                @if ($format === 'large')
                                    @if ($item['type'])
                                        <div class="label-detail">Type: {{ $item['type'] }}</div>
                                    @endif
                                    @if ($item['site'])
                                        <div class="label-detail">Site: {{ $item['site'] }}</div>
                                    @endif
                                    @if ($item['salle'])
                                        <div class="label-detail">Salle: {{ $item['salle'] }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            {{-- Fill empty cells if row is not complete --}}
            @for ($i = $row->count(); $i < $perRow; $i++)
                <div class="label-cell"></div>
            @endfor
        </div>
    @endforeach
</div>
@endsection
