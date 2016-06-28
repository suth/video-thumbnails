<?php
/**
 * @coversDefaultClass Video_Thumbnails_Meta
 */
class Video_Thumbnails_Meta_Test extends WP_UnitTestCase {

    private function invokePrivateMethod($className, $methodName, $args = array())
    {
		$reflector = new ReflectionClass($className);
		$method = $reflector->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs(new $className, $args);
    }

    /**
     * @test
     * @covers ::set
     * @covers ::get
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
     * @covers ::exists
     */
    public function it_has_an_exists_method()
    {
        $post_id = $this->factory->post->create();

        $this->assertFalse( Video_Thumbnails_Meta::exists( $post_id ) );

        Video_Thumbnails_Meta::set( $post_id, 33 );

        $this->assertTrue( Video_Thumbnails_Meta::exists( $post_id ) );
    }

    /**
     * @test
     * @covers ::getKey
     */
    public function it_has_a_getKey_method()
    {
        $this->assertEquals(
            '_video_thumbnail',
            $this->invokePrivateMethod('Video_Thumbnails_Meta', 'getKey')
        );
    }

    /**
     * @test
     * @covers ::getPostMeta
     */
    public function it_has_a_getPostMeta_method()
    {
        $post_id = $this->factory->post->create();

        $this->assertEquals(
            '',
            $this->invokePrivateMethod('Video_Thumbnails_Meta', 'getPostMeta', array($post_id))
        );

        Video_Thumbnails_Meta::set( $post_id, 'new_value' );

        $this->assertEquals(
            'new_value',
            $this->invokePrivateMethod('Video_Thumbnails_Meta', 'getPostMeta', array($post_id))
        );
    }
}

