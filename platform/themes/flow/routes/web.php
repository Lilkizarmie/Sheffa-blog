<?php

// Custom routes
Route::group(['namespace' => 'Theme\Flow\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {

        // Add your custom route here
        Route::get('ajax/get-panel-inner', 'FlowController@ajaxGetPanelInner')
            ->name('theme.ajax-get-panel-inner');

    });
});

Theme::routes();

Route::group(['namespace' => 'Theme\Flow\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {

        Route::get('/', 'FlowController@getIndex')
            ->name('public.index');

        Route::get('sitemap.xml', 'FlowController@getSiteMap')
            ->name('public.sitemap');

        Route::get('{slug?}' . config('core.base.general.public_single_ending_url'), 'FlowController@getView')
            ->name('public.single');

    });
});
