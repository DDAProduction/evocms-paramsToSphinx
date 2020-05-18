# paramsToViewAndSphinx

## Install

1) Install package [evocms-params](https://github.com/DDAProduction/evocms-params)
2) `php artisan package:installrequire ddaproduction/evocms-evocms-paramsToViewAndSphinx "*"` in you **core/** folder
3) `php artisan migrate --path=vendor/ddaproduction/evocms-evocms-paramsToViewAndSphinx/database/migrations`
4) Install sphinx 
5) Create ***custom*** path in folder ***/etc/sphinxsearch/***
6) Create in u folder main.conf with default config
7) Add to u cron jobs php `/home/dda/web/filterkomora.ddaproduction.com/public_html/core/artisan cron:build_view` 
8) Add to u ***root*** cron jobs `sh ***YOU_PROJECT_PATH***/core/vendor/ddaproduction/evocms-paramsToViewAndSphinx/SHSctiprts/create_config.sh`


