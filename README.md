# Consulting OS – Guide de démarrage

Outil interne de gestion de missions de conseil.
Stack : **Laravel 11** · **MySQL 8** · **Nginx** · **Tailwind CSS** · **API Claude (IA)**

---

## Prérequis (à installer une seule fois)

### 1. Docker Desktop pour Windows
Docker va gérer PHP, MySQL et le serveur web — tu n'installes rien d'autre.

👉 Télécharge ici : https://www.docker.com/products/docker-desktop/

Installe-le, redémarre ton PC, puis vérifie qu'il est bien actif (icône Docker dans la barre des tâches).

---

## Installation (première fois)

1. Ouvre le dossier `cconseil-app` dans l'Explorateur Windows
2. Double-clique sur **`setup.bat`**
3. Attends environ 3–5 minutes (installation automatique)
4. L'application s'ouvre dans ton navigateur

C'est tout.

---

## Utilisation quotidienne

| Action | Commande |
|--------|----------|
| Démarrer l'environnement | Double-clic sur `start.bat` |
| Arrêter l'environnement | Double-clic sur `stop.bat` |
| Accéder à l'application | http://localhost:8080 |
| Accéder à la base de données | http://localhost:8081 (phpMyAdmin) |

---

## Architecture du projet

```
cconseil-app/
│
├── docker-compose.yml          → Orchestration des services (app, db, nginx)
├── docker/
│   ├── Dockerfile              → Image PHP 8.2 + extensions Laravel
│   └── php.ini                 → Configuration PHP (upload, timezone...)
│
├── nginx/
│   └── default.conf            → Configuration du serveur web
│
├── .env.example                → Modèle de configuration
│
├── setup.bat                   → Installation initiale (une fois)
├── start.bat                   → Démarrage quotidien
├── stop.bat                    → Arrêt
│
└── src/                        → Code source Laravel (créé par setup.bat)
    ├── app/
    │   ├── Models/             → Entités : Client, Mission, FinancialData...
    │   ├── Http/Controllers/   → Contrôleurs par module
    │   └── Services/           → Logique métier (calcul KPIs, appel IA...)
    ├── database/
    │   └── migrations/
    │       └── consulting_os/  → Migrations personnalisées (déjà créées)
    ├── resources/views/        → Templates Blade + Tailwind
    └── routes/web.php          → Routes de l'application
```

---

## Modules v1 (Sprints)

### Sprint 1 – MVP utilisable pour Beauty Spa
- [ ] Authentification (Laravel Breeze)
- [ ] Création fiche client
- [ ] Saisie données financières
- [ ] Calcul automatique : seuil de rentabilité, EBE, marge
- [ ] Dashboard simple (vue globale portefeuille)

### Sprint 2 – Intelligence & Documents
- [ ] Analyse IA contextuelle (via API Claude)
- [ ] Génération plan d'action recommandé
- [ ] Génération de compte rendu PDF automatisé
- [ ] Import CSV données financières

---

## Configuration de l'API Claude (IA)

Pour activer la génération automatique de comptes rendus et d'analyses :

1. Crée un compte sur https://console.anthropic.com
2. Génère une clé API
3. Ouvre le fichier `src/.env`
4. Remplace la ligne :
   ```
   ANTHROPIC_API_KEY=sk-ant-VOTRE_CLE_API_ICI
   ```
   par ta vraie clé.

---

## Base de données – Schéma

Conformément au cahier des charges, les tables suivantes sont créées :

| Table | Description |
|-------|-------------|
| `users` | Consultants / accès à l'outil |
| `clients` | Fiches clients (SIRET, secteur, type activité) |
| `missions` | Missions par client (phases, statut, honoraires) |
| `financial_data` | Données financières mensuelles / annuelles |
| `kpis` | Indicateurs calculés (EBE, seuil, CAF, trésorerie...) |
| `action_plans` | Plan d'action avec statuts rouge/orange/vert |
| `meeting_reports` | Comptes rendus générés par IA |

---

## Commandes utiles (avancé)

Exécuter une commande Laravel depuis le terminal Windows :
```bash
docker exec consulting_os_app php artisan [commande]
```

Exemples :
```bash
# Voir les routes disponibles
docker exec consulting_os_app php artisan route:list

# Relancer les migrations
docker exec consulting_os_app php artisan migrate

# Ouvrir un shell dans le conteneur
docker exec -it consulting_os_app bash
```

---

## Déploiement VPS (quand v1 prête)

Le projet est conçu pour être déployé sur ton VPS Linux avec Docker.
Les étapes seront :
1. `git push` du code sur le serveur
2. Ajustement du `.env` (domaine, clés de prod)
3. `docker-compose up -d` sur le VPS
4. Configuration HTTPS avec Let's Encrypt (Certbot)

---

## Support

Ce projet est développé progressivement en suivant la méthodologie sprint définie dans le cahier des charges.
L'objectif Sprint 1 : **être utilisable sur la mission Beauty Spa avant la fin de la semaine 2.**
