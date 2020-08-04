<?php

namespace EvolutionCMS\ParamsToViewAndSphinx\Controllers;

use EvolutionCMS\Ddafilters\Models\FilterParams;
use EvolutionCMS\Ddafilters\Models\FilterParamsCategory;
use EvolutionCMS\Ddafilters\Models\FilterParamsUnits;
use EvolutionCMS\Ddafilters\Models\FilterParamValues;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\ParamsToViewAndSphinx\Models\CategoryToView;
use Foolz\SphinxQL\Exception\DatabaseException;
use Foolz\SphinxQL\Facet;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;

class SphinxFilterParams
{
    private $conn;
    public $param_name;

    public function __construct()
    {
        $this->conn = new Connection();

    }

    public function getFilterParams($catId, $filter = [])
    {
        $arr_out = [];
        $this->conn->setParams(array('port' => 9306));

        $catToView = CategoryToView::where('resource_id', $catId)->first();
        $params = FilterParamsCategory::where('category_id', $catToView->category_id)
            ->where('show_in_category', 1)->get();
        $param_name = $params->pluck('type_output', 'param_id')->toArray();
        $keys = array_keys($param_name);
        $params_ids = FilterParams::whereIn('id', $keys)->get();
        foreach ($params_ids as $params_id) {
            $this->param_name[$params_id->alias]['output'] = $param_name[$params_id->getKey()];
            $this->param_name[$params_id->alias]['input'] = $params_id->typeinput;
        }

        $arr_out['filters'] = $this->getFacets($catToView->view_name, $params, $filter);
        $arr_out['prodIds'] = $this->getProds($catToView->view_name, $filter);
        return $arr_out;
    }


    public function getProds($viewName, $filter)
    {

        $query = (new SphinxQL($this->conn))->select('id')
            ->from($viewName)->limit(100000)->option('max_matches', 100000);;
        $query = $this->make_filter($query, $filter);
        $result = $query->execute()->fetchAllAssoc();

        return array_column($result, 'id');

    }

    public function getFacets($viewName, $params, $filter)
    {

        $result = [];
        foreach ($params as $param) {
            $temp_filter = $filter;
            $param_desc = FilterParams::find($param->param_id);
            unset($temp_filter[$param_desc->alias]);
            if ($param_desc->typeinput == 'input') $param_desc->alias = 'clear_' . $param_desc->alias;
            $temp_facets = $this->getFacet($viewName, $param_desc->alias, $temp_filter, $param_desc, $param);
            $result = array_merge($result, $temp_facets);
        }

        return $result;

    }


    public function getFacet($viewName, $facet_name, $filters, $param_desc, $param)
    {

        $facet = new Facet($this->conn);
        $facet->facet([$facet_name]);
        $query = (new SphinxQL($this->conn));
        if ($param->type_output == 'range' || $param->type_output == 'rangeslider') {
            $query->select('MIN(' . $facet_name . ')', 'MAX(' . $facet_name . ')', $facet_name)->from($viewName)->facet($facet);
        } else {
            $query->select($facet_name)
                ->from($viewName)->facet($facet);
        }

        $query = $this->make_filter($query, $filters);
        $facets = [];
        try {
            $result = $query->executeBatch();
            do {
                if ($res = $result->store()) {
                    $res_alt = $res->getStored();
                    foreach ($res_alt as $item) {
                        $item = $item->fetchAssoc();
                        if (isset($item['count(*)'])) {
                            if ($param_desc->typeinput == 'input') {
                                $item['alias'] = $param_desc->prefix . $item[$facet_name];
                                $unit = FilterParamsUnits::find($param_desc->unit_id);
                                $item['unit_ru'] = $unit->desc_ru;
                                $item['unit_ua'] = $unit->desc_ua;
                                $item['unit_en'] = $unit->desc_en;
                            } else {
                                $filter_value = FilterParamValues::find($item[$facet_name]);

                                $item['alias'] = $filter_value->alias;
                                $item[$facet_name . '_ru'] = $filter_value->value_ru;
                                $item[$facet_name . '_en'] = $filter_value->value_en;
                                $item[$facet_name . '_ua'] = $filter_value->value_ua;

                            }
                            $facets[$facet_name]['param'][] = $item;
                        } else {
                            if (isset($item['min(' . $facet_name . ')'])) {
                                $facets[$facet_name]['min'] = $item['min(' . $facet_name . ')'];
                            }
                            if (isset($item['max(' . $facet_name . ')'])) {
                                $facets[$facet_name]['max'] = $item['max(' . $facet_name . ')'];
                            }

                        }

                    }

                    //$res->();
                }
            } while ($result->store() && $result->getNext());
            $facets[$facet_name]['desc_out'] = $param->toArray();
            $facets[$facet_name]['desc'] = $param_desc->toArray();
        } catch (DatabaseException $e) {

            $facets = [];
        }

        return $facets;
    }

    public function make_filter($query, $filters)
    {

        foreach ($filters as $key => $filter) {
            if (is_array($filter)) {
                $new_data = [];
                foreach ($filter as $item) {
                    $new_data[] = (int)$item;
                }
                $query->where($key, 'IN', $new_data);
            } else {
                if (stristr($key, 'from_') !== false) {
                    $filter = (int)$filter;
                    $key = str_replace('from_', '', $key);
                    $key = 'clear_' . $key;
                    $query->where($key, '>=', $filter);
                } elseif (stristr($key, 'to_') !== false) {
                    $filter = (int)$filter;
                    $key = str_replace('to_', '', $key);
                    $key = 'clear_' . $key;
                    $query->where($key, '<=', $filter);
                } else {
                    if ($this->param_name[$key]['output'] == 'range' || $this->param_name[$key]['output'] == 'rangeslider' || $this->param_name[$key]['input'] == 'select') {
                        $filter = (int)$filter;
                    }
                    if ($this->param_name[$key]['input'] != 'select')
                        $key = 'clear_' . $key;
                    $query->where($key, '=', $filter);
                }
            }
        }
        return $query;
    }

    public function get_multi_result_set($conn, $statement)
    {
        $results = [];
        $pdo = \DB::connection($conn)->getPdo();
        $result = $pdo->prepare($statement);
        $result->execute();
        do {
            $resultSet = [];
            foreach ($result->fetchall(\PDO::FETCH_ASSOC) as $res) {
                array_push($resultSet, $res);
            }
            array_push($results, $resultSet);
        } while ($result->nextRowset());

        return $results;
    }

    public function getFilterAndResourceArray($url)
    {
        $url = explode('?', $url);
        $url[0] = str_replace('.html', '', $url[0]);
        $url[0] = trim($url[0], "/");
        $arr_url = explode('/', $url[0]);
        $filters = [];
        $id = null;
        $aliasListing = \UrlProcessor::getFacadeRoot()->documentListing;
        while ($id == null && count($arr_url) > 0) {
            if (isset($aliasListing[implode('/', $arr_url)])) {
                $id = $aliasListing[implode('/', $arr_url)];
            } else {
                $filters[] = array_pop($arr_url);
            }
        }
        unset($_GET['q']);
        $filter_array = $_GET;
        if (count($filters) > 0) {
            $filter_array_temp = [];
            $outFilter = FilterParamValues::whereIn('alias', $filters)->get();
            foreach ($outFilter as $item) {
                $filter_array_temp[$item->param_id][] = $item->getKey();
            }
            $keys = array_keys($filter_array_temp);

            $keys = FilterParams::whereIn('id', $keys)->pluck('alias', 'id')->toArray();

            foreach ($filter_array_temp as $key => $value) {
                $filter_array[$keys[$key]] = $value;
            }
        }
        return ['id'=>$id,'filters'=>$filter_array];


    }
}
