<?php

/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class AdvertisementAdmin extends ModelAdmin
{
    public $showImportForm = false;

    private static $managed_models = array('Advertisement');

    private static $url_segment = 'ads';

    private static $menu_title = 'Ads / Slides';
}
