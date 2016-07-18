<?php
/**
 * @covers Video_Thumbnails_Meta
 */
class Video_Thumbnails_Meta_Test extends WP_UnitTestCase {
    /**
     * @test
     */
    public function it_can_set_and_get_meta()
    {
        $post_id = $this->factory->post->create();

        $this->assertFalse( !! Video_Thumbnails_Meta::get( $post_id ) );

        $this->assertTrue( !! Video_Thumbnails_Meta::set( $post_id, 33 ) );

        $this->assertEquals( 33, Video_Thumbnails_Meta::get( $post_id ) );
    }

    /**
     * @test
     */
    public function it_has_an_exists_method()
    {
        $post_id = $this->factory->post->create();

        $this->assertFalse( Video_Thumbnails_Meta::exists( $post_id ) );

        Video_Thumbnails_Meta::set( $post_id, 33 );

        $this->assertTrue( Video_Thumbnails_Meta::exists( $post_id ) );
    }
}

