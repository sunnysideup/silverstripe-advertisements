<?php

/**
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @description: manage cards
 **/

class AdvertisementAdmin extends ModelAdmin {

	public $showImportForm = false;

	public static $managed_models = array('Advertisement');

	public static $url_segment = 'ads';

	public static $menu_title = 'Ads / Slides';
		static function set_menu_title($v) {self::$menu_title = $v;}
}

