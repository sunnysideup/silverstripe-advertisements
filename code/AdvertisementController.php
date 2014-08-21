<?php


class AdvertisementController extends Controller {


	private static $allowed_actions = array(
		"removealladvertisements" => "ADMIN",
		"deletealladvertisements" => "ADMIN"
	);

	function removealladvertisements($request) {
		$id = intval($request->param("ID"))-0;
		$page = SiteTree::get()->byID($id);
		if(!$page) {
			return "this page does not exist";
		}
		DB::query("DELETE FROM SiteTree_Advertisements WHERE SiteTreeID = ".$id);
		DB::query("UPDATE SiteTree SET AdvertisementsFolderID = 0 WHERE SiteTree.ID = ".$id);
		DB::query("UPDATE SiteTree_Live SET AdvertisementsFolderID = 0 WHERE SiteTree_Live.ID = ".$id);
		return sprintf(
			_t("AdvertisementController.REMOVEDALL", 'Removed all %1$s from this page, please reload page to see results.')
			, Config::inst()->get("Advertisement", "plural_name")
		);
	}

	function deletealladvertisements($request) {
		DB::query("DELETE FROM \"Advertisement\"");
		return sprintf(
			_t("AdvertisementController.DELETEDALL", 'Deleted all %1$s from this website, please reload page to see results.')
			, Config::inst()->get("Advertisement", "plural_name")
		);
	}

}
