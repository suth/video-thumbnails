<?php
/**
 * @coversDefaultClass Video_Thumbnails_Settings
 */
class Video_Thumbnails_Settings_Test extends WP_UnitTestCase {

    /**
     * @test
     * @covers ::get
     * @covers ::build_instance
     */
    public function it_gets_a_singleton()
    {
        $instance = Video_Thumbnails_Settings::get();

        $this->assertInstanceOf(
            'Refactored_Settings_0_5_0',
            $instance
        );
    }
}
