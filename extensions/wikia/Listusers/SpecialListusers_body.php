<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 * @author Piotr Molski <moli@wikia-inc.com> for Wikia.com
 * @version: 1.0
 */

class Listusers extends SpecialRedirectToSpecial {
	var $mCityId;
	var $mAction;
	var $mTitle;
	var $mDefGroups;
	var $mFilterStart;
	var $mContribs;

	/* @var ListusersData $mData */
	var $mData;

	var $mDefContrib = null;
	var $searchByUser = '';

	const TITLE		= 'Listusers';
	const DEF_GROUP_NAME	= 'all';
	const DEF_EDITS		= 5;
	const DEF_LIMIT		= 30;
	const DEF_ORDER		= 'dtedit:desc';

	/**
	 * constructor
	 */
	function  __construct() {
		parent::__construct( 'Listusers', self::TITLE  /*class*/ );
	}

	/*
	 * main function - check request params and set defaults
	 *
	 * @access public
	 *
	 * show form
	 */
	public function execute( $subpage ) {
		global $wgOut, $wgCityId;

		if ( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}

		/**
		 * defaults
		 */
		$this->mCityId = $wgCityId;
		// VOLDEV-47
		// remove default highlighted groups (all are now highlighted)
		$this->mDefGroups = array();
		$this->mTitle = Title::makeTitle( NS_SPECIAL, self::TITLE );
		$this->mAction = htmlspecialchars( $this->mTitle->getLocalURL() );
		$this->mContribs = array(
			0	=> wfMessage( 'listusersallusers' )->escaped(),
			1	=> wfMessage( 'listusers-1contribution' )->escaped(),
			5	=> wfMessage( 'listusers-5contributions' )->escaped(),
			10	=> wfMessage( 'listusers-10contributions' )->escaped(),
			20	=> wfMessage( 'listusers-20contributions' )->escaped(),
			50	=> wfMessage( 'listusers-50contributions' )->escaped(),
			100	=> wfMessage( 'listusers-100contributions' )->escaped()
		);

		/**
		 * initial output
		 */
		$wgOut->setPageTitle( wfMessage( 'listuserstitle' )->text() );
		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );
		$target = $this->getRequest()->getVal( 'target' );
		if ( empty( $target ) ) {
			$target = $this->getRequest()->getVal( 'group' );
		}

		if ( !empty( $target ) ) {
			if ( strpos($target, "," ) !== false )  {
				$this->mDefGroups = explode( ",", $target );
			} else {
				$this->mDefGroups = array( $target );
			}
		} elseif ( !empty( $subpage ) ) {
			@list ( $subpage, $this->mDefContrib, $this->searchByUser ) = explode( "/", $subpage );
			if ( !in_array( $this->mDefContrib, array_keys( $this->mContribs ) ) ) {
				$this->mDefContrib = null;
			}
			if ( strpos( $subpage, "," ) !== false )  {
				$this->mDefGroups = explode( ",", $subpage );
			} else {
				$this->mDefGroups = array( $subpage );
			}
		}

		$this->mDefContrib = is_null( $this->mDefContrib ) ? self::DEF_EDITS : $this->mDefContrib;

		$this->mData = new ListusersData( $this->mCityId );
		$this->mData->load();
		$this->mData->setFilterGroup( $this->mDefGroups );

		/**
		 * show form
		 */
		$this->showForm();
	}

	/*
	 * HTML form
	 *
	 * @access public
	 *
	 * show form
	 */
	function showForm ( $error = "" ) {
		global $wgOut, $wgContLang, $wgExtensionsPath, $wgJsMimeType, $wgResourceBasePath, $wgStylePath, $wgUser;

		wfProfileIn( __METHOD__ );

		$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"{$wgResourceBasePath}/resources/wikia/libraries/jquery/datatables/jquery.dataTables.min.js\"></script>\n" );
		$wgOut->addStyle( AssetsManager::getInstance()->getSassCommonURL( 'extensions/wikia/Listusers/css/table.scss' ) );

		$oTmpl = new EasyTemplate( dirname( __FILE__ ) . "/templates/" );
		$oTmpl->set_vars( array(
			"error"			=> $error,
			"action"		=> $this->mAction,
			"wgContLang"		=> $wgContLang,
			"wgExtensionsPath"	=> $wgExtensionsPath,
			"wgStylePath"		=> $wgStylePath,
			"defContrib"		=> $this->mDefContrib,
			"searchByUser"		=> $this->searchByUser,
			"wgUser"		=> $wgUser,
			"title"			=> self::TITLE,
			'groups'        => $this->mData->getGroups(),
			'filtered_group' => $this->mData->getFilterGroup(),
			'contribs'      => $this->mContribs,
		));
		$wgOut->addHTML( $oTmpl->render( "main-form" ) );
		wfProfileOut( __METHOD__ );
	}
}

/**
 * Listusers redirects
 * @author Cqm
 * VOLDEV-49
 */

/**
 * ListStaff --> ListUsers/staff
 */
class SpecialListStaff extends SpecialRedirectToSpecial {
	function __construct() {
		parent::__construct( 'Liststaff', 'Listusers', 'staff' );
	}
}

/**
 * ListVstf --> ListUsers/vstf
 */
class SpecialListVstf extends SpecialRedirectToSpecial {
	function __construct() {
		parent::__construct( 'Listvstf', 'Listusers', 'vstf' );
	}
}

/**
 * ListHelpers --> ListUser/helper
 */
class SpecialListHelpers extends SpecialRedirectToSpecial {
	function __construct() {
		parent::__construct( 'Listhelpers', 'Listusers', 'helper' );
	}
}
