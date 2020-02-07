# indec

## Para instalar el entorno de desarrollo se debe:

- Clone GitHub repo
```
git clone https://github.com/dantofema/segmenter.git
```
- cd into your project
```
cd segmenter
```
- create .gitignore
```
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
```
composer install
```

- Install NPM Dependencies
```
npm install
```
- Create a copy of your .env file & configure app
```
cp .env.example .env
```

- Generate an app encryption key
```
php artisan key:generate
```


- Para iniciar con una nueva base de datos debe crearse la base de datos una vez configurada en .env
```
php artisan migrate
```

- Para configurar las tareas programadas de laravel agregamos al cron (vía crontab -e)
```
* * * * * cd /home/DCINDEC/mretamozo/segmenter_new && php artisan schedule:run >> /dev/null 2>&1
```

- Se agrego como submodule el proyecto de Segmentacion-CORE, para iniciarlo luego de clonar el repo principal debe ejecutar:
```
git submodule init
git submodule update
```
alternativamente puede agregarse la opción ```--recursive````al hacer el clone principal.

- Run app in http://localhost:8000
```
php artisan serve
```
or
```
php artisan serve --host=domainserver --port=9999
```

* From https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
