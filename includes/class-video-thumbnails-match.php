<?php

class Video_Thumbnails_Match
{
    public $data;
    public $callback;
    public $offset;

    public function __construct( $data, $callback, $offset = null )
    {
        $this->data = $data;
        $this->callback = $callback;
        $this->offset = $offset;
    }

    public function get_image_url()
    {
        return call_user_func(
            $this->callback,
            $this->data
        );
    }
}
