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

- probables requerimientos previos a la instalación de las siguientes dependencias
```
sudo apt-get install php-mbstring
sudo apt-get install php-dom
sudo apt-get install php-zip
sudo apt-get install php-gd
sudo apt-get install php-pdo-pgsql
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
para setear el entorno de ejecución de la app
```
cp .env.example .env
```

editarlo para que los servicios apunten donde corresponden
en el siguiente: en el .env del ejemplo hay que cambiar 
```
APP_URL=url_del_servidor_donde_corre_la_aplicacion_laravel

DB_CONNECTION=pgsql
DB_HOST=url_del_servidor_de_DB_postgresql
DB_PORT=5432
DB_DATABASE=base_de_datos
DB_USERNAME=usuario_del_segmentador
DB_PASSWORD=clave_del_usuario_del_segmentador

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
alternativamente puede agregarse la opción ```--recursive``` al hacer el clone principal.

- Run app in http://localhost:8000
```
php artisan serve
```
usar el parámetro APP_URL definido en el .dev
y elegir un puerto libre para que la app laravel esté escuchando
```
php artisan serve --host=url_del_servidor_donde_corre_la_aplicacion_laravel --port=9999
```

* From https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
