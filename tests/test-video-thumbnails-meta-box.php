<?php
/**
 * @coversDefaultClass Video_Thumbnails_Meta_Box
 */
class Video_Thumbnails_Meta_Box_Test extends WP_UnitTestCase {

    private function newMetaBox()
    {
        return new Video_Thumbnails_Meta_Box('plugin-name', '1.0', Video_Thumbnails_Settings::get());
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function it_has_a_constructor()
    {
        $meta_box = $this->newMetaBox();

        $this->assertInstanceOf('Video_Thumbnails_Meta_Box', $meta_box);
    }

    /**
     * @test
     * @covers ::register
     */
    public function it_has_a_register_method()
    {
        global $wp_meta_boxes;

        $obj = $this->getMockBuilder('Video_Thumbnails_Meta_Box')
            ->setConstructorArgs(array('plugin-name', '1.0', Video_Thumbnails_Settings::get()))
            ->setMethods(array('get_post_types'))
            ->getMock();

        $obj->expects($this->once())
            ->method('get_post_types')
            ->willReturn(array('posts'));

        $obj->register();

        $expected = array(
            'id' => 'plugin-name',
            'title' => 'Video Thumbnail',
            'callback' => array(
                $obj,
                'render'
            ),
            'args' => null
        );

        $this->assertEquals(
            $expected,
            $wp_meta_boxes['posts']['side']['low']['plugin-name']
        );
    }

    /**
     * @test
     * @covers ::get_post_types
     */
    public function it_has_a_get_post_types_method()
    {
        $meta_box = $this->newMetaBox();

        $this->assertEquals(
            array('post'),
            $meta_box->get_post_types()
        );
    }
}
