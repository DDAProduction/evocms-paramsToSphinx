<?php

use EvolutionCMS\Models\SystemSetting;

Event::listen('evolution.onInsertParamToCategory', function ($params) {
    \EvolutionCMS\ParamsToViewAndSphinx\Models\CategoryToView::where('category_id', $params['category_id'])->update(['check'=>0]);
});