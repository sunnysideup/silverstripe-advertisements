<?php

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *
 *
 **/

class AdvertisementDecorator extends SiteTreeDecorator {


	public static function add_requirements($alternativeFileLocation = null) {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("advertisements/javascript/Advertisements.js");
		$file = "";
		if(self::$use_custom_javascript) {
			$file = project()."/javascript/AdvertisementsExecutive.js";
		}
		elseif($alternativeFileLocation) {
			$file = $alternativeFileLocation;
		}
		if(!$file)  {
			$file = "advertisements/javascript/AdvertisementsExecutive.js";
		}
		Requirements::javascript($file);
		Requirements::themedCSS("Advertisements");
	}

	function extraStatics() {
		return array(
			'db' => array(
				"UseParentAdvertisements" => "Boolean"
			),
			'has_one' => array(
				"AdvertisementsFolder" => "Folder",
				"AdvertisementStyle" => "AdvertisementStyle"
			),
			'many_many' => array(
				"Advertisements" => "Advertisement"
			)
		);
	}

	protected static $use_custom_javascript = false;
		static function set_use_custom_javascript($b){self::$use_custom_javascript = $b;}

	protected static $page_classes_without_advertisements = array();
		static function get_page_classes_without_advertisements(){return self::$page_classes_without_advertisements;}
		static function set_page_classes_without_advertisements(array $array){
			if(!is_array($array)) {debug::show("argument needs to be an array in AdvertisementsDecorator::set_page_classes_without_advertisements()");}
			self::$page_classes_without_advertisements = $array;
		}

	protected static $specific_name_for_advertisements = "Advertisements";
		static function set_specific_name_for_advertisements($v){self::$specific_name_for_advertisements = $v;}
		static function get_specific_name_for_advertisements(){return self::$specific_name_for_advertisements;}

	protected static $page_classes_with_advertisements = array();
		static function get_page_classes_with_advertisements(){return self::$page_classes_with_advertisements;}
		static function set_page_classes_with_advertisements(array $array){
			if(!is_array($array)) {debug::show("argument needs to be an array in AdvertisementsDecorator::set_page_classes_with_advertisements()");}
			self::$page_classes_with_advertisements = $array;
		}

	protected static $advertisements_dos;

	public function updateCMSFields(FieldSet &$fields) {
		if($this->classHasAdvertisements($this->owner->ClassName)) {
			$tabName = $this->MyTabName();
			//advertisements shown...
			$where = '';
			if($this->owner->AdvertisementsFolderID) {
				$images = DataObject::get("Image", "ParentID = ".$this->owner->AdvertisementsFolderID);
				if($images) {
					$where = "\"AdvertisementImageID\" IN (".implode(",", $images->map("ID", "ID")).")";
				}
				else {
					$where = " 1 = 2";
				}
			}

			//create new advertisements
			$txt = sprintf(_t("AdvertisementDecorator.CREATE", 'Create new %1$s'), Advertisement::$plural_name);
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$txt = sprintf(
				_t(
					"AdvertisementDecorator.CREATENEWFROMFOLDER",
					'Create New %1$s from images in the folder selected below - each image in the folder will be used to create a %3$s. %2$s'
				),
				Advertisement::$plural_name,
				Advertisement::recommended_image_size_statement(),
				Advertisement::$singular_name
			);
			$fields->addFieldToTab($tabName, new TreeDropdownField( 'AdvertisementsFolderID', $txt, 'Folder'));
			//$advertisementsCount = DB::query("SELECT COUNT(ID) FROM \"Advertisement\" $whereDB ;")->value();
			$advertisements = DataObject::get("Advertisement", $where);
			$txt = sprintf(_t("AdvertisementDecorator.ACTUAL", 'Current %1$s Shown'), Advertisement::$plural_name);
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			if($advertisements) {
				$txt = sprintf(_t("AdvertisementDecorator.SELECT", 'Select %1$s to show ... (list below shows all slides available, but on the ticked ones are shown.)'), Advertisement::$plural_name);
				$fields->addFieldToTab($tabName, new CheckboxSetField("Advertisements", $txt, $advertisements->toDropdownMap("ID", "FullTitle")));
				if(class_exists("DataObjectSorterController")) {
					$shownAdvertisements = $this->owner->getManyManyComponents('Advertisements');
					if($shownAdvertisements) {
						$array = $shownAdvertisements->column("ID");
						$idString = implode(",",$array);
						$link = DataObjectSorterController::popup_link("Advertisement", $filterField = "ID", $filterValue = $idString, $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle");
						$fields->addFieldToTab($tabName, new LiteralField("AdvertisementsSorter", $link));
					}
				}
			}
			else {
				$txt = sprintf(
					_t("AdvertisementDecorator.CREATE",'<p>Please <a href="admin/%1$s/">create %2$s</a> on the <a href="admin/%1$s/">%3$s tab</a> first, or see below on how to create %2$s from a folder.</p>'),
					AdvertisementAdmin::$url_segment,
					Advertisement::$plural_name,
					AdvertisementAdmin::$menu_title
				);
				$fields->addFieldToTab($tabName, new LiteralField("AdvertisementsHowToCreate", $txt));
			}
			if($parent = $this->advertisementParent()) {
				$txt = sprintf(_t("AdvertisementDecorator.ORUSE", 'OR  ... use %1$s from  <i>%2$s</i>.'), Advertisement::$plural_name, $parent->Title);
				$fields->addFieldToTab($tabName, new CheckboxField("UseParentAdvertisements", $txt));
			}
			if($styles = DataObject::get("AdvertisementStyle")) {
				$fields->addFieldToTab($tabName, $this->MyHeaderField("Style"));
				$list = $styles->toDropdownMap("ID", "Title",$emptyString = _t("AdvertisementDecorator.SELECTSTYLE", "--select style--"), $sortByTitle = true);
				$fields->addFieldToTab($tabName, new DropdownField("AdvertisementStyleID", _t("AdvertisementDecorator.STYLECREATED", "Select style (styles are created by your developer)"), $list));
			}

			$txt = sprintf(_t("AdvertisementDecorator.EDIT", 'Edit %1$s'), Advertisement::$plural_name);
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$txt = sprintf(
				_t("AdvertisementDecorator.PLEASEMANAGEEXISTING",'<p>Please manage existing %1$s on the <a href="admin/%2$s/">%3$s tab</a>.</p>'),
				Advertisement::$plural_name,
				AdvertisementAdmin::$url_segment,
				AdvertisementAdmin::$menu_title
			);
			$fields->addFieldToTab($tabName, new LiteralField("ManageAdvertisements", $txt));
			$txt = sprintf(_t("AdvertisementDecorator.DELETE", 'Delete %1$s'), Advertisement::$plural_name);
			$fields->addFieldToTab($tabName, $this->MyHeaderField($txt));
			$page = DataObject::get_by_id("SiteTree", $this->owner->ID);

			$txtRemove = sprintf(_t("AdvertisementDecorator.REMOVE", 'Remove all %1$s from this page (%1$s will not be deleted)'), Advertisement::$plural_name);
			$txtConfirmRemove = sprintf(_t("AdvertisementDecorator.CONFIRMREMOVE", 'Are you sure you want to remove all %1$s from this page?'), Advertisement::$plural_name);
			$removeallLink = 'advertisements/removealladvertisements/'.$this->owner->ID.'/';
			$jquery = 'if(confirm(\''.$txtConfirmRemove.'\')) {jQuery(\'#removealladvertisements\').load(\''.$removeallLink.'\');} return false;';
			$fields->addFieldToTab($tabName, new LiteralField("removealladvertisements", '<p><a href="'.$removeallLink.'" onclick="'.$jquery.'"  id="removealladvertisements">'.$txtRemove.'</a></p>'));

			$txtDelete = sprintf(_t("AdvertisementDecorator.DELETE", 'Delete all %1$s from from this website (but not the images associated with them)'), Advertisement::$plural_name);
			$txtConfirmDelete = sprintf(_t("AdvertisementDecorator.CONFIRMDELETE", 'Are you sure you want to delete all %1$s - there is no UNDO?'), Advertisement::$plural_name);
			$deleteallLink = 'advertisements/deletealladvertisements/'.$this->owner->ID.'/';
			$jquery = 'if(confirm(\''.$txtConfirmDelete.'\')) {jQuery(\'#deletealladvertisements\').load(\''.$deleteallLink.'\');} return false;';
			$fields->addFieldToTab($tabName, new LiteralField("deletealladvertisements", '<p><a href="'.$deleteallLink.'" onclick="'.$jquery.'"  id="deletealladvertisements">'.$txtDelete.'</a></p>'));

		}
		return $fields;
	}

	protected function MyTabName() {
		$code = preg_replace("/[^a-zA-Z0-9\s]/", " ", AdvertisementAdmin::$menu_title);
		$code = str_replace(" ", "", $code);
		return "Root.Content.".$code;
	}

	protected function MyHeaderField($title) {
		$code = preg_replace("/[^a-zA-Z0-9\s]/", "", $title);
		$code = str_replace(" ", "", $code);
		return new LiteralField($code, "<h4 style='margin-top: 20px'>$title</h4>");
	}

	function AdvertisementSet($style = null) {
		if($this->classHasAdvertisements($this->owner->ClassName)) {
			$browseSet = $this->advertisementsToShow();
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
			$parent = DataObject::get_by_id("SiteTree", $this->owner->ParentID);
		}
		elseif($this->owner->URLSegment != "home") {
			$parent = DataObject::get_one("SiteTree", "URLSegment = 'home' AND \"ClassName\" <> 'RedirectorPage'");
			if(!$parent) {
				$parent = DataObject::get_one("HomePage");
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
				if(!DataObject::get_by_id("Image", $imageID)) {
					$obj = DataObject::get_by_id("Advertisement", $objectID);
					if($obj) {
						$obj->delete();
						$obj->destroy();
						unset($objects[$objectID]);
					}
				}
			}
			//check if a folder has been set and create objects
			if($this->owner->AdvertisementsFolderID) {
				$dos2 = DataObject::get(
					"Image",
					"\"File\".\"ParentID\" = ".$this->owner->AdvertisementsFolderID." AND \"Advertisement\".\"AdvertisementImageID\" IS NULL ",
					"",
					"LEFT JOIN \"Advertisement\" ON \"Advertisement\".\"AdvertisementImageID\" = \"File\".\"ID\" "
				);
				if($dos2) {
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
				if(!DataObject::get_by_id("AdvertisementStyle",$this->owner->AdvertisementStyleID)) {
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
			$parent = $this->advertisementParent();
			if($parent) {
				return $parent->advertisementsToShow();
			}
		}
		return $this->owner->Advertisements();
	}

	protected function getResizedAdvertisements(){

	}

	protected function classHasAdvertisements($className) {
		//assumptions:
		//1. in general YES
		//2. if list of WITH is shown then it must be in that
		//3. otherwise check if it is specifically excluded (WITHOUT)
		$result = true;
		$inc =  self::get_page_classes_with_advertisements();
		$exc =  self::get_page_classes_without_advertisements();
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

