# Gestion des congés et des autorisations d'absence – ANPTIC

Application web (Laravel / PHP / PostgreSQL) de gestion des demandes d'autorisation d'absence, de congé administratif et de jouissance de congé, avec circuit de validation multiniveaux.

## Prérequis

- [Docker Desktop](https://docs.docker.com/desktop/) installé et lancé
- [Git](https://git-scm.com/downloads) installé

Aucune autre installation n'est nécessaire (pas besoin de PHP, Composer ou PostgreSQL en local : tout tourne dans les conteneurs).

## Installation et lancement

```bash
git clone https://github.com/Kaborelouise/absence_conge.git
cd absence_conge
docker compose up -d --build
```

La première construction prend 2 à 5 minutes. Les conteneurs s'occupent automatiquement de tout :
- création du fichier `.env` à partir de `.env.docker`
- génération de la clé d'application si besoin
- application des migrations de la base de données

## Accès à l'application

Une fois les conteneurs démarrés, ouvrir dans un navigateur :

**http://localhost:8080**

## Vérifier que tout fonctionne

```bash
docker ps
```

Deux conteneurs doivent apparaître avec le statut "Up" : `absence-conge-app` et `absence-conge-db`.

## Commandes utiles

```bash
# Voir les logs de l'application en direct
docker compose logs -f app

# Ouvrir un terminal dans le conteneur de l'application
docker compose exec app bash

# Arrêter les conteneurs (les données sont conservées)
docker compose down

# Arrêter et supprimer aussi les données de la base
docker compose down -v
```

## Dépannage

| Problème | Solution |
|---|---|
| Port 8080 déjà utilisé | Modifier `"8080:80"` en `"8081:80"` (ou autre) dans `docker-compose.yml`, puis `docker compose up -d --build` |
| Port 5432 déjà utilisé | Modifier `"5432:5432"` en `"5433:5432"` dans `docker-compose.yml` |
| Page blanche ou erreur 500 | Consulter les logs : `docker compose logs --tail 50 app` |
| Changement de code non pris en compte | Reconstruire l'image : `docker compose up -d --build` |

## Structure technique

- **Backend** : PHP / Laravel
- **Base de données** : PostgreSQL 16
- **Conteneurisation** : Docker & Docker Compose (un conteneur pour l'application, un pour la base de données)


> Note importante sur le fichier .env
> Le projet est configuré pour Docker. Si [.env](.env) n'existe pas, Docker le crée automatiquement à partir de [.env.docker](.env.docker).
> Dans ce mode, la base PostgreSQL est accessible via le nom de service `db` à l'intérieur du réseau Docker.
>
> Si vous souhaitez lancer l'application sans Docker, vous devez modifier [.env](.env) pour utiliser PostgreSQL localement :
> DB_HOST=127.0.0.1
> DB_PORT=5432
> DB_DATABASE=absence_conge
> DB_USERNAME=postgres
> DB_PASSWORD=postgres

## Contact

Projet développé par **KABORE Louise Jessica A.** dans le cadre d'un stage à l'ANPTIC.