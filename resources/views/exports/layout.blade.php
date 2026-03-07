<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') - ReseauApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 8px; margin-bottom: 16px; }
        .header-title { font-size: 18px; font-weight: bold; color: #2563eb; }
        .header-subtitle { font-size: 11px; color: #64748b; margin-top: 2px; }
        .header-meta { font-size: 9px; color: #94a3b8; margin-top: 4px; }
        h2 { font-size: 14px; color: #2563eb; margin: 16px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        h3 { font-size: 12px; color: #334155; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
        th { background: #2563eb; color: white; text-align: left; padding: 5px 6px; font-weight: 600; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 9px; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .badge-maintenance { background: #fef3c7; color: #92400e; }
        .badge-planifiee { background: #dbeafe; color: #1e40af; }
        .badge-en_cours { background: #fef3c7; color: #92400e; }
        .badge-terminee { background: #dcfce7; color: #166534; }
        .summary-table td { font-weight: 600; font-size: 12px; text-align: center; }
        .summary-table th { text-align: center; }
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #94a3b8; }
        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }

        @page { margin: 20mm 15mm 25mm 15mm; }

        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .footer .page-number:after { content: "Page " counter(page) " / " counter(pages); }
    </style>
    @yield('styles')
</head>
<body>
    <div class="footer">
        <span class="page-number"></span>
    </div>

    <div class="header">
        <div class="header-title">ReseauApp - Eramet Comilog</div>
        <div class="header-subtitle">@yield('title')</div>
        <div class="header-meta">
            {{ $generatedAt ?? now()->format('d/m/Y H:i') }}
            @if(isset($generatedBy))
                &mdash; {{ $generatedBy }}
            @endif
        </div>
    </div>

    @yield('content')
</body>
</html>
