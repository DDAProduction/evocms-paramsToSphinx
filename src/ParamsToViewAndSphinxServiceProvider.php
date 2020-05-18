<?php namespace EvolutionCMS\ParamsToViewAndSphinx;

use EvolutionCMS\ServiceProvider;

class ParamsToViewAndSphinxServiceProvider extends ServiceProvider
{
    /**
     * Если указать пустую строку, то сниппеты и чанки будут иметь привычное нам именование
     * Допустим, файл test создаст чанк/сниппет с именем test
     * Если же указан namespace то файл test создаст чанк/сниппет с именем paramsToViewAndSphinx#test
     * При этом поддерживаются файлы в подпапках. Т.е. файл test из папки subdir создаст элемент с именем subdir/test
     */
    protected $namespace = 'paramsToViewAndSphinx';
    /**
     * Register the service provider.
     *
     * @return void
     */
    protected $commands = [
        'EvolutionCMS\ParamsToViewAndSphinx\Console\CronBuildView',
    ];
    public function register()
    {
        $this->commands($this->commands);
        $this->loadPluginsFrom(
            dirname(__DIR__) . '/plugins/'
        );
    }
}