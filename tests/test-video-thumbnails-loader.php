<?php
/**
 * @coversDefaultClass Video_Thumbnails_Loader
 */
class Video_Thumbnails_Loader_Test extends WP_UnitTestCase {

    /**
     * @test
     * @covers ::__construct
     */
    public function it_has_a_constructor()
    {
        $loader = new Video_Thumbnails_Loader();

        $this->assertInstanceOf('Video_Thumbnails_Loader', $loader);
    }

    /**
     * @test
     * @covers ::add_action
     * @covers ::add
     */
    public function it_has_an_add_action_method()
    {
        $loader = new Video_Thumbnails_Loader();

        $loader->add_action('init', 'ComponentObject', 'callback_method');

        $expected = array(array(
            'hook' => 'init',
            'component' => 'ComponentObject',
            'callback' => 'callback_method',
            'priority' => 10,
            'accepted_args' => 1
        ));

        $this->assertAttributeEquals(
            $expected,
            'actions',
            $loader
        );
    }

    /**
     * @test
     * @covers ::add_filter
     * @covers ::add
     */
    public function it_has_an_add_filter_method()
    {
        $loader = new Video_Thumbnails_Loader();

        $loader->add_filter('title', 'ComponentObject', 'callback_method');

        $expected = array(array(
            'hook' => 'title',
            'component' => 'ComponentObject',
            'callback' => 'callback_method',
            'priority' => 10,
            'accepted_args' => 1
        ));

        $this->assertAttributeEquals(
            $expected,
            'filters',
            $loader
        );
    }

    /**
     * @test
     * @covers ::run
     */
    public function it_has_a_run_method()
    {
        global $wp_filter;

        $loader = new Video_Thumbnails_Loader();

        $loader->add_action('init', 'ComponentObject', 'callback_method');
        $loader->add_filter('wp_title', 'ComponentObject', 'callback_method');

        $loader->run();

        $expected = array(
            'function' => array(
                'ComponentObject',
                'callback_method'
            ),
            'accepted_args' => 1
        );

        $this->assertContains(
            $expected,
            $wp_filter['init'][10]
        );

        $this->assertContains(
            $expected,
            $wp_filter['wp_title'][10]
        );
    }
}
