<?php
/**
 * @covers CollegeHumor_Thumbnails
 * @covers Dailymotion_Thumbnails
 * @covers Facebook_Thumbnails
 * @covers FunnyOrDie_Thumbnails
 * @covers GoogleDrive_Thumbnails
 * @covers Livestream_Thumbnails
 * @covers Metacafe_Thumbnails
 * @covers Mpora_Thumbnails
 * @covers Rutube_Thumbnails
 * @covers Sapo_Thumbnails
 * @covers Ted_Thumbnails
 * @covers Tudou_Thumbnails
 * @covers Twitch_Thumbnails
 * @covers Vimeo_Thumbnails
 * @covers Vine_Thumbnails
 * @covers Vk_Thumbnails
 * @covers Wistia_Thumbnails
 * @covers Youku_Thumbnails
 * @covers YouTube_Thumbnails
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
     */
    public function it_parses_content_and_returns_an_array()
    {
        $results = $this->providers->parse( 'No video content.' );

        $this->assertEquals( array(), $results );
    }

    /**
     * @test
     */
    public function providers_all_parse_content()
    {
        foreach ($this->providers->get_providers() as $provider) {
            $this->assertProviderMatches($provider);
        }
    }

    public function assertProviderMatches($provider) {
        foreach ($provider->get_tests() as $test) {
            $videos = $provider->parse($test['markup']);
            $this->assertEquals(
                $test['expected'],
                $videos[0]->data,
                'Failed to correctly parse ' . $provider->name . ': ' . $test['name']
            );
        }
    }

    /**
     * @test
     * @ignore
     * @group external-http
     */
    public function providers_all_fetch_image_urls()
    {
        foreach ($this->providers->get_providers() as $provider) {
            $this->assertProviderFetches($provider);
        }
    }

    public function assertProviderFetches($provider) {
        foreach ($provider->get_tests() as $test) {
            $url = $provider->get_thumbnail_url($test['expected']);
            var_dump($url);
            $this->assertEquals(
                $test['image_hash'],
                $this->getFileHash( $url ),
                'Failed to correctly fetch ' . $provider->name . ' video: ' . $test['expected']
            );
        }
    }

    public function getFileHash( $url ) {
		$response = wp_remote_get( $url );
		if( is_wp_error( $response ) ) {
			$result = false;
		} else {
			$result = md5( $response['body'] );
		}
		return $result;
	}
}
