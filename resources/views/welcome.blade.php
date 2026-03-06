<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ReseauApp API - Eramet Comilog</title>
    <link rel="icon" href="/logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0a0c14;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Background gradient effect */
        .bg-glow {
            position: fixed;
            top: -30%;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(48,53,93,0.3) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 960px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .logo {
            width: 72px;
            height: 72px;
            filter: brightness(0) invert(1);
            margin-bottom: 1.5rem;
        }

        .title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
        }

        .subtitle {
            font-size: 1rem;
            font-weight: 300;
            color: #94a3b8;
            margin-bottom: 1.25rem;
        }

        .badge {
            display: inline-block;
            background: #30355d;
            color: #a5b4fc;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Card */
        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(8px);
        }

        .card-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.25rem;
        }

        /* Endpoints table */
        .endpoints-table {
            width: 100%;
            border-collapse: collapse;
        }

        .endpoints-table th {
            text-align: left;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .endpoints-table td {
            padding: 0.6rem 0.75rem;
            font-size: 0.875rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }

        .endpoints-table tr:last-child td {
            border-bottom: none;
        }

        .endpoints-table tr:hover td {
            background: rgba(255,255,255,0.02);
        }

        .method {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            letter-spacing: 0.05em;
            font-family: 'Outfit', monospace;
            min-width: 52px;
            text-align: center;
        }

        .method-get { background: rgba(34,197,94,0.15); color: #4ade80; }
        .method-post { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .method-put { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .method-delete { background: rgba(239,68,68,0.15); color: #f87171; }

        .route-path {
            font-family: 'Outfit', monospace;
            color: #cbd5e1;
            font-size: 0.8rem;
        }

        .route-desc {
            color: #64748b;
            font-size: 0.8rem;
        }

        .route-roles {
            font-size: 0.7rem;
            color: #475569;
        }

        /* Swagger button */
        .actions {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .btn-swagger {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: #30355d;
            color: #e2e8f0;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.85rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid rgba(165,180,252,0.15);
        }

        .btn-swagger:hover {
            background: #3b4180;
            border-color: rgba(165,180,252,0.3);
            transform: translateY(-1px);
            box-shadow: 0 8px 30px rgba(48,53,93,0.4);
        }

        .btn-swagger svg {
            width: 20px;
            height: 20px;
        }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
        }

        .info-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #a5b4fc;
        }

        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,0.05);
            margin-top: auto;
        }

        .footer-text {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 300;
        }

        .footer-brand {
            color: #64748b;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .title { font-size: 1.5rem; }
            .info-grid { grid-template-columns: 1fr; }
            .card { padding: 1.25rem; }
            .endpoints-table { font-size: 0.8rem; }
            .container { padding: 2rem 1rem; }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <img src="/logo.png" alt="ReseauApp" class="logo">
            <h1 class="title">ReseauApp API</h1>
            <p class="subtitle">Infrastructure Réseau & Inventaire - Eramet Comilog</p>
            <span class="badge">v1.0.0</span>
        </header>

        <!-- Quick stats -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-value">6</div>
                <div class="info-label">Ressources CRUD</div>
            </div>
            <div class="info-item">
                <div class="info-value">4</div>
                <div class="info-label">Endpoints Stats</div>
            </div>
            <div class="info-item">
                <div class="info-value">2FA</div>
                <div class="info-label">Authentification</div>
            </div>
        </div>

        <!-- Swagger button -->
        <div class="actions">
            <a href="/api/documentation" class="btn-swagger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Documentation Swagger
            </a>
        </div>

        <!-- Endpoints table -->
        <div class="card">
            <h2 class="card-title">Authentification</h2>
            <table class="endpoints-table">
                <thead>
                    <tr>
                        <th>Méthode</th>
                        <th>Route</th>
                        <th>Description</th>
                        <th>Accès</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/login</td>
                        <td class="route-desc">Connexion utilisateur</td>
                        <td class="route-roles">Public</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/2fa/challenge</td>
                        <td class="route-desc">Vérification 2FA</td>
                        <td class="route-roles">Public</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/logout</td>
                        <td class="route-desc">Déconnexion</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/auth/me</td>
                        <td class="route-desc">Utilisateur courant</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 class="card-title">2FA Management</h2>
            <table class="endpoints-table">
                <thead>
                    <tr>
                        <th>Méthode</th>
                        <th>Route</th>
                        <th>Description</th>
                        <th>Accès</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/2fa/setup</td>
                        <td class="route-desc">Initialiser 2FA</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/2fa/verify</td>
                        <td class="route-desc">Activer 2FA</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/2fa/disable</td>
                        <td class="route-desc">Désactiver 2FA</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/auth/2fa/recovery-codes</td>
                        <td class="route-desc">Codes de récupération</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/2fa/recovery-codes/regenerate</td>
                        <td class="route-desc">Régénérer codes</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 class="card-title">Statistiques</h2>
            <table class="endpoints-table">
                <thead>
                    <tr>
                        <th>Méthode</th>
                        <th>Route</th>
                        <th>Description</th>
                        <th>Accès</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/stats/global</td>
                        <td class="route-desc">Statistiques globales</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/stats/systems-by-type</td>
                        <td class="route-desc">Systèmes par type</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/stats/equipements-by-coffret</td>
                        <td class="route-desc">Équipements par coffret</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/stats/ports-by-vlan</td>
                        <td class="route-desc">Ports par VLAN</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 class="card-title">Ressources CRUD</h2>
            <table class="endpoints-table">
                <thead>
                    <tr>
                        <th>Méthode</th>
                        <th>Route</th>
                        <th>Description</th>
                        <th>Accès</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $resources = [
                            ['name' => 'coffrets', 'label' => 'Coffrets / Armoires'],
                            ['name' => 'equipements', 'label' => 'Équipements réseau'],
                            ['name' => 'ports', 'label' => 'Ports réseau'],
                            ['name' => 'metrics', 'label' => 'Métriques'],
                            ['name' => 'liaisons', 'label' => 'Liaisons'],
                            ['name' => 'systems', 'label' => 'Systèmes'],
                        ];
                    @endphp
                    @foreach($resources as $res)
                    <tr>
                        <td>
                            <span class="method method-get">GET</span>
                            <span class="method method-post">POST</span>
                        </td>
                        <td class="route-path">/api/v1/{{ $res['name'] }}</td>
                        <td class="route-desc">{{ $res['label'] }}</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td>
                            <span class="method method-get">GET</span>
                            <span class="method method-put">PUT</span>
                            <span class="method method-delete">DEL</span>
                        </td>
                        <td class="route-path">/api/v1/{{ $res['name'] }}/{id}</td>
                        <td class="route-desc">{{ $res['label'] }} (détail)</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p class="footer-text">
                <span class="footer-brand">Eramet Comilog</span> &middot;
                ReseauApp API v1.0.0 &middot;
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} &middot;
                PHP v{{ PHP_VERSION }} &middot;
                JOBS-Conseil
            </p>
        </footer>
    </div>
</body>
</html>
