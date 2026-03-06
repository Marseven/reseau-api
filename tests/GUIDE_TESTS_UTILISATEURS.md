# Guide de Tests Utilisateurs - ReseauApp API

## Vue d'ensemble

Ce guide documente les scenarios de tests manuels pour chaque profil utilisateur de l'API ReseauApp.
L'API dispose de 4 roles avec des niveaux d'acces differents.

**Base URL** : `/api/v1/`
**Authentification** : Bearer token (Laravel Sanctum)
**Mot de passe par defaut** : `password`

---

## Matrice d'acces globale

| Endpoint | Methode | administrator | directeur | technicien | user |
|----------|---------|:---:|:---:|:---:|:---:|
| /auth/login | POST | 200 | 200 | 200 | 200 |
| /auth/me | GET | 200 | 200 | 200 | 200 |
| /auth/logout | POST | 200 | 200 | 200 | 200 |
| /auth/2fa/setup | POST | 200 | 200 | 200 | 200 |
| /auth/2fa/verify | POST | 200 | 200 | 200 | 200 |
| /auth/2fa/disable | POST | 200 | 200 | 200 | 200 |
| /auth/2fa/challenge | POST | 200 | 200 | 200 | 200 |
| /stats/global | GET | 200 | 200 | **403** | **403** |
| /stats/systems-by-type | GET | 200 | 200 | **403** | **403** |
| /stats/equipements-by-coffret | GET | 200 | 200 | **403** | **403** |
| /stats/ports-by-vlan | GET | 200 | 200 | **403** | **403** |
| /coffrets | GET | 200 | 200 | **403** | **403** |
| /coffrets | POST | 201 | **403** | **403** | **403** |
| /coffrets/{id} | GET | 200 | 200 | **403** | **403** |
| /coffrets/{id} | PUT | 200 | **403** | **403** | **403** |
| /coffrets/{id} | DELETE | 200 | 200 | **403** | **403** |
| /equipements | GET | 200 | 200 | **403** | **403** |
| /equipements | POST | 201 | **403** | **403** | **403** |
| /equipements/{id} | GET | 200 | 200 | **403** | **403** |
| /equipements/{id} | PUT | 200 | **403** | **403** | **403** |
| /equipements/{id} | DELETE | 200 | 200 | **403** | **403** |
| /ports | GET | 200 | 200 | **403** | **403** |
| /ports | POST | 201 | **403** | **403** | **403** |
| /ports/{id} | GET | 200 | 200 | **403** | **403** |
| /ports/{id} | PUT | 200 | **403** | **403** | **403** |
| /ports/{id} | DELETE | 200 | 200 | **403** | **403** |
| /metrics | GET | 200 | 200 | **403** | **403** |
| /metrics | POST | 201 | 201 | **403** | **403** |
| /metrics/{id} | GET | 200 | 200 | **403** | **403** |
| /metrics/{id} | PUT | 200 | 200 | **403** | **403** |
| /metrics/{id} | DELETE | 200 | 200 | **403** | **403** |
| /liaisons | GET | 200 | 200 | **403** | **403** |
| /liaisons | POST | 201 | **403** | **403** | **403** |
| /liaisons/{id} | GET | 200 | 200 | **403** | **403** |
| /liaisons/{id} | PUT | 200 | **403** | **403** | **403** |
| /liaisons/{id} | DELETE | 200 | 200 | **403** | **403** |
| /systems | GET | 200 | 200 | **403** | **403** |
| /systems | POST | 201 | **403** | **403** | **403** |
| /systems/{id} | GET | 200 | 200 | **403** | **403** |
| /systems/{id} | PUT | 200 | **403** | **403** | **403** |
| /systems/{id} | DELETE | 200 | 200 | **403** | **403** |

> **Note** : Le directeur peut lire (index/show) et supprimer (destroy) toutes les ressources.
> En revanche, les actions store et update sont bloquees par `isAdministrator()` dans les Form Requests,
> **sauf pour les Metrics** ou `authorize()` retourne `true`.

---

## 1. Profil Administrator

**Role** : Acces complet a toutes les ressources et actions.

### 1.1 Authentification

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A1 | Login avec username | POST | /auth/login | `{"username":"admin","password":"password"}` | 200 + token |
| A2 | Login avec email | POST | /auth/login | `{"username":"admin@test.com","password":"password"}` | 200 + token |
| A3 | Login mot de passe incorrect | POST | /auth/login | `{"username":"admin","password":"wrong"}` | 401 |
| A4 | Login utilisateur inactif | POST | /auth/login | `{"username":"inactive_admin","password":"password"}` | 401 |
| A5 | Recuperer profil | GET | /auth/me | - | 200 + donnees utilisateur |
| A6 | Deconnexion | POST | /auth/logout | - | 200 + token supprime |

### 1.2 2FA

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A7 | Setup 2FA | POST | /auth/2fa/setup | - | 200 + provisioning URI |
| A8 | Verifier 2FA (activer) | POST | /auth/2fa/verify | `{"code":"123456"}` | 200 + recovery codes |
| A9 | Login avec 2FA active | POST | /auth/login | credentials | 200 + `requires_2fa: true` + temp token |
| A10 | Challenge 2FA | POST | /auth/2fa/challenge | `{"two_factor_token":"...","code":"123456"}` | 200 + token definitif |
| A11 | Recovery codes restants | GET | /auth/2fa/recovery-codes | - | 200 + count |
| A12 | Regenerer recovery codes | POST | /auth/2fa/recovery-codes/regenerate | `{"code":"123456"}` | 200 + nouveaux codes |
| A13 | Desactiver 2FA | POST | /auth/2fa/disable | `{"code":"123456"}` | 200 |

### 1.3 Statistiques

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| A14 | Stats globales | GET | /stats/global | 200 + compteurs par entite |
| A15 | Systemes par type | GET | /stats/systems-by-type | 200 + groupement |
| A16 | Equipements par coffret | GET | /stats/equipements-by-coffret | 200 + groupement |
| A17 | Ports par VLAN | GET | /stats/ports-by-vlan | 200 + groupement |

### 1.4 CRUD Coffrets

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A18 | Lister coffrets | GET | /coffrets | - | 200 + liste paginee |
| A19 | Filtrer par status | GET | /coffrets?status=active | - | 200 + coffrets actifs uniquement |
| A20 | Rechercher par nom | GET | /coffrets?search=Alpha | - | 200 + resultats filtres |
| A21 | Pagination custom | GET | /coffrets?per_page=5 | - | 200 + 5 items max |
| A22 | Creer coffret | POST | /coffrets | `{"code":"COF-001","name":"Test","piece":"Salle A","long":2.3,"lat":48.8}` | 201 + coffret cree |
| A23 | Voir coffret | GET | /coffrets/{id} | - | 200 + coffret avec relations |
| A24 | Modifier coffret | PUT | /coffrets/{id} | `{"name":"Nouveau nom"}` | 200 + coffret modifie |
| A25 | Supprimer coffret | DELETE | /coffrets/{id} | - | 200 + soft delete (deleted_at non null) |

### 1.5 CRUD Equipements

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A26 | Lister equipements | GET | /equipements | - | 200 + liste paginee |
| A27 | Creer equipement | POST | /equipements | `{"equipement_code":"EQ-001","name":"Switch","type":"switch","coffret_id":1,"status":"active"}` | 201 |
| A28 | Voir equipement | GET | /equipements/{id} | - | 200 + equipement avec coffret et ports |
| A29 | Modifier equipement | PUT | /equipements/{id} | `{"name":"Nouveau nom"}` | 200 |
| A30 | Supprimer equipement | DELETE | /equipements/{id} | - | 200 + soft delete |

### 1.6 CRUD Ports

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A31 | Lister ports | GET | /ports | - | 200 + liste paginee |
| A32 | Filtrer par VLAN | GET | /ports?vlan=100 | - | 200 + ports du VLAN |
| A33 | Creer port | POST | /ports | `{"port_label":"P1","device_name":"SW01","poe_enabled":true}` | 201 |
| A34 | Voir port | GET | /ports/{id} | - | 200 |
| A35 | Modifier port | PUT | /ports/{id} | `{"device_name":"SW02"}` | 200 |
| A36 | Supprimer port | DELETE | /ports/{id} | - | 200 + soft delete |

### 1.7 CRUD Metrics

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A37 | Lister metrics | GET | /metrics | - | 200 + liste paginee |
| A38 | Creer metric | POST | /metrics | `{"name":"CPU","type":"gauge","coffret_id":1,"status":true}` | 201 |
| A39 | Voir metric | GET | /metrics/{id} | - | 200 |
| A40 | Modifier metric | PUT | /metrics/{id} | `{"name":"Memory"}` | 200 |
| A41 | Supprimer metric | DELETE | /metrics/{id} | - | 200 + soft delete |

### 1.8 CRUD Liaisons

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A42 | Lister liaisons | GET | /liaisons | - | 200 + liste paginee |
| A43 | Creer liaison | POST | /liaisons | `{"from":1,"to":2,"label":"Fibre A","media":"fibre","status":true}` | 201 |
| A44 | Voir liaison | GET | /liaisons/{id} | - | 200 |
| A45 | Modifier liaison | PUT | /liaisons/{id} | `{"label":"Fibre B"}` | 200 |
| A46 | Supprimer liaison | DELETE | /liaisons/{id} | - | 200 + soft delete |

### 1.9 CRUD Systems

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| A47 | Lister systemes | GET | /systems | - | 200 + liste paginee |
| A48 | Creer systeme | POST | /systems | `{"name":"NMS","type":"monitoring","status":true}` | 201 |
| A49 | Voir systeme | GET | /systems/{id} | - | 200 |
| A50 | Modifier systeme | PUT | /systems/{id} | `{"name":"NMS v2"}` | 200 |
| A51 | Supprimer systeme | DELETE | /systems/{id} | - | 200 + soft delete |

---

## 2. Profil Directeur

**Role** : Acces en lecture a toutes les ressources + statistiques. Ecriture uniquement pour les Metrics.

### 2.1 Authentification

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| D1 | Login | POST | /auth/login | 200 + token |
| D2 | Profil | GET | /auth/me | 200 + donnees utilisateur |
| D3 | Deconnexion | POST | /auth/logout | 200 |

### 2.2 Statistiques (acces autorise)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| D4 | Stats globales | GET | /stats/global | 200 |
| D5 | Systemes par type | GET | /stats/systems-by-type | 200 |
| D6 | Equipements par coffret | GET | /stats/equipements-by-coffret | 200 |
| D7 | Ports par VLAN | GET | /stats/ports-by-vlan | 200 |

### 2.3 Lecture CRUD (autorisee)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| D8 | Lister coffrets | GET | /coffrets | 200 |
| D9 | Voir coffret | GET | /coffrets/{id} | 200 |
| D10 | Lister equipements | GET | /equipements | 200 |
| D11 | Voir equipement | GET | /equipements/{id} | 200 |
| D12 | Lister ports | GET | /ports | 200 |
| D13 | Voir port | GET | /ports/{id} | 200 |
| D14 | Lister metrics | GET | /metrics | 200 |
| D15 | Voir metric | GET | /metrics/{id} | 200 |
| D16 | Lister liaisons | GET | /liaisons | 200 |
| D17 | Voir liaison | GET | /liaisons/{id} | 200 |
| D18 | Lister systemes | GET | /systems | 200 |
| D19 | Voir systeme | GET | /systems/{id} | 200 |

### 2.4 Ecriture CRUD bloquee (isAdministrator = false)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| D20 | Creer coffret | POST | /coffrets | **403** |
| D21 | Modifier coffret | PUT | /coffrets/{id} | **403** |
| D22 | Creer equipement | POST | /equipements | **403** |
| D23 | Modifier equipement | PUT | /equipements/{id} | **403** |
| D24 | Creer port | POST | /ports | **403** |
| D25 | Modifier port | PUT | /ports/{id} | **403** |
| D26 | Creer liaison | POST | /liaisons | **403** |
| D27 | Modifier liaison | PUT | /liaisons/{id} | **403** |
| D28 | Creer systeme | POST | /systems | **403** |
| D29 | Modifier systeme | PUT | /systems/{id} | **403** |

### 2.5 Suppression CRUD (autorisee - pas de FormRequest)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| D30 | Supprimer coffret | DELETE | /coffrets/{id} | 200 |
| D31 | Supprimer equipement | DELETE | /equipements/{id} | 200 |
| D32 | Supprimer port | DELETE | /ports/{id} | 200 |
| D33 | Supprimer metric | DELETE | /metrics/{id} | 200 |
| D34 | Supprimer liaison | DELETE | /liaisons/{id} | 200 |
| D35 | Supprimer systeme | DELETE | /systems/{id} | 200 |

### 2.6 Exception Metrics (authorize = true)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| D36 | Creer metric | POST | /metrics | **201** |
| D37 | Modifier metric | PUT | /metrics/{id} | **200** |

---

## 3. Profil Technicien

**Role** : Acces authentifie uniquement. Bloque par le middleware `role:administrator,directeur` sur toutes les ressources.

### 3.1 Authentification (autorisee)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| T1 | Login | POST | /auth/login | 200 + token |
| T2 | Profil | GET | /auth/me | 200 |
| T3 | Deconnexion | POST | /auth/logout | 200 |

### 3.2 Statistiques (bloquees)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| T4 | Stats globales | GET | /stats/global | **403** |
| T5 | Systemes par type | GET | /stats/systems-by-type | **403** |
| T6 | Equipements par coffret | GET | /stats/equipements-by-coffret | **403** |
| T7 | Ports par VLAN | GET | /stats/ports-by-vlan | **403** |

### 3.3 CRUD (tout bloque)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| T8 | Lister coffrets | GET | /coffrets | **403** |
| T9 | Creer coffret | POST | /coffrets | **403** |
| T10 | Voir coffret | GET | /coffrets/{id} | **403** |
| T11 | Modifier coffret | PUT | /coffrets/{id} | **403** |
| T12 | Supprimer coffret | DELETE | /coffrets/{id} | **403** |
| T13 | Lister equipements | GET | /equipements | **403** |
| T14 | Creer equipement | POST | /equipements | **403** |
| T15 | Voir equipement | GET | /equipements/{id} | **403** |
| T16 | Modifier equipement | PUT | /equipements/{id} | **403** |
| T17 | Supprimer equipement | DELETE | /equipements/{id} | **403** |
| T18 | Lister ports | GET | /ports | **403** |
| T19 | Creer port | POST | /ports | **403** |
| T20 | Voir port | GET | /ports/{id} | **403** |
| T21 | Modifier port | PUT | /ports/{id} | **403** |
| T22 | Supprimer port | DELETE | /ports/{id} | **403** |
| T23 | Lister metrics | GET | /metrics | **403** |
| T24 | Creer metric | POST | /metrics | **403** |
| T25 | Voir metric | GET | /metrics/{id} | **403** |
| T26 | Modifier metric | PUT | /metrics/{id} | **403** |
| T27 | Supprimer metric | DELETE | /metrics/{id} | **403** |
| T28 | Lister liaisons | GET | /liaisons | **403** |
| T29 | Creer liaison | POST | /liaisons | **403** |
| T30 | Voir liaison | GET | /liaisons/{id} | **403** |
| T31 | Modifier liaison | PUT | /liaisons/{id} | **403** |
| T32 | Supprimer liaison | DELETE | /liaisons/{id} | **403** |
| T33 | Lister systemes | GET | /systems | **403** |
| T34 | Creer systeme | POST | /systems | **403** |
| T35 | Voir systeme | GET | /systems/{id} | **403** |
| T36 | Modifier systeme | PUT | /systems/{id} | **403** |
| T37 | Supprimer systeme | DELETE | /systems/{id} | **403** |

---

## 4. Profil User

**Role** : Acces authentifie uniquement. Meme restrictions que technicien.

### 4.1 Authentification (autorisee)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| U1 | Login | POST | /auth/login | 200 + token |
| U2 | Profil | GET | /auth/me | 200 |
| U3 | Deconnexion | POST | /auth/logout | 200 |

### 4.2 Statistiques (bloquees)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| U4 | Stats globales | GET | /stats/global | **403** |
| U5 | Systemes par type | GET | /stats/systems-by-type | **403** |
| U6 | Equipements par coffret | GET | /stats/equipements-by-coffret | **403** |
| U7 | Ports par VLAN | GET | /stats/ports-by-vlan | **403** |

### 4.3 CRUD (tout bloque)

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| U8 | Lister coffrets | GET | /coffrets | **403** |
| U9 | Creer coffret | POST | /coffrets | **403** |
| U10 | Voir coffret | GET | /coffrets/{id} | **403** |
| U11 | Modifier coffret | PUT | /coffrets/{id} | **403** |
| U12 | Supprimer coffret | DELETE | /coffrets/{id} | **403** |
| U13-U37 | Tous les CRUD restants | * | /equipements, /ports, /metrics, /liaisons, /systems | **403** |

---

## 5. Scenarios transverses

### 5.1 Acces non authentifie

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| X1 | Acces /me sans token | GET | /auth/me | **401** |
| X2 | Acces /logout sans token | POST | /auth/logout | **401** |
| X3 | Acces /coffrets sans token | GET | /coffrets | **401** |
| X4 | Acces /stats sans token | GET | /stats/global | **401** |
| X5 | Acces CRUD sans token | POST | /coffrets | **401** |

### 5.2 Validation des donnees

| # | Scenario | Methode | Endpoint | Corps | Resultat attendu |
|---|----------|---------|----------|-------|------------------|
| X6 | Login sans champs | POST | /auth/login | `{}` | 422 |
| X7 | Coffret sans champs requis | POST | /coffrets | `{}` | 422 |
| X8 | Equipement avec IP invalide | POST | /equipements | `{"ip_address":"invalid"}` | 422 |
| X9 | Equipement code non unique | POST | /equipements | code duplique | 422 |
| X10 | Coffret_id inexistant | POST | /equipements | `{"coffret_id":9999}` | 422 |
| X11 | Status invalide | POST | /coffrets | `{"status":"invalid"}` | 422 |

### 5.3 Pagination et filtres

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| X12 | Pagination par defaut (15) | GET | /coffrets | 15 items max par page |
| X13 | Pagination custom (5) | GET | /coffrets?per_page=5 | 5 items max par page |
| X14 | Filtre status | GET | /coffrets?status=active | Coffrets actifs uniquement |
| X15 | Recherche par nom | GET | /coffrets?search=Alpha | Resultats correspondants |
| X16 | Filtre VLAN ports | GET | /ports?vlan=100 | Ports du VLAN 100 |

### 5.4 Soft Delete

| # | Scenario | Methode | Endpoint | Resultat attendu |
|---|----------|---------|----------|------------------|
| X17 | Suppression coffret | DELETE | /coffrets/{id} | `deleted_at` non null en DB |
| X18 | Suppression equipement | DELETE | /equipements/{id} | `deleted_at` non null en DB |
| X19 | Suppression port | DELETE | /ports/{id} | `deleted_at` non null en DB |
| X20 | Suppression metric | DELETE | /metrics/{id} | `deleted_at` non null en DB |
| X21 | Suppression liaison | DELETE | /liaisons/{id} | `deleted_at` non null en DB |
| X22 | Suppression systeme | DELETE | /systems/{id} | `deleted_at` non null en DB |

---

## Notes importantes

### Mecanisme d'autorisation
Le systeme utilise 2 couches d'autorisation :
1. **Middleware `role:administrator,directeur`** : bloque technicien et user sur toutes les routes CRUD et stats
2. **Form Request `authorize()`** : `isAdministrator()` bloque le directeur sur les actions store/update (sauf Metrics)

### Exception Metrics
`StoreMetricRequest` et `UpdateMetricRequest` retournent `authorize() = true`, ce qui permet au directeur de creer et modifier des metrics (le middleware laisse passer, et le FormRequest aussi).

### Actions Destroy
Les actions `destroy()` n'utilisent pas de Form Request, donc le directeur peut supprimer toutes les ressources (seul le middleware `role:administrator,directeur` s'applique).

### 2FA
Le setup/verify/disable 2FA est accessible a tous les utilisateurs authentifies (pas de restriction de role). Le challenge 2FA est public mais rate-limited (5 tentatives par minute).
