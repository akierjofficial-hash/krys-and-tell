FROM richarvey/nginx-php-fpm:3.1.6

# Put Laravel exactly where this image expects it
WORKDIR /var/www/html
COPY . /var/www/html

# Use your Laravel nginx config (this is the one that matters)
COPY conf/nginx-site.conf /etc/nginx/sites-available/default.conf

# Render/Laravel env
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

ENV COMPOSER_ALLOW_SUPERUSER=1

CMD ["/start.sh"]
