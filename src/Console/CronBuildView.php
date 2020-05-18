<?php


namespace EvolutionCMS\ParamsToViewAndSphinx\Console;

use EvolutionCMS\Ddafilters\Models\FilterParams;
use EvolutionCMS\Ddafilters\Models\FilterParamsCategory;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\ParamsToViewAndSphinx\Models\CategoryToView;
use Illuminate\Console\Command;


class CronBuildView extends Command
{

    protected $signature = 'cron:build_view';

    protected $description = 'Build view for category';


    public function handle()
    {
        $modx = EvolutionCMS();

        $views = CategoryToView::where('category_id', '>', 0)->get();
        $bar = $this->output->createProgressBar(count($views));
        $bar->start();
        $dbConfig = include EVO_CORE_PATH . 'config/database/connections/default.php';
        $outConfigs = [];
        foreach ($views as $view) {
            $bar->advance();
            $configs = ['sql_field_string = pagetitle','sql_field_string = content'];
            $select = [];
            $join = [];
            $tableIteration = 1;
            $sphinxSelect = ['id','pagetitle','content'];
            $params = FilterParamsCategory::where('category_id', $view->category_id)->get();
            $view_name = 'view_' . SiteContent::select('alias')->find($view->resource_id)->alias;
            $view_name = str_replace('-','_', $view_name);
            $parents = $modx->getChildIds($view->resource_id);
            $parents = array_values($parents);
            $parents[] = $view->resource_id;
            foreach ($params as $param) {
                $tableIteration++;
                $paramData = FilterParams::find($param->param_id);
                $sphinxSelect[] = $paramData->alias;
                if ($paramData->typeinput == 'select') {
                    $select[] = 't' . $tableIteration . '.value as ' . $paramData->alias;
                    $configs[] = "sql_attr_multi = uint " . $paramData->alias . " from field " . $paramData->alias . "; ";

                } else {
                    $sphinxSelect[] = 'clear_' . $paramData->alias;
                    $select[] = "CONCAT('" . $paramData->prefix . "', t" . $tableIteration . ".value) as " . $paramData->alias;
                    $select[] = "t" . $tableIteration . ".value as clear_" . $paramData->alias;

                    $configs[] = "sql_attr_string = clear_" . $paramData->alias . "";
                    $configs[] = "sql_attr_string = " . $paramData->alias . "";
                }
                $join[] = 'LEFT JOIN ' . $modx->getDatabase()->getFullTableName('site_tmplvar_contentvalues') . ' as t' . $tableIteration . ' 
                                ON t1.id = t' . $tableIteration . '.contentid AND t' . $tableIteration . '.tmplvarid = ' . $paramData->tv_id;

            }
            $query = "CREATE OR REPLACE VIEW  `" . $view_name . "` AS SELECT t1.id, t1.pagetitle, t1.content, " . implode(', ', $select) . " FROM " . $modx->getDatabase()->getFullTableName('site_content') . ' as t1
            ' . implode(' ', $join) . ' WHERE parent IN (' . implode(',', $parents) . ') AND template IN (' . $modx->getConfig('template_products') . ')';
            \DB::select(\DB::raw($query));
            $view->check = 1;
            $view->view_name = $view_name;
            $view->save();

            $sphinxConfig = "source " . $view_name . "
                            {
                              type          = mysql
                            
                              #SQL settings (for ‘mysql’ and ‘pgsql’ types)
                            
                              sql_host      = " . $dbConfig['host'] . "
                              sql_user      = " . $dbConfig['username'] . "
                              sql_pass      = " . $dbConfig['password'] . "
                              sql_db        = " . $dbConfig['database'] . "
                              sql_port      = " . $dbConfig['port'] . " # optional, default is 3306
                              sql_query_pre = SET NAMES utf8
                              sql_query     = SELECT " . implode(',', $sphinxSelect) . " FROM `" . $view_name . "` \n";
            $sphinxConfig .= implode("\n", $configs);
            $sphinxConfig .= "\n}\nindex " . $view_name . "
                                {
                                    source        = " . $view_name . "
                                    path          = /var/lib/sphinxsearch/data/" . $view_name . "
                                    min_infix_len = 3 #Длина инфикса, это необходимо для морфологии
                                    morphology = stem_enru #Указываем наличие морфологии
                                    expand_keywords=1
                                    index_exact_words=1
                                }
                               
                                ";
            $outConfigs[] = $sphinxConfig;



        }
        $config_dir = '/etc/sphinxsearch/custom/';
        if (is_dir($config_dir)) {
            file_put_contents($config_dir.md5(EVO_CORE_PATH).'.conf', implode("\n\n", $outConfigs));
        } else {
            echo "Create custom config folder";
        }
        $bar->finish();


    }

}