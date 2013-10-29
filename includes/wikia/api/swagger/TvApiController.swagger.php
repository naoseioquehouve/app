<?php

use Swagger\Annotations as SWG;

/**
 * @SWG\Resource(
 * 	apiVersion="1",
 * 	swaggerVersion="1.1",
 * 	resourcePath="Tv",
 * 	basePath="http://www.wikia.com"
 * )
 *
 *
 * @SWG\Model( id="TvResultSet" )
 * @SWG\Property(
 * 		name="seriesName",
 * 		type="string",
 * 		required="true",
 * 		description="sss"
 * 	)
 *
 * @SWG\Model( id="TvItem" )
 * 	@SWG\Property(
 * 		name="title",
 * 		type="string",
 * 		description="The title of the article"
 * 	)
 * 	@SWG\Property(
 * 		name="url",
 * 		type="string",
 * 		description="The relative URL of the Article"
 * 	)
 * 	@SWG\Property(
 * 		name="pageid",
 * 		type="int",
 * 		description="An internal identification number for Article"
 * 	)
 * 	@SWG\Property(
 * 		name="contentUrl",
 * 		type="string",
 * 		description="The URL of plain content of the article"
 * 	)
 *
 * @SWG\Api(
 * 	path="/api/v1/Tv/Article",
 * 	description="Get article against series name and episode name",
 * 	@SWG\Operations(
 * 		@SWG\Operation(
 * 			httpMethod="GET",
 * 			summary="Get article against series name and episode name",
 * 			nickname="getArticle",
 * 			responseClass="TvResultSet",
 *			@SWG\ErrorResponses(
 *				@SWG\ErrorResponse( code="400", reason="seriesName or episodeName not provided" ),
 *				@SWG\ErrorResponse( code="404", reason="Results not found" )
 *				),
 *			@SWG\Parameters(
 *				@SWG\Parameter(
 *					name="seriesName",
 *					description="The name of series",
 *					paramType="query",
 *					required="true",
 *					allowMultiple="false",
 *					dataType="string",
 *					defaultValue=""
 *				),
 *				@SWG\Parameter(
 *					name="episodeName",
 *					description="The name of episode",
 *					paramType="query",
 *					required="true",
 *					allowMultiple="false",
 *					dataType="string",
 *					defaultValue=""
 *				)
 *			)
 *		)
 * 	)
 *)
 *
 */

die;
