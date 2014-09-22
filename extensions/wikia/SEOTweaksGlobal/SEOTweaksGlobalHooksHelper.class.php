<?php

/**
 * @author: Jacek Jursza <jacek@wikia-inc.com>
 * @author Jacek 'mech' Woźniak <mech@wikia-inc.com>
 * Date: 28.02.13 14:45
 */
class SEOTweaksGlobalHooksHelper {

	const MAX_WIDTH = 2000;

	/**
	 * Given a file, return url to file, or thumbnail url if size larger than MAX_WIDTH
	 * @param $file File
	 * @return string|false
	 */
	static protected function getResizeImageUrlIfLargerThanMax( $file ) {
		$width = $file->getWidth();
		if ( $width > self::MAX_WIDTH ) {
			$thumbObj = $file->transform( array( 'width' => self::MAX_WIDTH ), 0 );
			if ( $thumbObj ) $fileUrl = $thumbObj->getUrl();
		} else {
			$fileUrl = $file->getUrl();
		}
		return $fileUrl;
	}

	static protected function makeKey( $title ) {
		return wfMemcKey( 'OpenGraphTitleImage', md5( $title->getDBKey() ) );
	}

	/**
	 * Return first image from an article, the biggest as possible
	 * take minimal recommended image size from facebook, and if not found, take minimal requirement
	 * @param $title
	 * @return null|Title
	 */
	static protected function getFirstArticleImage( $title ) {
		$retTitle = self::getFirstArticleImageLargerThan( $title, 600, 315 );
		if ( empty( $retTitle ) ) {
			$retTitle = self::getFirstArticleImageLargerThan( $title, 200 );
		}
		return $retTitle;
	}

	/**
	 * Return first image from an article, matched criteria
	 * @param $title
	 * @param $width
	 * @return null|Title
	 */
	static protected function getFirstArticleImageLargerThan( $title, $width ) {
		$imageServing = new ImageServing( array( $title->getArticleID() ), $width );
		$out = $imageServing->getImages( 1 );
		if ( !empty( $out ) ) {
			///used reset instead direct call because we can get hashmap from ImageServing driver.
			$first = reset( $out );
			$name = $first[ 0 ][ 'name' ];
			return Title::newFromText( $name, NS_FILE );
		}
		return null;
	}

	/**
	 * @param $meta
	 * @param $title Title
	 * @return bool
	 */
	static public function onOpenGraphMetaHeaders( &$meta, $title ) {
		global $wgMemc;
		if ( !empty( $title ) && $title instanceof Title && !$title->isMainPage() ) {
			$namespace = $title->getNamespace();
			if ( $namespace == NS_USER ) {
				return true;
			}
			$cacheKey = self::makeKey( $title );
			$imageUrl = $wgMemc->get( $cacheKey );

			if ( is_null( $imageUrl ) || $imageUrl === false ) {    // not in memcache
				if ( $namespace != NS_FILE ) {
					$title = self::getFirstArticleImage( $title );
				}
				if ( !empty( $title ) ) {
					$file = wfFindFile( $title );
					if ( !empty( $file ) ) {
						$thumb = self::getResizeImageUrlIfLargerThanMax( $file );
						if ( !empty( $thumb ) ) $meta[ "og:image" ] = $thumb;
					}
				}
				if ( isset( $meta[ "og:image" ] ) && ( !empty( $meta[ "og:image" ] ) ) ) {
					$imageUrl = $meta[ "og:image" ];
				} else {
					// Even if there is no og:image, we store the info in memcahe so we don't do the
					// processing again
					$imageUrl = '';
				}
				$wgMemc->set( $cacheKey, $imageUrl );
			}

			if ( !empty( $imageUrl ) ) { // only when there is a thumbnail url
				$meta[ 'og:image' ] = $imageUrl;
			}
		}
		return true;
	}

	static public function onArticleRobotPolicy( &$policy, Title $title ) {

		$ns = MWNamespace::getSubject( $title->getNamespace() );

		if ( in_array( $ns, array( NS_MEDIAWIKI, NS_TEMPLATE ) ) ) {
			$policy = array(
				'index' => 'noindex',
				'follow' => 'follow'
			);
		}
		return true;
	}

	static public function onShowMissingArticle( $article ) {
		global $wgOut;

		if ( $article instanceof Article ) {
			if ( $article->getTitle()->getNamespace() == NS_USER || $article->getTitle()->getNamespace() == NS_USER_TALK ) {
				// bugId:PLA-844
				$wgOut->setRobotPolicy( "noindex,nofollow" );
			}
		}
		return true;
	}
}
