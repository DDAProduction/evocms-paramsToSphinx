# paramsToSphinx

## Install

1) Install package [evocms-params](https://github.com/DDAProduction/evocms-params)
2) `php artisan package:installrequire ddaproduction/evocms-params_to_sphinx "*"` in you **core/** folder
3) `php artisan migrate --path=vendor/ddaproduction/evocms-params_to_sphinx/database/migrations`
4) Install sphinx 
5) Create ***custom*** path in folder ***/etc/sphinxsearch/***
6) Create in u folder main.conf with default config
7) Add to u cron jobs `php ***YOU_PROJECT_PATH***/core/artisan cron:build_view` 
8) Add to u ***root*** cron jobs `sh ***YOU_PROJECT_PATH***/core/vendor/ddaproduction/evocms-params_to_sphinx/SHSctiprts/create_config.sh`


