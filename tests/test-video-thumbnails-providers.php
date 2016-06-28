<?php
/**
 * @coversDefaultClass Video_Thumbnails_Providers
 */
class Video_Thumbnails_Provider_Test extends WP_UnitTestCase {

    private $parser;

    /** @before */
    public function setUpProviders()
    {
        $this->providers = new Video_Thumbnails_Providers(
            'video-thumbnails',
            '3.0',
            Video_Thumbnails_Settings::get()
        );

        $this->providers->init();
    }

    /**
     * @test
     * @covers ::parse
     */
    public function it_parses_content_and_returns_an_array()
    {
        $results = $this->providers->parse( 'No video content.' );

        $this->assertEquals( array(), $results );
    }
}
