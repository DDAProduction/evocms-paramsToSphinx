<?php

namespace EvolutionCMS\Custom;

use Event;
use EvolutionCMS\ParamsToViewAndSphinx\Controllers\SphinxFilterParams;

Event::listen('evolution.OnPageNotFound', function ($params) {

    $sphinx = new SphinxFilterParams();
    //Сюда можно передавать урл чтобы оно само находило текущий ресурс и массив с фильтрацией
    $resourceFilterData = $sphinx->getFilterAndResourceArray($_SERVER['REQUEST_URI']);
    if (!is_null($resourceFilterData['id'])) {
        //Передаём id ресурса для которого строим фильтр и массив фильтров
        $params = $sphinx->getFilterParams($resourceFilterData['id'], $resourceFilterData['filters']);
        echo '<pre>';
        print_r($params);
        exit();
    }
});
