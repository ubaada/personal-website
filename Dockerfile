FROM php:8.3.36-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends cron python3-venv tini \
    && rm -rf /var/lib/apt/lists/* \
    && python3 -m venv /opt/analytics
COPY utilities/requirements.txt /opt/analytics/requirements.txt
RUN /opt/analytics/bin/pip install --no-cache-dir -r /opt/analytics/requirements.txt

RUN pecl install apcu-5.1.28 \
    && docker-php-ext-enable apcu \
    && a2enmod rewrite remoteip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && rm -rf /tmp/pear

COPY docker/php.ini /usr/local/etc/php/conf.d/90-site.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/workers.conf /etc/apache2/conf-enabled/90-workers.conf
COPY docker/analytics.cron /etc/cron.d/analytics
COPY docker/website-start /usr/local/bin/website-start
COPY docker/run-analytics.py /opt/analytics/run-analytics.py
COPY utilities/update_view_count.py /opt/analytics/update_view_count.py
COPY --chown=root:root . /var/www/html/
RUN find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chmod 0755 /usr/local/bin/website-start \
    && chmod 0644 /etc/cron.d/analytics \
    && rm -rf /var/www/html/utilities
COPY docker/healthz.php /var/www/html/healthz.php

HEALTHCHECK --interval=15s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/healthz.php || exit 1

ENTRYPOINT ["/usr/bin/tini", "--", "/usr/local/bin/website-start"]
CMD ["apache2-foreground"]
STOPSIGNAL SIGTERM
