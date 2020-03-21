<?php

namespace Nghh\Lib\Wordpress\Models\PostType;

class Model
{
    protected $post_type;
    protected $query;

    public function __construct($post_type)
    {
        $this->post_type = $post_type;
    }

    public function getPosts($args = [])
    {
        $default_args = [
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1
        ];

        $args = wp_parse_args($args, $default_args);

        $this->query = new \WP_Query($args);
    }

    public function havePosts()
    {
        return $this->query->have_posts();
    }

    public function thePost()
    {
        $this->query->the_post();
        return $this->query->post;
    }
}
