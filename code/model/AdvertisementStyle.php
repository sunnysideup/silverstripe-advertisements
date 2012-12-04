<?php

/**
 * @nicolaas [at] sunnysideup.co.nz
 *
 *
 **/


class AdvertisementStyle extends DataObject {

	public static $db = array (
		"Title" => "Varchar(100)",
		"FileLocation" => "Varchar(100)"
	);

	public static $has_many = array (
		"Parent" => "SiteTree"
	);


	protected static $array_of_js_file_options = array();
		static function set_array_of_js_file_options($v) {self::$array_of_js_file_options = $v;}
		static function get_array_of_js_file_options() {return self::$array_of_js_file_options;}
		static function add_to_array_of_js_file_options($title, $file_location) {
			if(!file_exists(Director::baseFolder()."/".$file_location)) {
				user_error($file_location . " does not exist, please check!", E_USER_NOTICE);
			}
			return self::$array_of_js_file_options[$title] = $file_location;
		}


	protected static $fx = array(
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

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if($a = self::get_array_of_js_file_options()) {
			if(is_array($a)) {
				if(count($a)) {
					foreach($a as $k => $v) {
						if(!DataObject::get("AdvertisementStyle", "Title = '".$k."' OR FileLocation = '".$v."'")) {
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

/*
	$db = array(
// override these globally if you like (they are all optional)
$.fn.cycle.defaults = {
		fx:             "Varchar(50)", // name of transition effect (or comma separated names, ex: fade,scrollUp,shuffle)
		timeout:         "Int",  // milliseconds between slide transitions (0 to disable auto advance)
		//timeoutFn:       "Text",  // callback for determining per-slide timeout value:  function(currSlideElement, nextSlideElement, options, forwardFlag)
		continuous:      "Boolean",     // true to start next transition immediately after current one completes
		speed:           "Int",  // speed of the transition (any valid fx speed value)
		speedIn:         "Int",  // speed of the 'in' transition
		speedOut:        "Int",  // speed of the 'out' transition
		allowPagerClickBubble: "Boolean",  // allows or prevents click event on pager anchors from bubbling
		pagerAnchorBuilder: "Text", // callback fn for building anchor links:  function(index, DOMelement)
		before:          "Text",  // transition callback (scope set to element to be shown):     function(currSlideElement, nextSlideElement, options, forwardFlag)
		after:           "Text",  // transition callback (scope set to element that was shown):  function(currSlideElement, nextSlideElement, options, forwardFlag)
		end:             "Text",  // callback invoked when the slideshow terminates (use with autostop or nowrap options): function(options)
		easing:          "Varchar(50)",  // easing method for both in and out transitions
		easeIn:          "Varchar(50)",  // easing for "in" transition
		easeOut:         "Varchar(50)",  // easing for "out" transition
		shuffle:         "Varchar(50)",  // coords for shuffle animation, ex: { top:15, left: 200 }
		animIn:          "Text",  // properties that define how the slide animates in
		animOut:         "Text",  // properties that define how the slide animates out
		cssBefore:       "Text",  // properties that define the initial state of the slide before transitioning in
		cssAfter:        "Text",  // properties that defined the state of the slide after transitioning out
		fxFn:            "Text",  // function used to control the transition: function(currSlideElement, nextSlideElement, options, afterCalback, forwardFlag)
		height:         "Varchar(20)", // container height
		startingSlide:   "Int",     // zero-based index of the first slide to be displayed
		sync:            "Boolean",     // true if in/out transitions should occur simultaneously
		random:          "Boolean",     // true for random, false for sequence (not applicable to shuffle fx)
		fit:             "Boolean",     // force slides to fit container
		containerResize: "Boolean",     // resize container to fit largest slide
		pause:           "Boolean",     // true to enable "pause on hover"
		pauseOnPagerHover: "Boolean",   // true to pause when hovering over pager link
		autostop:        "Boolean",     // true to end slideshow after X transitions (where X == slide count)
		autostopCount:   "Int",     // number of transitions (optionally used with autostop to define X)
		delay:           "Int",     // additional delay (in ms) for first transition (hint: can be negative)
		slideExpr:       "Text",  // expression for selecting slides (if something other than all children is required)
		cleartype:       "Text",  // true if clearType corrections should be applied (for IE)
		cleartypeNoBg:   "Boolean", // set to true to disable extra cleartype fixing (leave false to force background color setting on slides)
		nowrap:          "Boolean",     // true to prevent slideshow from wrapping
		fastOnEvent:     "Int",     // force fast transitions when triggered manually (via pager or prev/next); value == time in ms
		randomizeEffects:"Boolean",     // valid when multiple effects are used; true to make the effect sequence random
		rev:             "Boolean",     // causes animations to transition in reverse
		manualTrump:     "Boolean",  // causes manual transition to stop an active transition instead of being ignored
		requeueOnImageNotLoaded: "Boolean", // requeue the slideshow if any image slides are not yet loaded
		requeueTimeout:  "Int",   // ms delay for requeue
		//activePagerClass: 'activeSlide', // class name used for the active pager link
		//updateActivePagerLink: null // callback fn invoked to update the active pager link (adds/removes activePagerClass style)
};

	)
*/


}
