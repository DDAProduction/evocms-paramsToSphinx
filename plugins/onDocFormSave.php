<?php

use EvolutionCMS\Models\SystemSetting;

Event::listen('evolution.OnDocFormSave', function ($params) {
    $modx = EvolutionCMS();
    $templates = $modx->getConfig('template_category');
    $template_arr = [];
    if ($templates != '') {
        $template_arr = explode(',', $templates);
    }
    if (in_array($_POST['template'], $template_arr)) {
        $resource = $modx->makeDocumentObject($params['id']);
        if($resource['category'] > 0 ){
            \EvolutionCMS\ParamsToViewAndSphinx\Models\CategoryToView::updateOrCreate(['resource_id'=>$params['id']],['check'=>'0','category_id'=>$resource['category']]);
        }
    }
});