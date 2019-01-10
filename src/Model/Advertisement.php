<?php

namespace Sunnysideup\Advertisements\Model;

use SilverStripe\Core\Config\Config;
use Sunnysideup\Advertisements\Model\Advertisement;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DB;
use Sunnysideup\Advertisements\Model\AdvertisementDecorator;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;

/* *
 *@author nicolaas[at] sunnysideup.co.nz
 *
 **/

class Advertisement extends DataObject
{
    private static $table_name = 'Advertisement';

    /**
     *
     * @var int
     */
    private static $thumbnail_size = 140;

    /**
     *
     * @var int
     */
    private static $width = 0;

    /**
     *
     * @var int
     */
    private static $height = 0;

    /**
     *
     * @var bool
     */
    private static $show_title = false;

    /**
     *
     * @var bool
     */
    private static $show_description = false;

    /**
     * must be a string as booleans dont work well on configs
     * @varchar yes / no
     */
    private static $resize_images = "yes";

    /**
     *
     * @return string
     */
    public static function recommended_image_size_statement()
    {
        $array = array();
        if (Config::inst()->get(Advertisement::class, "width")) {
            $array[] = "width = ".Config::inst()->get(Advertisement::class, "width")."px";
        }
        if (Config::inst()->get(Advertisement::class, "height")) {
            $array[] = "height = ".Config::inst()->get(Advertisement::class, "height")."px";
        }
        $count = count($array);
        if ($count == 0) {
            return _t("Advertisement.NO_RECOMMENDED_SIZE_HAS_BEEN_SET", "No recommeded image size has been set.");
        } else {
            return _t("Advertisement.RECOMMENDED_SIZE", "Recommended Size").": ".implode(" "._t("Advertisement.AND", "and")." ", $array).".";
        }
    }


    private static $db = array(
        "Title" => "Varchar(255)",
        "ExternalLink" => "Varchar(150)",
        "Description" => "Text",
        "Sort" => "Int"
    );

    private static $has_one = array(
        "AdvertisementImage" => Image::class,
        "LinkedPage" => SiteTree::class,
        "AdditionalImage" => Image::class
    );

    private static $belongs_many_many = array(
        "Parents" => SiteTree::class,
    );

    private static $casting = array(
        "FullTitle" => "HTMLText",
        "Link" => "Varchar",
        "GroupID" => "Int"
    );

    private static $defaults = array(
        "Sort" => 1000
    );

    private static $default_sort = "\"Sort\" ASC, \"Title\" ASC";

    private static $searchable_fields = array(
        "Title" => "PartialMatchFilter"
    );

    private static $singular_name = "Advertisement";

    private static $plural_name = "Advertisements";

    private static $summary_fields = array(
        "FullTitle" => "Image",
        "Link" => "Link"
    );


    /**
     * @alias getLink
     * @return string
     */
    public function Link()
    {
        return $this->getLink();
    }


    /**
     *
     * @return string
     */
    public function getLink()
    {
        $link = '';
        if ($this->ExternalLink) {
            $link = $this->ExternalLink;
        } elseif ($this->LinkedPageID) {
            if ($this->LinkedPage()) {
                $link = $this->LinkedPage()->Link();
            }
        }
        return $link;
    }


    /**
     *
     * @return HTMLText
     */
    public function getFullTitle($additionalStyle = '')
    {
        $s = $this->Title;
        if ($this->AdvertisementImageID) {
            $image = $this->AdvertisementImage();
            if ($image && $image->exists()) {
                $size = Config::inst()->get(Advertisement::class, "thumbnail_size");
                $thumb = $image->Fit(
                    $size,
                    $size
                );
                if ($thumb) {
                    $s = '
                        <img src="'.$thumb->Link().'" title="'.$thumb->Link().'"/ style="vertical-align: top; display: block; float: left; padding-right: 10px; '.$additionalStyle.' "><div style="width: 100%;">'.$s.'</div><div style="clear: left;"></div>';
                }
            }
        }
        return DBField::create_field("HTMLText", $s);
    }

    /**
     *
     * @return HTMLText
     */
    public function FullTitle()
    {
        return $this->getFullTitle();
    }

    /**
     *
     * @return int | null
     */
    public function getGroupID()
    {
        if ($this->AdvertisementImageID) {
            $image = Image::get()->byID($this->AdvertisementImageID);
            if ($image) {
                return $image->ParentID;
            }
        }
    }

    /**
     *
     * @return int | null
     */
    public function GroupID()
    {
        return $this->getGroupID();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab("Root", "Sort");
        $fields->removeFieldFromTab("Root.Main", "AdvertisementImage");
        $fields->removeFieldFromTab("Root.Main", "AdvertisementImageID");
        $fields->removeFieldFromTab("Root.Main", "LinkedPageID");
        $fields->removeFieldFromTab("Root.Main", "ExternalLink");
        $fields->removeFieldFromTab("Root.Parents", "Parents");
        $fields->removeFieldFromTab("Root", "Parents");

        if (! $this->ShowTitle()) {
            $fields->removeFieldFromTab("Root", "Title");
        }

        if (! $this->ShowDescription()) {
            $fields->removeFieldFromTab("Root", "Description");
        }

        $fields->addFieldToTab("Root.Main", ReadonlyField::create("Link", 'Calculated Link'));
        $fields->addFieldToTab("Root.Main", $mainImageField = new UploadField($name = "AdvertisementImage", $title = $this->i18n_singular_name()));
        $mainImageField->setRightTitle(self::recommended_image_size_statement());
        $fields->addFieldToTab(
            "Root.Main",
            $additionalImageField = UploadField::create(
                $name = "AdditionalImage",
                $title = $this->i18n_singular_name()." "._t("Advertisement.ADDITIONAL_IMAGE", "additional image")
            )
        );
        $additionalImageField->setRightTitle(self::recommended_image_size_statement());
        if ($this->ID) {
            $treeField = TreeMultiselectField::create(
                "Parents",
                _t("Advertisement.GETCMSFIELDSPARENTID", "only show on ... (leave blank to show on all "
                    .$this->i18n_singular_name()
                    ." pages)"),
                SiteTree::class
            );
            /*$callback = $this->callbackFilterFunctionForMultiSelect();
            if($callback) {
                $treeField->setFilterFunction ($callback);
            }*/
            $fields->addFieldToTab("Root.ShownOn", $treeField);
        }
        $fields->addFieldToTab(
            "Root.OptionalLink",
            $externalLinkField = new TextField($name = "ExternalLink", $title = _t("Advertisement.GETCMSFIELDSEXTERNALLINK", "link to external site"))
        );
        $externalLinkField->setRightTitle(_t("Advertisement.GETCMSFIELDSEXTERNALLINK_EXPLANATION", "(e.g. http://www.wikipedia.org) - this will override an internal link"));
        $fields->addFieldToTab("Root.OptionalLink", TreeDropdownField::create($name = "LinkedPageID", $title = _t("Advertisement.GETCMSFIELDSEXTERNALLINKID", "link to a page on this website"), $sourceObject = SiteTree::class));
        if (class_exists("DataObjectSorterController")) {
            //sorted on parent page...
        } else {
            $fields->addFieldToTab(
                "Root.Position",
                $sortField = NumericField::create(
                    "Sort",
                    _t("Advertisement.SORT", "Sort index number")
                )
            );
            $sortField->setRightTitle(_t("Advertisement.SORT_EXPLANATION", "the lower the number, the earlier it shows"));
        }
        $fields->removeFieldFromTab("Root.Main", "AlternativeSortNumber");

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->Sort) {
            $defaults = $this->Config()->get("defaults");
            $this->Sort = isset($defaults["Sort"]) ? $defaults["Sort"] : 0;
        }
    }


    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        DB::query("UPDATE \"Advertisement\" SET \"Title\" = \"ID\" WHERE \"Title\" = '' OR \"Title\" IS NULL;");
    }


    /**
     *
     * @return bool
     */
    protected function callbackFilterFunctionForMultiSelect()
    {
        $inc = Config::inst()->get(AdvertisementDecorator::class, "page_classes_with_advertisements");
        $exc = Config::inst()->get(AdvertisementDecorator::class, "page_classes_without_advertisements");
        if (is_array($inc) && count($inc)) {
            $string = 'return in_array($obj->class, array(\''.implode("','", $inc).'\'));';
        } elseif (is_array($exc) && count($exc)) {
            $string = 'return !in_array($obj->class, array(\''.implode("','", $exc).'\'));';
        }
        if (isset($string)) {
            return create_function('$obj', $string);
        } else {
            return false;
        }
    }

    public function Image()
    {
        //will be depreciated in the future.
        return $this->ResizedAdvertisementImage();
    }

    /**
     *
     * @return Image | null
     */
    public function ResizedAdvertisementImage()
    {
        $resizedImage = null;
        $imageID = intval($this->AdvertisementImageID+ 0);
        if ($imageID) {
            if ($this->Config()->get("resize_images") == 'yes') {
                $imageObject = Image::get()->byID($imageID);
                $resizedImage = $imageObject;
                if ($imageObject) {
                    if ($imageObject->ID) {
                        $imageObject->Title = Convert::raw2att($this->Title);
                        $w = Config::inst()->get(Advertisement::class, "width");
                        $h = Config::inst()->get(Advertisement::class, "height");
                        if ($h && $w) {
                            $resizedImage = $imageObject->Fit($w, $h);
                        } elseif ($h) {
                            $resizedImage = $imageObject->ScaleHeight($h);
                        } elseif ($w) {
                            $resizedImage = $imageObject->ScaleWidth($w);
                        } else {
                            $resizedImage = $imageObject;
                        }
                    } else {
                        //debug::show("no image");
                    }
                } else {
                    //debug::show("could not find image");
                }
            } else {
                return $this->AdvertisementImage();
            }
        } else {
            //debug::show("no imageID ($imageID) ");
        }

        return $resizedImage;
    }

    public function ThinyThumb()
    {
        return "This function has not been implemented";
    }

    /**
     *
     * @return bool
     */
    public function ShowTitle()
    {
        return $this->Config()->get('show_title');
    }

    /**
     *
     * @return bool
     */
    public function ShowDescription()
    {
        return $this->Config()->get('show_description');
    }
}
