# docker build -t j0nix-rest-api .
# docker run -p80:80 -d --name j0nix-rest-api j0nix-rest-api
FROM php:5.6-apache
RUN  a2enmod rewrite
COPY api.php /var/www/html
COPY rest.class.php /var/www/html
COPY .htaccess /var/www/html
RUN chmod -R 0755 /var/www/html
RUN sed -ri -e 's!DocumentRoot.*!&\n\t\t<Directory "/var/www/html">\n\t\t\tAllowOverride All\n\t\t</Directory>!' /etc/apache2/sites-available/*.conf
#RUN service apache2 restart
