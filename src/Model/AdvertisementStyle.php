<?php

namespace Sunnysideup\Advertisements\Model;



use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use Sunnysideup\Advertisements\Model\AdvertisementStyle;
use SilverStripe\ORM\DataObject;



/**
 * @nicolaas [at] sunnysideup.co.nz
 *
 *
 **/


class AdvertisementStyle extends DataObject
{
    private static $db = array(
        "Title" => "Varchar(100)",
        "FileLocation" => "Varchar(100)"
    );

    private static $has_many = array(
        "Parent" => SiteTree::class
    );

    private static $array_of_js_file_options = array();

    private static $fx = array(
        "blindX",
        "blindY",
        "blindZ",
        "cover",
        "curtainX",
        "curtainY",
        "fade",
        "fadeZoom",
        "growX",
        "growY",
        "none",
        "scrollUp",
        "scrollDown",
        "scrollLeft",
        "scrollRight",
        "scrollHorz",
        "scrollVert",
        "shuffle",
        "slideX",
        "slideY",
        "toss",
        "turnUp",
        "turnDown",
        "turnLeft",
        "turnRight",
        "uncover",
        "wipe",
        "zoom"
    );

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if ($a = Config::inst()->get(AdvertisementStyle::class, "array_of_js_file_options")) {
            if (is_array($a)) {
                if (count($a)) {
                    foreach ($a as $k => $v) {
                        if (!AdvertisementStyle::get()->where("Title = '".$k."' OR FileLocation = '".$v."'")->First()) {
                            $o = new AdvertisementStyle();
                            $o->Title = $k;
                            $o->FileLocation = $v;
                            $o->write();
                        }
                    }
                }
            }
        }
    }
}
