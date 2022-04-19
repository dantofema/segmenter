# Horizon / Redis
## Pre-requisito
Servidor redis y cliente redis en php instalados y funcionando.

## Supervisor
Supervisor configuration files are typically stored within your server's `/etc/supervisor/conf.d` directory.

Copiar archivo de configuración [https://github.com/manureta/segmenter/blob/master/horizon.conf]

## Instalación y ejecución

Starting Supervisor

Once the configuration file has been created, you may update the Supervisor configuration and start the monitored processes using the following commands:

```
sudo supervisorctl reread
 
sudo supervisorctl update
 
sudo supervisorctl start horizon
```
