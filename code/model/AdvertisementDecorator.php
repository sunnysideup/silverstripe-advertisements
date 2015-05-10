<?php

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *
 *
 **/

class AdvertisementDecorator extends SiteTreeExtension {

	/**
	 * load an alternative collection of JS file to power your
	 * slideslow
	 * see yml files for example
	 * @var Array
	 */
	private static $alternative_javascript_file_array = array();

	public static function add_requirements($alternativeFileLocation = null) {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		$jsFileArray = Config::inst()->get("AdvertisementDecorator", "alternative_javascript_file_array");

		if(count($jsFileArray)) {
			foreach($jsFileArray as $file) {
				Requirements::javascript($file);
			}
		}
		else {
			Requirements::javascript("advertisements/javascript/Advertisements.js");
			$file = "";
			$customJavascript = Config::inst()->get("AdvertisementDecorator", "use_custom_javascript");
			if($customJavascript == 1) {
				$file = project()."/javascript/AdvertisementsExecutive.js";
			}
			elseif($alternativeFileLocation) {
				$file = $alternativeFileLocation;
			}
			if(!$file)  {
				$file = "advertisements/javascript/AdvertisementsExecutive.js";
			}
			Requirements::javascript($file);
			Requirements::themedCSS("Advertisements", "advertisements");
		}
	}

	private static $db = array(
		"UseParentAdvertisements" => "Boolean"
	);

	private static $has_one = array(
		"AdvertisementsFolder" => "Folder",
		"AdvertisementStyle" => "AdvertisementStyle"
	);

	private static $many_many = array(
		"Advertisements" => "Advertisement"
	);

	private static $use_custom_javascript = false;

	private static $page_classes_without_advertisements = array();

	private static $specific_name_for_advertisements = "Advertisements";

	private static $page_classes_with_advertisements = array();

	private static $advertisements_dos = null;

	public function updateCMSFields(FieldList $fields) {
		if($this->classHasAdvertisements($this->owner->ClassName)) {
			$tabName = $this->MyTabName();
			//advertisements shown...
			$where = '1 = 1';
			if($this->owner->AdvertisementsFolderID) {
				$images = Image::get()->filter("ParentID", $this->owner->AdvertisementsFolderID);
				if($images->count()) {
					$where = "\"AdvertisementImageID\" IN (".implode(",", $images->column("ID")).")";
				}
				else {
					$where = " 1 = 2";
				}
			}

			//$advertisementsCount = DB::query("SELECT COUNT(ID) FROM \"Advertisement\" $whereDB ;")->value();
			$advertisements = $this->owner->Advertisements()->where($where);
			$txt = sprintf(_t("AdvertisementDecorator.ACTUAL", 'Current %1$s Shown'), Config::inst()->get("Advertisement", "plural_name"));
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$txt = sprintf(_t("AdvertisementDecorator.SELECT", 'Select %1$s to show ... (list below shows all slides available, but on the ticked ones are shown.)'), Config::inst()->get("Advertisement", "plural_name"));
			$advertisementsGridField = new GridField('Advertisements',  $txt,  $this->owner->Advertisements(), GridFieldConfig_RelationEditor::create());
			$fields->addFieldToTab($tabName, $advertisementsGridField);
			if(class_exists("DataObjectSorterController")) {
				$shownAdvertisements = $this->owner->getManyManyComponents('Advertisements');
				if($shownAdvertisements) {
					$array = $shownAdvertisements->column("ID");
					$idString = implode(",",$array);
					$link = DataObjectSorterController::popup_link("Advertisement", $filterField = "ID", $filterValue = $idString, $linkText = "sort ".Config::inst()->get("Advertisement", "plural_name"), $titleField = "FullTitle");
					$fields->addFieldToTab($tabName, new LiteralField("AdvertisementsSorter", $link));
				}
			}
			if($advertisements->count()) {

			}
			else {
				$txt = sprintf(
					_t("AdvertisementDecorator.CREATE",'<p>Please <a href="admin/%1$s/">create %2$s</a> on the <a href="admin/%1$s/">%3$s tab</a> first, or see below on how to create %2$s from a folder.</p>'),
					Config::inst()->get("AdvertisementAdmin", "url_segment"),
					Config::inst()->get("Advertisement", "plural_name"),
					Config::inst()->get("AdvertisementAdmin", "menu_title")
				);
				$fields->addFieldToTab($tabName, new LiteralField("AdvertisementsHowToCreate", $txt));
			}
			if($parent = $this->advertisementParent()) {
				$txt = sprintf(_t("AdvertisementDecorator.ORUSE", 'OR  ... use %1$s from  <i>%2$s</i>.'), Config::inst()->get("Advertisement", "plural_name"), $parent->Title);
				$fields->addFieldToTab($tabName, new CheckboxField("UseParentAdvertisements", $txt));
			}

			//create new advertisements
			$txt = sprintf(_t("AdvertisementDecorator.CREATE", 'Create new %1$s'), Config::inst()->get("Advertisement", "plural_name"));
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$txt = sprintf(
				_t(
					"AdvertisementDecorator.CREATENEWFROMFOLDER_EXPLANATION",
					'Create New %1$s from images in the folder selected - each image in the folder will be used to create a %3$s. %2$s'
				),
				Config::inst()->get("Advertisement", "plural_name"),
				Advertisement::recommended_image_size_statement(),
				Config::inst()->get("Advertisement", "singular_name")
			);
			if(Folder::get()->count()) {
				$fields->addFieldToTab(
					$tabName,
					$treeDropdownField = new TreeDropdownField(
						'AdvertisementsFolderID',
						_t("AdvertisementDecorator.CREATENEWFROMFOLDER", "Create from folder"),
						'Folder'
					)
				);
				$treeDropdownField->setRightTitle($txt);
			}

			$styles = AdvertisementStyle::get();
			if($styles->count()) {
				$fields->addFieldToTab($tabName, $this->MyHeaderField("Style"));
				$list = $styles->map("ID", "Title", $emptyString = _t("AdvertisementDecorator.SELECTSTYLE", "--select style--"), $sortByTitle = true);
				$fields->addFieldToTab(
					$tabName,
					$selectStyleField = new DropdownField(
						"AdvertisementStyleID",
						_t("AdvertisementDecorator.STYLECREATED", "Select style"),
						$list
					)
				);
				$selectStyleField->setRightTitle(_t("AdvertisementDecorator.STYLECREATED_EXPLANATION", "Styles are created by your developer"));
			}

			$txt = sprintf(_t("AdvertisementDecorator.EDIT", 'Edit %1$s'), Config::inst()->get("Advertisement", "plural_name"));
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$txt = sprintf(
				_t("AdvertisementDecorator.PLEASEMANAGEEXISTING",'<p>Please manage existing %1$s on the <a href="admin/%2$s/">%3$s tab</a>.</p>'),
				Config::inst()->get("Advertisement", "plural_name"),
				Config::inst()->get("AdvertisementAdmin", "url_segment"),
				Config::inst()->get("AdvertisementAdmin", "menu_title")
			);
			$fields->addFieldToTab($tabName, new LiteralField("ManageAdvertisements", $txt));
			$txt = sprintf(_t("AdvertisementDecorator.DELETE", 'Delete %1$s'), Config::inst()->get("Advertisement", "plural_name"));
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$page = SiteTree::get()->byID($this->owner->ID);

			$txtRemove = sprintf(_t("AdvertisementDecorator.REMOVE", 'Remove all %1$s from this page (%1$s will not be deleted)'), Config::inst()->get("Advertisement", "plural_name"));
			$txtConfirmRemove = sprintf(_t("AdvertisementDecorator.CONFIRMREMOVE", 'Are you sure you want to remove all %1$s from this page?'), Config::inst()->get("Advertisement", "plural_name"));
			$removeallLink = 'advertisements/removealladvertisements/'.$this->owner->ID.'/';
			$jquery = 'if(confirm(\''.$txtConfirmRemove.'\')) {jQuery(\'#removealladvertisements\').load(\''.$removeallLink.'\');} return false;';
			$fields->addFieldToTab($tabName, new LiteralField("removealladvertisements", '<p class="message warning"><a href="'.$removeallLink.'" onclick="'.$jquery.'"  id="removealladvertisements"  class="ss-ui-button">'.$txtRemove.'</a></p>'));

			$txtDelete = sprintf(_t("AdvertisementDecorator.DELETE", 'Delete all %1$s from from this website (but not the images associated with them)'), Config::inst()->get("Advertisement", "plural_name"));
			$txtConfirmDelete = sprintf(_t("AdvertisementDecorator.CONFIRMDELETE", 'Are you sure you want to delete all %1$s - there is no UNDO?'), Config::inst()->get("Advertisement", "plural_name"));
			$deleteallLink = 'advertisements/deletealladvertisements/'.$this->owner->ID.'/';
			$jquery = 'if(confirm(\''.$txtConfirmDelete.'\')) {jQuery(\'#deletealladvertisements\').load(\''.$deleteallLink.'\');} return false;';
			$fields->addFieldToTab($tabName, new LiteralField("deletealladvertisements", '<p class="message bad"><a href="'.$deleteallLink.'" onclick="'.$jquery.'"  id="deletealladvertisements" class="ss-ui-button">'.$txtDelete.'</a></p>'));

		}
		return $fields;
	}

	protected function MyTabName() {
		$code = preg_replace("/[^a-zA-Z0-9\s]/", " ", Config::inst()->get("AdvertisementAdmin", "menu_title"));
		$code = str_replace(" ", "", $code);
		return "Root.".$code;
	}

	protected function MyHeaderField($title) {
		$code = preg_replace("/[^a-zA-Z0-9\s]/", "", $title);
		$code = str_replace(" ", "", $code);
		return new LiteralField($code, "<h4 style='margin-top: 20px'>$title</h4>");
	}

	function AdvertisementSet($style = null) {
		if($this->classHasAdvertisements($this->owner->ClassName)) {
			$browseSet = $this->owner->advertisementsToShow();
			if($browseSet) {
				$file = null;
				if($this->owner->AdvertisementStyleID) {
					$style = $this->owner->AdvertisementStyle();
				}
				if($style) {
					$file = $style->FileLocation;
				}
				self::add_requirements($file);

				return $browseSet;
			}
		}
	}

	protected function advertisementParent() {
		$parent = null;
		if($this->owner->ParentID) {
			$parent = SiteTree::get()->byID($this->owner->ParentID);
		}
		elseif($this->owner->URLSegment != "home") {
			$parent = SiteTree::get()->where("URLSegment = 'home' AND \"ClassName\" <> 'RedirectorPage'")->First();
			if(!$parent) {
				if(class_exists("HomePage")) {
					$parent = HomePage::get()->First();
				}
				else {
					$parent = Page::get()->filter(array("URLSegment" => "home"))->First();
				}
			}
		}
		if($parent) {
			if($this->classHasAdvertisements($parent->ClassName)) {
				return $parent;
			}
		}
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		if($this->classHasAdvertisements($this->owner->ClassName)) {
			$objects = array(0 => 0);
			$images = array(0 => 0);
			$dos1 = $this->advertisementsToShow();
			if($dos1) {
				foreach($dos1 as $obj) {
					$images[$obj->ID] = $obj->AdvertisementImageID;
					$objects[$obj->ID] = $obj->ID;
				}
			}
			//check for non-existing images and delete advertisements associated with it

			foreach($images as $objectID => $imageID) {
				if(!Image::get()->byID($imageID)) {
					$obj = Advertisement::get()->byID($objectID);
					if($obj) {
						$obj->delete();
						$obj->destroy();
						unset($objects[$objectID]);
					}
				}
			}
			//check if a folder has been set and create objects
			if($this->owner->AdvertisementsFolderID) {
				$dos2 = Image::get()
					->where("\"File\".\"ParentID\" = ".$this->owner->AdvertisementsFolderID." AND \"Advertisement\".\"AdvertisementImageID\" IS NULL ")
					->leftJoin("Advertisement",  "\"Advertisement\".\"AdvertisementImageID\" = \"File\".\"ID\" ");
				if($dos2->count()) {
					$advertisementsToAdd = array();
					foreach($dos2 as $image) {
						$newAdvertisement = new Advertisement();
						$newAdvertisement->AdvertisementImageID = $image->ID;
						$newAdvertisement->Title = $image->Title;
						$newAdvertisement->AutoAdded = true;
						$newAdvertisement->write();
						$objects[$newAdvertisement->ID] = $newAdvertisement->ID;
					}
					$this->owner->Advertisements()->addMany($objects);
				}
			}
			if($this->owner->AdvertisementStyleID) {
				if(!AdvertisementStyle::get()->byID($this->owner->AdvertisementStyleID)) {
					$this->owner->AdvertisementStyleID = 0;
				}
			}
			//remove advdertisements if parent is being used...
			if($this->owner->UseParentAdvertisements) {
				if($this->advertisementParent()) {
					$combos = $this->owner->Advertisements();
					if($combos) {
						$combos->removeAll();
					}
				}
				else {
					$this->owner->UseParentAdvertisements  = false;
				}
			}
		}
	}

	public function advertisementsToShow() {
		if($this->owner->UseParentAdvertisements) {
			$parent = $this->owner->advertisementParent();
			if($parent) {
				return $parent->advertisementsToShow();
			}
		}
		return $this->owner->Advertisements();
	}

	/*
	protected function getResizedAdvertisements(){

	}
	*/

	protected function classHasAdvertisements($className) {
		//assumptions:
		//1. in general YES
		//2. if list of WITH is shown then it must be in that
		//3. otherwise check if it is specifically excluded (WITHOUT)
		$result = true;
		$inc =  Config::inst()->get("AdvertisementDecorator","page_classes_with_advertisements");
		$exc =  Config::inst()->get("AdvertisementDecorator","page_classes_without_advertisements");
		if(is_array($inc) && count($inc)) {
			$result = false;
			if(in_array($className,$inc)) {
				$result = true;
			}
		}
		elseif(is_array($exc) && count($exc) && in_array($className,$exc))  {
			$result = false;
		}
		return $result;
	}



}

