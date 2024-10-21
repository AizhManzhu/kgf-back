ARG PHP_VER=8.0

FROM webdevops/php-nginx:$PHP_VER-alpine as kgf-back-vendor

WORKDIR /app

COPY ./storage/app /app/storage/app
COPY composer.* ./

RUN chmod 0777 /app/storage/app -R
RUN composer install \
    --no-autoloader \
    --no-interaction \
    --no-scripts

###############################################################################

FROM reg.cic.kz/devops/release.html:latest as release-html
ARG CI_COMMIT_REF_NAME=unknown
ARG CI_PIPELINE_ID=unknown
ARG CI_PIPELINE_IID=unknown
ARG CI_COMMIT_SHORT_SHA=unknown
ARG CI_COMMIT_SHA=unknown
ARG CI_PROJECT_PATH=unknown
ARG CI_JOB_ID=unknown
ARG CI_PROJECT_URL=unknown
ARG TZ="Asia/Almaty"

WORKDIR /app

RUN npm run render -- release.html

###############################################################################


FROM webdevops/php-nginx:$PHP_VER-alpine

WORKDIR /app

COPY --from=kgf-back-vendor /app/vendor /app/vendor
COPY . .


# Copy release.html
COPY --from=release-html /app/release.html /app/public/release.html

COPY .deploy/conf/nginx/default.conf /opt/docker/etc/nginx/vhost.conf

RUN composer dump-autoload

RUN php artisan storage:link
RUN php artisan route:cache

RUN ln -s /shared/.env /app/.env

COPY .deploy/conf/supervisor/kgf-worker.conf /opt/docker/etc/supervisor.d/kgf-worker.conf

# Push useful bash aliases
# https://www.atatus.com/blog/14-useful-bash-aliases-that-make-shell-less-complex-and-more-fun/
RUN echo 'alias a="php /app/artisan"' >> ~/.bashrc
RUN echo 'alias ll="ls -la"' >> ~/.bashrc
RUN echo 'alias tin="php /app/artisan tinker"' >> ~/.bashrc
