 Mandarina
![Logo INDEC][logo] INDEC


## Prerequisitos
* PHP 7 
```
php-mbstring
php-dom
php-zip
php-gd
php-pdo-pgsql
```
* gdal (ogr2ogr)
* pgdbf

## Para instalar el entorno de desarrollo se debe, (según extracto de [guia][1]):

- Clonar repositorio GitHub
```git
git clone https://github.com/manureta/segmenter.git --recurse-submodules 
```
- cambiar directorio a su proyecto
```bash
cd segmenter
```
- crear archivo .gitignore
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

- Instalar Composer Dependencias
```bash
composer install
```

- Instalar NPM Dependencias
```bash
npm install
```
- Cree su propia copia del archivo .env & configure su app,
para setear el entorno de ejecución de la app
```bash
cp .env.example .env
```

Luego editar el archivo .env para que direccione donde corresponde,
en el siguiente ejemplo hay que cambiar 
```
APP_URL=url_del_servidor_donde_corre_la_aplicacion_laravel

DB_HOST=url_del_servidor_de_DB_postgresql

DB_DATABASE=base_de_datos
DB_USERNAME=usuario_del_segmentador
DB_PASSWORD=clave_del_usuario_del_segmentador

```


- Generar una app encryption key
```bash
php artisan key:generate
```


- Para iniciar con una nueva base de datos debe crearse la base de datos una vez configurada en .env
```bash
php artisan migrate
```


- Una vez creada la estructura de base de datos con migrate, se carga con datos:
```bash
php artisan db:seed
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
usar el parámetro APP_URL definido en el .dev
y elegir un puerto libre para que la app laravel esté escuchando
```
php artisan serve --host=url_del_servidor_donde_corre_la_aplicacion_laravel --port=9999
```

[1]: https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
[logo]: https://www.indec.gob.ar/Images_WEBINDEC/Logo/Logo_Indec.pngoyecto Mandarina
![Logo INDEC][logo] INDEC


## Prerequisitos
* PHP 7 
```
php-mbstring
php-dom
php-zip
php-gd
php-pdo-pgsql
```
* gdal (ogr2ogr)
* pgdbf

## Para instalar el entorno de desarrollo se debe, (según extracto de [guia][1]):

- Clonar repositorio GitHub
```git
git clone https://github.com/manureta/segmenter.git --recurse-submodules 
```
- cambiar directorio a su proyecto
```bash
cd segmenter
```
- crear archivo .gitignore
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

- Instalar Composer Dependencias
```bash
composer install
```

- Instalar NPM Dependencias
```bash
npm install
```
- Cree su propia copia del archivo .env & configure su app,
para setear el entorno de ejecución de la app
```bash
cp .env.example .env
```

Luego editar el archivo .env para que direccione donde corresponde,
en el siguiente ejemplo hay que cambiar 
```
APP_URL=url_del_servidor_donde_corre_la_aplicacion_laravel

DB_HOST=url_del_servidor_de_DB_postgresql

DB_DATABASE=base_de_datos
DB_USERNAME=usuario_del_segmentador
DB_PASSWORD=clave_del_usuario_del_segmentador

```


- Generar una app encryption key
```bash
php artisan key:generate
```


- Para iniciar con una nueva base de datos debe crearse la base de datos una vez configurada en .env
```bash
php artisan migrate
```


- Una vez creada la estructura de base de datos con migrate, se carga con datos:
```bash
php artisan db:seed
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
usar el parámetro APP_URL definido en el .dev
y elegir un puerto libre para que la app laravel esté escuchando
```
php artisan serve --host=url_del_servidor_donde_corre_la_aplicacion_laravel --port=9999
```

[1]: https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
[logo]: https://www.indec.gob.ar/Images_WEBINDEC/Logo/Logo_Indec.png

