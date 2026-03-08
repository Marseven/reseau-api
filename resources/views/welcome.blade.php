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
            max-width: 1020px;
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
            max-height: 64px;
            width: auto;
            object-fit: contain;
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
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }

        .endpoints-table tr:last-child td { border-bottom: none; }
        .endpoints-table tr:hover td { background: rgba(255,255,255,0.02); }

        .method {
            display: inline-block;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            letter-spacing: 0.05em;
            font-family: 'Outfit', monospace;
            min-width: 42px;
            text-align: center;
        }

        .method-get { background: rgba(34,197,94,0.15); color: #4ade80; }
        .method-post { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .method-put { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .method-delete { background: rgba(239,68,68,0.15); color: #f87171; }

        .route-path { font-family: 'Outfit', monospace; color: #cbd5e1; font-size: 0.8rem; }
        .route-desc { color: #64748b; font-size: 0.8rem; }
        .route-roles { font-size: 0.7rem; color: #475569; }

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

        .btn-swagger svg { width: 20px; height: 20px; }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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

        .info-value { font-size: 1.5rem; font-weight: 700; color: #a5b4fc; }
        .info-label { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,0.05);
            margin-top: auto;
        }

        .footer-text { font-size: 0.8rem; color: #475569; font-weight: 300; }
        .footer-brand { color: #64748b; font-weight: 500; }

        @media (max-width: 640px) {
            .title { font-size: 1.5rem; }
            .info-grid { grid-template-columns: repeat(2, 1fr); }
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
            <img src="/logo.png" alt="Eramet Comilog" class="logo">
            <h1 class="title">ReseauApp API</h1>
            <p class="subtitle">Infrastructure Réseau & Inventaire — Eramet Comilog</p>
            <span class="badge">v1.0.0</span>
        </header>

        <!-- Quick stats -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-value">14</div>
                <div class="info-label">Ressources CRUD</div>
            </div>
            <div class="info-item">
                <div class="info-value">7</div>
                <div class="info-label">Analytics</div>
            </div>
            <div class="info-item">
                <div class="info-value">2FA</div>
                <div class="info-label">Authentification</div>
            </div>
            <div class="info-item">
                <div class="info-value">5</div>
                <div class="info-label">Exports CSV/PDF</div>
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

        <!-- Public -->
        <div class="card">
            <h2 class="card-title">Public (sans authentification)</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th></tr></thead>
                <tbody>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/health</td>
                        <td class="route-desc">Health check</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/stats/public</td>
                        <td class="route-desc">Compteurs agrégés</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/login</td>
                        <td class="route-desc">Connexion utilisateur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/auth/2fa/challenge</td>
                        <td class="route-desc">Vérification 2FA (rate-limited)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Auth -->
        <div class="card">
            <h2 class="card-title">Authentification & Profil</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
                <tbody>
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
                    <tr>
                        <td><span class="method method-put">PUT</span></td>
                        <td class="route-path">/api/v1/auth/profile</td>
                        <td class="route-desc">Modifier profil</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-put">PUT</span></td>
                        <td class="route-path">/api/v1/auth/password</td>
                        <td class="route-desc">Changer mot de passe</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 2FA -->
        <div class="card">
            <h2 class="card-title">Gestion 2FA</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
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

        <!-- CRUD Resources -->
        <div class="card">
            <h2 class="card-title">Ressources CRUD</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
                <tbody>
                    @php
                        $resources = [
                            ['name' => 'sites', 'label' => 'Sites'],
                            ['name' => 'zones', 'label' => 'Zones'],
                            ['name' => 'batiments', 'label' => 'Bâtiments'],
                            ['name' => 'salles', 'label' => 'Salles'],
                            ['name' => 'coffrets', 'label' => 'Coffrets / Baies'],
                            ['name' => 'equipements', 'label' => 'Équipements réseau'],
                            ['name' => 'ports', 'label' => 'Ports réseau'],
                            ['name' => 'liaisons', 'label' => 'Liaisons'],
                            ['name' => 'metrics', 'label' => 'Métriques'],
                            ['name' => 'systems', 'label' => 'Systèmes'],
                            ['name' => 'vlans', 'label' => 'VLANs'],
                            ['name' => 'maintenances', 'label' => 'Maintenances'],
                            ['name' => 'change-requests', 'label' => 'Demandes de changement'],
                            ['name' => 'users', 'label' => 'Utilisateurs'],
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
                        <td class="route-roles">R: all auth · W: admin, directeur</td>
                    </tr>
                    <tr>
                        <td>
                            <span class="method method-get">GET</span>
                            <span class="method method-put">PUT</span>
                            <span class="method method-delete">DEL</span>
                        </td>
                        <td class="route-path">/api/v1/{{ $res['name'] }}/{id}</td>
                        <td class="route-desc">{{ $res['label'] }} (détail)</td>
                        <td class="route-roles">R: all auth · W: admin, directeur</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notifications -->
        <div class="card">
            <h2 class="card-title">Notifications</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
                <tbody>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/notifications</td>
                        <td class="route-desc">Liste des notifications</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-put">PUT</span></td>
                        <td class="route-path">/api/v1/notifications/read-all</td>
                        <td class="route-desc">Tout marquer comme lu</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-put">PUT</span></td>
                        <td class="route-path">/api/v1/notifications/{id}/read</td>
                        <td class="route-desc">Marquer une notification comme lue</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-delete">DEL</span></td>
                        <td class="route-path">/api/v1/notifications/{id}</td>
                        <td class="route-desc">Supprimer une notification</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Statistiques & Analytics -->
        <div class="card">
            <h2 class="card-title">Statistiques & Analytics</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
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
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/equipements-by-type</td>
                        <td class="route-desc">Équipements par type</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/equipements-by-classification</td>
                        <td class="route-desc">Équipements par classification</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/equipements-by-status</td>
                        <td class="route-desc">Équipements par statut</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/equipements-by-vendor</td>
                        <td class="route-desc">Équipements par fournisseur</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/maintenance-trends</td>
                        <td class="route-desc">Tendances maintenances</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/port-utilization</td>
                        <td class="route-desc">Utilisation des ports</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/analytics/sites-summary</td>
                        <td class="route-desc">Résumé par site</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Exports, Imports, Reports -->
        <div class="card">
            <h2 class="card-title">Exports, Imports & Rapports</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
                <tbody>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/exports/{resource}/csv</td>
                        <td class="route-desc">Export CSV (equipements, coffrets, ports, liaisons, activity-logs)</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/exports/architecture/pdf</td>
                        <td class="route-desc">Export architecture PDF</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/imports/{resource}/csv</td>
                        <td class="route-desc">Import CSV (coffrets, equipements, ports, liaisons)</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/imports/{resource}/template</td>
                        <td class="route-desc">Template CSV d'import</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/reports/summary</td>
                        <td class="route-desc">Résumé des rapports</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/reports/{type}/pdf</td>
                        <td class="route-desc">Rapports PDF (network-status, modifications, interventions)</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-post">POST</span></td>
                        <td class="route-path">/api/v1/labels/{resource}</td>
                        <td class="route-desc">Étiquettes PDF (coffrets, equipements)</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Misc -->
        <div class="card">
            <h2 class="card-title">Divers</h2>
            <table class="endpoints-table">
                <thead><tr><th>Méthode</th><th>Route</th><th>Description</th><th>Accès</th></tr></thead>
                <tbody>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/qr/coffret/{token}</td>
                        <td class="route-desc">Résolution QR code coffret</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/qr/equipement/{token}</td>
                        <td class="route-desc">Résolution QR code équipement</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/topology</td>
                        <td class="route-desc">Topologie réseau</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/activity-logs</td>
                        <td class="route-desc">Logs d'activité</td>
                        <td class="route-roles">admin, directeur</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/coffrets/{id}/history</td>
                        <td class="route-desc">Historique d'un coffret</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/login-audits</td>
                        <td class="route-desc">Audit des connexions</td>
                        <td class="route-roles">admin</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/login-audits/me</td>
                        <td class="route-desc">Mon historique de connexion</td>
                        <td class="route-roles">Auth</td>
                    </tr>
                    <tr>
                        <td><span class="method method-get">GET</span></td>
                        <td class="route-path">/api/v1/settings</td>
                        <td class="route-desc">Paramètres système</td>
                        <td class="route-roles">admin</td>
                    </tr>
                    <tr>
                        <td><span class="method method-put">PUT</span></td>
                        <td class="route-path">/api/v1/settings</td>
                        <td class="route-desc">Modifier paramètres</td>
                        <td class="route-roles">admin</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p class="footer-text">
                <span class="footer-brand">Eramet Comilog</span> &middot;
                ReseauApp API v1.0.0 &middot;
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} &middot;
                PHP v{{ PHP_VERSION }}
            </p>
        </footer>
    </div>
</body>
</html>
