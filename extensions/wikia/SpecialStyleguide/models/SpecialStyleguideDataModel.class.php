<?php

/**
 * Class SpecialStyleguideDataModel
 * Handles getting of data
 */
class SpecialStyleguideDataModel {

	private $sectionData;

	public function __construct() {
		$this->sectionData = [
			'header' => [
				'home' => [
					'mainHeader' => wfMessage( 'styleguide-home-header' )->plain(),
					'getStartedBtnLink' => '',
					'getStartedBtnTitle' => wfMessage( 'styleguide-get-started' )->plain(),
					'getStartedBtnLabel' => wfMessage( 'styleguide-get-started' )->plain(),
					'version' => 'Version 1.0.0'
				],
				'components' => [
					'sectionHeader' => wfMessage( 'styleguide-components-header' )->plain(),
					'tagLine' => wfMessage( 'styleguide-components-header-tagline' )->plain(),
				],
				'tagLine' => wfMessage( 'styleguide-home-header-tagline' )->plain(),
			],
			'footer' => [
				'list' => [
					[
						'link' => '#',
						'linkTitle' => wfMessage( 'styleguide-blog' )->plain(),
						'linkLabel' => wfMessage( 'styleguide-blog' )->plain(),
					],
					[
						'link' => '#',
						'linkTitle' => wfMessage( 'styleguide-changelog' )->plain(),
						'linkLabel' => wfMessage( 'styleguide-changelog' )->plain(),
					]
				]
			],
			'home' => [
				'sections' => [
					[
						'header' => wfMessage( 'styleguide-home-welcome-message' )->plain(),
						'paragraph' => wfMessage( 'styleguide-home-welcome-tagline' )->plain(),
					],
					[
						'header' => wfMessage( 'styleguide-home-stakeholders-header' )->plain(),
						'paragraph' => wfMessage( 'styleguide-home-stakeholders-paragraph' )->plain(),
					],
					[
						'header' => wfMessage( 'styleguide-home-team-header' )->plain(),
						'paragraph' => wfMessage( 'styleguide-home-team-paragraph' )->plain(),
						'list' => [
							[
								'link' => '',
								'linkTitle' => 'Elizabeth Worthy',
								'linkName' => 'Elizabeth Worthy',
								'linkTagline' => wfMessage( 'styleguide-home-team-pm' )->plain(),
							],
							[
								'link' => '',
								'linkTitle' => 'Rafał Leszczyński',
								'linkName' => 'Rafał Leszczyński',
								'linkTagline' => wfMessage( 'styleguide-home-team-engineer' )->plain(),
							],
							[
								'link' => '',
								'linkTitle' => 'Mika Kozma',
								'linkName' => 'Mika Kozma',
								'linkTagline' => wfMessage( 'styleguide-home-team-designer' )->plain(),
							],
							[
								'link' => '',
								'linkTitle' => 'Earl Carlson',
								'linkName' => 'Earl Carlson',
								'linkTagline' => wfMessage( 'styleguide-home-team-designer' )->plain()
							]
						]
					]
				]
			],
			'components' => [
				'componentsList' => UIFactory::getInstance()->getAllComponents()
			]
		];
	}
	
	public function getSectionData() {
		return $this->sectionData;
	}

	/**
	 * Returns data for section given as param
	 * 
	 * @param array $sectionNames
	 * 
	 * @return array
	 */
	public function getPartOfSectionData( $sectionNames ) {
		$results = [];
		$data = $this->getSectionData();
		$itterations = count( $sectionNames );
		
		foreach( $sectionNames as $subSection ) {
			if( $itterations === 1 ) {
				$results = isset( $data[$subSection] ) ? $data[$subSection] : [];
			} else {
				$data = isset( $data[$subSection] ) ? $data[$subSection] : [];
				$itterations--;
			}
		}
		
		return $results;
	}

	public function getStyleguidePageUrl( $subpage = false ) {
		$title = SpecialPage::getTitleFor( 'Styleguide', $subpage );
		
		return ( $title instanceof Title ) ? $title->getFullUrl() : '#';
	}
	
}
