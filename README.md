# paramsToSphinx

## Install

1) Install package [evocms-params](https://github.com/DDAProduction/evocms-params)
2) Configure your evocms-params
3) `php artisan package:installrequire ddaproduction/evocms-params_to_sphinx "*"` in you **core/** folder
4) `php artisan migrate --path=vendor/ddaproduction/evocms-params_to_sphinx/database/migrations`
5) Install sphinx 
6) Create ***custom*** path in folder ***/etc/sphinxsearch/***
7) Create in u folder main.conf with default config
8) Add to u cron jobs `php ***YOU_PROJECT_PATH***/core/artisan cron:build_view` 
9) Add to u ***root*** cron jobs `sh ***YOU_PROJECT_PATH***/core/vendor/ddaproduction/evocms-params_to_sphinx/SHSctiprts/create_config.sh`


