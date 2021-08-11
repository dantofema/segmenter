# Proyecto Mandarina
![Logo INDEC][logo]


## Prerequisitos (las instrucciones asumen que se está usando Ubuntu)
* PHP 7: php-mbstring php-dom php-zip php-gd php-pdo-pgsql 
```
sudo apt install php-mbstring php-dom php-zip php-gd php-pdo-pgsql
```
* gdal (ogr2ogr)
```
sudo apt-get install python3.6-dev
sudo add-apt-repository ppa:ubuntugis/ppa && sudo apt-get update
sudo apt-get update
sudo apt-get install gdal-bin
sudo apt-get install libgdal-dev
export CPLUS_INCLUDE_PATH=/usr/include/gdal
export C_INCLUDE_PATH=/usr/include/gdal
pip install GDAL
```
* pgdbf postgis
```
sudo apt install pgdbf postgis
```
* python3 pip psycopg2
```
sudo apt install python3-dev python3-pip
pip3 install psycopg2
```

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
- Definir el entorno creando usando `.env` como template,   
`cp .env.example .env`   
editarlo para configurar la aplicación a que direccione a la BD   

```

APP_URL=<url_del_servidor_donde_corre_la_aplicacion_laravel>:<puerto_del_servicio_de_la_aplicación>

DB_HOST=<url_del_servidor_de_DB_postgresql>
DB_DATABASE=<base_de_datos>
DB_USERNAME=<usuario_del_segmentador>
DB_PASSWORD=<clave_del_usuario_del_segmentador>
```


- Generar una app encryption key
```
php artisan key:generate
```

- Crear la base de datos mencionada en .env, conectarse, cargar postgis y crear el usuario del segmentador
```bash
createdb <base_de_datos> -h <url_del_servidor_de_DB_postgresql> -U <algun_db_admin>
psql <base_de_datos> -h <url_del_servidor_de_DB_postgresql> -U <algun_db_admin> -c 'create extension postgis;' 
```


- Crear la estructura de la base de datos usando migrate
```bash
php artisan migrate
```


- Cargar los datos usando db:seed
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
```bash
php artisan serve
```
usar el parámetro APP_URL definido en el .env
y elegir un puerto libre para que la app laravel esté escuchando
```bash
php artisan serve --host=<url_del_servidor_donde_corre_la_aplicacion_laravel> --port=<puerto_del_servicio_de_la_aplicación>
```
 

[1]: https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
[logo]: https://www.indec.gob.ar/Images_WEBINDEC/Logo/Logo_Indec.png

