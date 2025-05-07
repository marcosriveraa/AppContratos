#!/bin/bash

# Crear las carpetas necesarias
mkdir -p /var/www/html/contratos_firmados
mkdir -p /var/www/html/pendientes_firma
mkdir -p /var/www/html/logs

# Cambiar la propiedad de las carpetas a 'www-data' (usuario por defecto de Apache)
chown -R www-data:www-data /var/www/html

# Cambiar permisos (775 permite escritura por el grupo)
chmod -R 775 /var/www/html

# Iniciar Apache en primer plano para que el contenedor siga ejecutándose
exec apache2-foreground
