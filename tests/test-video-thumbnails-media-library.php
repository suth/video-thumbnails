<?php
/**
 * @covers Video_Thumbnails_Media_Library
 */
class Video_Thumbnails_Media_Library_Test extends WP_UnitTestCase {
    private function invokePrivateMethod($methodName, $args = array())
    {
		$reflector = new ReflectionClass('Video_Thumbnails_Media_Library');
		$method = $reflector->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs(new Video_Thumbnails_Media_Library, $args);
    }

    /**
     * @test
     */
    public function it_can_get_extension_for_image_types()
    {
        $types = array(
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif'
        );

        foreach ($types as $type => $extension) {
            $this->assertEquals(
                $extension,
                $this->invokePrivateMethod('get_extension_for_content_type', array($type))
            );
        }

        $this->assertWPError(
            $this->invokePrivateMethod('get_extension_for_content_type', array('application/json'))
        );
    }

    /** @test */
    public function it_can_generate_a_file_name_for_a_post()
    {
        $post = $this->factory->post->create( array( 'post_title' => 'This is a Sample' ) );

        $this->assertEquals(
            'this-is-a-sample.jpg',
            $this->invokePrivateMethod('construct_filename', array($post, '.jpg'))
        );
    }
}

