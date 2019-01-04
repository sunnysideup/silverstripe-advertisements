<?php

namespace Sunnysideup\Advertisements\Cms;


use Sunnysideup\Advertisements\Model\Advertisement;
use SilverStripe\Admin\ModelAdmin;



/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class AdvertisementAdmin extends ModelAdmin
{
    public $showImportForm = false;

    private static $managed_models = array(Advertisement::class);

    private static $url_segment = 'ads';

    private static $menu_title = 'Ads / Slides';
}
