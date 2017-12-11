<?php

use Swagger\Client\ApiException;
use Swagger\Client\ArticleVideo\Api\MappingsInternalApi;
use Swagger\Client\ArticleVideo\Models\Mapping;
use Wikia\DependencyInjection\Injector;
use Wikia\Logger\WikiaLogger;
use Wikia\Service\Swagger\ApiProvider;

/**
 * Created by PhpStorm.
 * User: ryba
 * Date: 11/12/2017
 * Time: 12:24
 */
class ArticleVideoService {
	const SERVICE_NAME = 'article-video';
	const INTERNAL_REQUEST_HEADER = 'X-Wikia-Internal-Request';

	private $articleVideoApiClient = null;

	/**
	 * @param $cityId
	 *
	 * @return Mapping[]
	 * @internal param $pageId
	 *
	 */
	public function getFeaturedVideosForWiki( $cityId ): array {
		$key = wfMemcKey( 'article-video', 'get-for-product', $cityId );

		return WikiaDataAccess::cache(
			$key,
			WikiaResponse::CACHE_SHORT,
			function () use ( $cityId ) {
				$api = $this->getMappingsInternalApiClient();
				$api->getApiClient()->getConfig()->setApiKey( self::INTERNAL_REQUEST_HEADER, '1' );
				$mappings = [];

				try {
					list( $response, $code ) = $api->getForProductWithHttpInfo( $cityId );

					if ( $code == 200 ) {
						$mappings = $response;
					}
				} catch ( ApiException $apiException ) {
					WikiaLogger::instance()->debug(
						'could not fetch data from article-video service',
						[
							'exception' => $apiException,
							'status_code' => intval( $apiException->getCode() )
						]
					);
				}

				return $mappings;
			}
		);
	}

	/**
	 * @param $cityId
	 * @param $pageId
	 *
	 * @return string - mediaId of featured video for given video if exists, empty string otherwise
	 */
	public function getFeatureVideoForArticle( $cityId, $pageId ): string {
		$mediaId = '';

		$forArticle = array_map(
			function ( Mapping $mapping ) {
				return $mapping->getMediaId();
			},
			array_filter(
				$this->getFeaturedVideosForWiki( $cityId ),
				function ( Mapping $mapping ) use ( $pageId ) {
					return $mapping->getId() === (string) $pageId;
				}
			)
		);

		if ( !empty( $forArticle ) ) {
			$mediaId = $forArticle[0];
		}

		return $mediaId;
	}

	/**
	 * Get Swagger-generated API client
	 *
	 * @return MappingsInternalApi
	 */
	private function getMappingsInternalApiClient(): MappingsInternalApi {
		if ( is_null( $this->articleVideoApiClient ) ) {
			$this->articleVideoApiClient = $this->createMappingsInternalApiClient();
		}

		return $this->articleVideoApiClient;
	}

	/**
	 * Create Swagger-generated API client
	 *
	 * @return MappingsInternalApi
	 */
	private function createMappingsInternalApiClient(): MappingsInternalApi {
		/** @var ApiProvider $apiProvider */
		$apiProvider = Injector::getInjector()->get( ApiProvider::class );
		$api = $apiProvider->getApi( self::SERVICE_NAME, MappingsInternalApi::class );

		// default CURLOPT_TIMEOUT for API client is set to 0 which means no timeout.
		// Overwriting to minimal value which is 1.
		// cURL function is allowed to execute not longer than 1 second
		$api->getApiClient()->getConfig()->setCurlTimeout( 1 );

		return $api;
	}
}