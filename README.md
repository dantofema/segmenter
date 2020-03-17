# Proyecto Mandarina
![Logo INDEC][logo] INDEC


## Prerequisitos
* PHP 7 (gd,pdo-psql,mbstring)
* gdal (ogr2ogr)
* pgdbf

## Para instalar el entorno de desarrollo se debe, según extracto de [guia][1]:

- Clone GitHub repo
```git
git clone https://github.com/manureta/segmenter.git --recurse-submodules 
```
- cd into your project
```bash
cd segmenter
```
- create .gitignore
```bash
echo "/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log" > .gitignore
```


- Install Composer Dependencies
```bash
composer install
```

- Install NPM Dependencies
```bash
npm install
```
- Create a copy of your .env file & configure app
```bash
cp .env.example .env
```

- Generate an app encryption key
```bash
php artisan key:generate
```


- Para iniciar con una nueva base de datos debe crearse la base de datos una vez configurada en .env
```bash
php artisan migrate
```

- Para configurar las tareas programadas de laravel agregamos al cron (vía crontab -e)
```
* * * * * cd segmenter && php artisan schedule:run >> /dev/null 2>&1
```


- En caso que no haya iniciado el submodule con ```--recursive``` al hacer el clone principal.

Debera agrega como submodule el proyecto de Segmentacion-CORE, para iniciarlo luego de clonar el repo principal debe ejecutar:
```bash
git submodule init
git submodule update
```


- Para correr la aplicación en desarrollo: 

Run app in http://localhost:8000
```
php artisan serve
```
or
```
php artisan serve --host=domainserver --port=9999
```

[1]: https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
[logo]: https://www.indec.gob.ar/Images_WEBINDEC/Logo/Logo_Indec.png

