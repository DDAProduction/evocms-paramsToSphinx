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
        $resource = $modx->makeDocumentObject($_POST['id']);
        $parents = $modx->getParentIds($_POST['id']);
        \EvolutionCMS\ParamsToViewAndSphinx\Models\CategoryToView::updateOrCreate(['resource_id'=>$_POST['id']],['check'=>'0','category_id'=>$resource['category']]);
        \EvolutionCMS\ParamsToViewAndSphinx\Models\CategoryToView::whereIn('resource_id', $parents)->update(['check'=>0]);
    }
});