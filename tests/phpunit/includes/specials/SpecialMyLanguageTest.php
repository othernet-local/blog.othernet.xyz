<?php

/**
 * @group Database
 * @covers SpecialMyLanguage
 */
class SpecialMyLanguageTest extends MediaWikiTestCase {
	public function addDBDataOnce() {
		$titles = [
			'Page/Another',
			'Page/Another/ru',
		];
		foreach ( $titles as $title ) {
			$page = WikiPage::factory( Title::newFromText( $title ) );
			if ( $page->getId() == 0 ) {
				$page->doEditContent(
					new WikitextContent( 'UTContent' ),
					'UTPageSummary',
					EDIT_NEW,
					false,
					User::newFromName( 'UTSysop' ) );
			}
		}
	}

	/**
	 * @covers SpecialMyLanguage::findTitle
	 * @dataProvider provideFindTitle
	 * @param string $expected
	 * @param string $subpage
	 * @param string $langCode
	 * @param string $userLang
	 */
	public function testFindTitle( $expected, $subpage, $langCode, $userLang ) {
		$this->setMwGlobals( 'wgLanguageCode', $langCode );
		$special = new SpecialMyLanguage();
		$special->getContext()->setLanguage( $userLang );
		// Test with subpages both enabled and disabled
		$this->mergeMwGlobalArrayValue( 'wgNamespacesWithSubpages', [ NS_MAIN => true ] );
		$this->assertTitle( $expected, $special->findTitle( $subpage ) );
		$this->mergeMwGlobalArrayValue( 'wgNamespacesWithSubpages', [ NS_MAIN => false ] );
		$this->assertTitle( $expected, $special->findTitle( $subpage ) );
	}

	/**
	 * @param string $expected
	 * @param Title|null $title
	 */
	private function assertTitle( $expected, $title ) {
		if ( $title ) {
			$title = $title->getPrefixedText();
		}
		$this->assertEquals( $expected, $title );
	}

	public static function provideFindTitle() {
		return [
			[ null, '::Fail', 'en', 'en' ],
			[ 'Page/Another', 'Page/Another/en', 'en', 'en' ],
			[ 'Page/Another', 'Page/Another', 'en', 'en' ],
			[ 'Page/Another/ru', 'Page/Another', 'en', 'ru' ],
			[ 'Page/Another', 'Page/Another', 'en', 'es' ],
		];
	}
}
