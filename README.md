# 📖 Carnet de Lecture — Journal Personnel

Application web pour enregistrer, évaluer et suivre ses lectures de livres au quotidien.

---

## 🛠️ Stack Technique

- **Backend** : PHP 8.2+ / Laravel 11
- **Frontend** : Vue.js 3, Tailwind CSS, Vite
- **Base de données** : SQLite / MySQL

---

## 💻 1. Lancement en Local

### Prérequis
- PHP 8.2+ & Composer
- Node.js 20+ & npm

### Installation
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Lancement
php artisan serve
npm run dev
```

---

## 🚀 2. Déploiement sur Serveur VPS (Debian 13)

### 2.1 Déploiement Laravel
```bash
cd /var/www
sudo git clone <URL_DEPOT_GIT> carnet-lecture
cd carnet-lecture

sudo composer install --no-dev --optimize-autoloader
sudo npm ci && sudo npm run build

sudo cp .env.example .env
sudo nano .env

sudo php artisan key:generate --force
sudo php artisan storage:link
sudo php artisan migrate --force

sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache

sudo chown -R www-data:www-data /var/www/carnet-lecture
sudo chmod -R 775 storage bootstrap/cache
```

### 2.2 Configuration Nginx (`/etc/nginx/sites-available/carnet-lecture`)
```nginx
server {
    listen 80;
    server_name lecture.sanchez-florian.fr;
    root /var/www/carnet-lecture/public;

    index index.php index.html;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/carnet-lecture /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d lecture.sanchez-florian.fr
```
