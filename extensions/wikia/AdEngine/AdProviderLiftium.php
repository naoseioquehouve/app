<?php

class AdProviderLiftium extends AdProviderIframeFiller implements iAdProvider {

	protected static $instance = false;

        private $slotsToCall = array();

        public function addSlotToCall($slotname){
                $this->slotsToCall[]=$slotname;
        }

	public function batchCallAllowed(){
		return false;
	}

	public function getBatchCallHtml(){
		return false;
	}

	public function getSetupHtml(){
                static $called = false;
                if ($called){
                        return false;
                }
                $called = true;

		global $wgDBname, $wgLang, $wgUser, $wgTitle, $wgLiftiumDevHosts, $wgDevelEnvironment;
		global $wgDartCustomKeyValues;

		// See Liftium.js for documentation on options
		$options = array();
		$options['pubid'] = 999;
		$options['baseUrl'] = '/__varnish_liftium/';
		$options['kv_wgDBname'] = $wgDBname;
                if (is_object($wgTitle)){
                       $options['kv_article_id'] = $wgTitle->getArticleID();
		}
		$cat = AdEngine::getCachedCategory();
		$options['kv_Hub'] = $cat['name'];
		$options['kv_skin'] = $wgUser->getSkin()->getSkinName();
		$options['kv_user_lang'] = $wgLang->getCode();
		$options['kv_cont_lang'] = $GLOBALS['wgLanguageCode'];
		$options['kv_isMainPage'] = ArticleAdLogic::isMainPage();
		$options['kv_page_type'] = ArticleAdLogic::getPageType();
		$options['geoUrl'] = "http://geoiplookup.wikia.com/";
		if (!empty($wgDartCustomKeyValues)) {
			$options['kv_dart'] = $wgDartCustomKeyValues;
		}
		$options['kv_domain'] = $_SERVER['HTTP_HOST'];

		// LiftiumOptions as json
		$out = '<script type="text/javascript">' . "\n";
		$out .= "LiftiumOptions = " . json_encode($options) . ';</script>';

		// Call the script
		global $wgDevelEnvironment;
		if (!empty($_GET['liftium_dev_hosts']) || !empty($wgLiftiumDevHosts)){
			$base = "http://nick.dev.liftium.com/";
			$version = '?' . mt_rand();
			$out .= "<script type=\"text/javascript\">var liftium_dev_hosts = 1;</script>";
		} else if ($wgDevelEnvironment){
			$base = "http://liftium.wikia.com/";
			$version = '?' . mt_rand();
		} else {
			$base = "/__varnish_liftium/";
			$version = "";
		}
		$out .=  '<script type="text/javascript" src="'. $base .'js/Liftium.js' . $version . '"></script>' . "\n";
		$out .=  '<script type="text/javascript" src="'. $base .'js/Wikia.js' . $version . '"></script>' . "\n";

		return $out;
	}

	public static function getInstance() {
		if(self::$instance == false) {
			self::$instance = new AdProviderLiftium();
		}
		return self::$instance;
	}

        public function getAd($slotname, $slot, $params = null){
		$out = $this->getSetupHtml();
		$out .= '<script type="text/javascript">' . "\n" .
			'LiftiumOptions.placement = "' . $slotname . '";' . "\n" . 
			'LiftiumDART.placement = "' . $slotname . '";' . "\n" . 
			'Liftium.callAd("' . $slot['size'] . '");</script>' . "\n";
		return $out;
        }

	protected function getIframeFillFunctionDefinition($function_name, $slotname, $slot) {
		global $wgEnableTandemAds, $wgEnableTandemAds_slave, $wgEnableTandemAds_delay;

                $out = '';
		if (!empty($wgEnableTandemAds) && !empty($wgEnableTandemAds_slave) && in_array($slotname, explode(",", str_replace(" ", "", $wgEnableTandemAds_slave))) && !empty($wgEnableTandemAds_delay)) {
                    // FIXME get rid of c&p
                    $out .= '<script type="text/javascript">' .
                            'function ' . $function_name . '() { ' .
                            'window.setTimeout(\'' .
                            'LiftiumOptions.placement = "' . $slotname . '";' .
                            'Liftium.callInjectedIframeAd("' . addslashes($slot['size']) .
                            '", document.getElementById("' . addslashes($slotname) .'_iframe"))' .
                            '\', ' . $wgEnableTandemAds_delay . ')' .
                            '; }</script>';
		}
                else {
                    $out .= '<script type="text/javascript">' .
                            'function ' . $function_name . '() { ' .
                            'LiftiumOptions.placement = "' . $slotname . '";' . "\n" .
                            'Liftium.callInjectedIframeAd("' . addslashes($slot['size']) .
                            '", document.getElementById("' . addslashes($slotname) .'_iframe")); }</script>';
                }

                return $out;
	}

        function getProviderValues($slot) {
                global $wgLanguageCode;
		$out = "lang=" . preg_replace("/-.*/", "", $wgLanguageCode);

		global $wgDartCustomKeyValues;
		if (!empty($wgDartCustomKeyValues)) {
			$out .= ";" . $wgDartCustomKeyValues;
		}

                return $out;
        }
}
