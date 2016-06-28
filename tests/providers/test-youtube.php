<?php
/**
 * @covers YouTube_Thumbnails
 */
class YouTube_Thumbnails_Test extends WP_UnitTestCase {

    private $provider;

    /** @before */
    public function setUpProvider()
    {
        $this->provider = new YouTube_Thumbnails(
            Video_Thumbnails_Settings::get()
        );

        $this->provider->init();
    }

    /**
     * @test
     */
    public function it_finds_matches()
    {
        foreach ($this->provider->get_tests() as $test) {
            $videos = $this->provider->parse($test['markup']);
            $this->assertEquals(
                $videos[0]->data,
                $test['expected']
            );
        }
    }

    /**
     * @test
     * @group external-http
     */
    public function it_finds_image_urls()
    {
        foreach ($this->provider->get_tests() as $test) {
            $videos = $this->provider->parse($test['markup']);
            $this->assertEquals(
                $videos[0]->get_image_url(),
                $test['image_url']
            );
        }
    }
}
