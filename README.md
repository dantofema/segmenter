# indec

## Para instalar el entorno de desarrollo se debe:

- Clone GitHub repo
git clone https://github.com/dantofema/segmenter.git

- cd into your project
cd segmenter

- create .gitignore
/node_modules
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
yarn-error.log


- Install Composer Dependencies
composer install

- Install NPM Dependencies
npm install

- Create a copy of your .env file & configure app
cp .env.example .env

- Generate an app encryption key
php artisan key:generate

- Run app in http://localhost:8000
php artisan serve

From https://devmarketer.io/learn/setup-laravel-project-cloned-github-com/
