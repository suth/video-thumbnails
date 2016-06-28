<?php
/**
 * @coversDefaultClass Video_Thumbnails_Match
 */
class Video_Thumbnails_Match_Test extends WP_UnitTestCase {
    /**
     * @test
     * @covers ::__construct
     * @covers ::get_image_url
     */
    public function it_can_get_an_image()
    {
        $match = new Video_Thumbnails_Match(
            'id_data',
            function( $data ) { return $data . '.jpg'; }
        );

        $this->assertEquals(
            'id_data.jpg',
            $match->get_image_url()
        );
    }
}

