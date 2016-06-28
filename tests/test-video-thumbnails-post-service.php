<?php
/**
 * @coversDefaultClass Video_Thumbnails_Post_Service
 */
class Video_Thumbnails_Post_Service_Test extends WP_UnitTestCase {

    private $service;

    /** @before */
    public function setUpService()
    {
        $providers = $this->getMockBuilder('Video_Thumbnails_Providers')
            ->setMethods(array('parse'))
            ->setConstructorArgs(array('video-thumbnails', '3.0', Video_Thumbnails_Settings::get()))
            ->getMock();

        $providers->method('parse')
            ->willReturn(array());

        $this->service = new Video_Thumbnails_Post_Service(
            'video-thumbnails',
            '3.0',
            Video_Thumbnails_Settings::get(),
            $providers
        );
    }

    /**
     * @test
     * @covers ::should_skip_post
     * @covers ::is_autosaving
     * @covers ::is_not_a_selected_post_type
     */
    public function it_skips_posts_when_appropriate()
    {
        $post_id = $this->factory->post->create(array('post_type' => 'custom'));

        $this->assertNull(
            $this->service->save_post($post_id)
        );

        $post_id = $this->factory->post->create();
        Video_Thumbnails_Meta::set($post_id, 'image.jpg');

        $this->assertNull(
            $this->service->save_post($post_id)
        );
    }
}
