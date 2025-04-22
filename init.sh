#!/bin/bash

# Crear las carpetas necesarias
mkdir -p /var/www/html/contratos_firmados /var/www/html/logs
mkdir -p /var/www/html/pendientes_firma /var/www/html/logs

# Cambiar la propiedad de las carpetas a 'www-data' (usuario por defecto de Apache)
chown -R www-data:www-data /var/www/html/contratos_firmados /var/www/html/logs
chown -R www-data:www-data /var/www/html/pendientes_firma /var/www/html/logs

# Cambiar permisos (considera no usar 777 para entornos de producción, podrías usar 755 o 775 según sea necesario)
chmod -R 775 /var/www/html/contratos_firmados /var/www/html/logs
chmod -R 775 /var/www/html/pendientes_firma /var/www/html/logs

# Asegúrate de que el directorio de la aplicación web también tenga permisos adecuados
chmod -R 755 ./html

# Iniciar Apache en primer plano para que el contenedor siga ejecutándose
exec apache2-foreground
