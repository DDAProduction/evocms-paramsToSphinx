<?php

namespace EvolutionCMS\Custom;

use Event;
use EvolutionCMS\ParamsToViewAndSphinx\Controllers\SphinxFilterParams;

Event::listen('evolution.OnPageNotFound', function ($params) {

    $sphinx = new SphinxFilterParams();
    $resourceFilterData = $sphinx->getFilterAndResourceArray($_SERVER['REQUEST_URI']);
    if (!is_null($resourceFilterData['id'])) {
        $params = $sphinx->getFilterParams($resourceFilterData['id'], $resourceFilterData['filters']);
        echo '<pre>';
        print_r($params);
        exit();
    }
});