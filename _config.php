<?php



/*
 * developed by www.sunnysideup.co.nz
 * author: Nicolaas - modules [at] sunnysideup.co.nz
 *
 **/

Director::addRules(10, array(
	'advertisements//$Action/$ID' => 'AdvertisementController',
));

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START advertisements MODULE ----------------===================
//**** NB, dont forget to theme the templates (and also JS, as shown below!)
//MUST SET
//Object::add_extension('SiteTree', 'AdvertisementDecorator');
//MAY SET
//AdvertisementDecorator::set_use_custom_javascript(false); //if true, this will search for myproject/javascript/AdvertisementsExecutive.js | myproject is usually called mysite.
//AdvertisementDecorator::set_page_classes_without_advertisements(array("UserDefineForm", "ErrorPage")); // excluded from the specified classes
//AdvertisementDecorator::set_page_classes_with_advertisements(array("HomePage")); //ONLY shown on specified classes
//AdvertisementAdmin::set_menu_title("Manage Slides");
//Advertisement::set_thumbnail_size(100);
//Advertisement::set_singular_name("Slide");
//Advertisement::set_plural_name("Slides");
//Advertisement::set_width(100);
//Advertisement::set_height(100);
//Advertisement::set_resize_images(true);
//ADVANCED
//AdvertisementStyle::add_to_array_of_js_file_options("slideshow style 1", "mysite/javascript/slideshow1.js");
//AdvertisementStyle::add_to_array_of_js_file_options("slideshow style 2", "mysite/javascript/slideshow1.js");

//	WITH: http://sunny.svnrepository.com/svn/sunny-side-up-general/dataobjectsorter
//Object::add_extension('Advertisement', 'DataObjectSorterDOD');
//===================---------------- END advertisements MODULE ----------------===================
