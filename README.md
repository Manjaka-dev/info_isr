# info_isr - Docker (PHP 8.2.4 + MariaDB 10.4.28)

Configuration Docker proche de ton XAMPP local:
- PHP `8.2.4` (Apache)
- MariaDB `10.4.28`
- Routes via `.htaccess` (`mod_rewrite` activé)

## Lancer le projet

```bash
docker compose up -d --build
```

Application:
- `http://localhost:8088/optim/info_isr/`

Admin:
- `http://localhost:8088/optim/info_isr/admin/`

Base de données (depuis l'hôte):
- hôte: `127.0.0.1`
- port: `3307`
- DB: `opti_info`
- user: `app`
- pass: `app`

## Arrêter

```bash
docker compose down
```

## Réinitialiser complètement la DB

```bash
docker compose down -v
docker compose up -d --build
```

Le fichier `data/data.sql` est importé automatiquement au premier démarrage (volume vide).

## Test rapide

```bash
bash docker/smoke-test.sh
```

## Notes

- Le code est monté en volume dans le conteneur web: les changements sont visibles sans rebuild.
- `uploads/` est conservé côté projet et utilisable par l'application.
- La connexion DB est pilotée par variables d'environnement dans `includes/connection.php`.

