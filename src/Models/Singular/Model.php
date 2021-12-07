<?php

namespace Nghh\Lib\Wordpress\Models\Singular;

use Nghh\Theme\Utils\Author;
use Nghh\Lib\Wordpress\Image;

use function Nghh\Lib\Wordpress\func\date;

class Model
{

    public $image;
    protected $post;
    protected $author;
    protected $opengraph_defaults = [
        'title' => '',
        'description' => '',
        'image' => '',
        'url' => '',
        'type' => ''
    ];

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    public function title($words = null)
    {
        $title = apply_filters('the_title', $this->post->post_title, $this->post->ID);

        if ($words) {
            return wp_trim_words($title, $words);
        }

        return $title;
    }

    public function id()
    {
        return $this->post->ID;
    }

    public function slug()
    {
        return $this->post->post_name;
    }

    public function date()
    {
        return date($this->post->post_date);
    }

    // TODO: Default Excerpt length to config file
    public function excerpt($length = 10, $strip_tags = false)
    {
        $excerpt = ($this->post->post_excerpt) ? $this->post->post_excerpt : $this->post->post_content;
        $excerpt = wp_trim_words($excerpt, $length, '…');
        $excerpt = ($strip_tags) ? strip_tags($excerpt) : $excerpt;

        return apply_filters('ng_post_excerpt', $excerpt);
    }

    public function content()
    {
        // dd($this->sponsored());
        // Must run through the_content filter so dynamic blocks will be rendered correctly
        return apply_filters('the_content', $this->post->post_content);
    }

    public function permalink()
    {
        return get_the_permalink($this->post);
    }

    public function image()
    {
        if (is_null($this->image)) {
            if (0 === ($image_id = get_post_thumbnail_id($this->post))) {
                return $this->image = false;
            }
            $this->image = new Image($image_id);
        }

        return $this->image;
    }

    public function author()
    {
        if (is_null($this->author)) {
            return $this->author = new Author((int) $this->post->post_author);
        }

        return $this->author;
    }

    /**
     * Returns the post_type string or boolean if post_type is passed to function
     *
     * @param string $post_type
     * @return void
     */
    public function postType(string $post_type = '')
    {
        if ($post_type) {
            return ($this->post->post_type == $post_type);
        }

        return $this->post->post_type;
    }
}
