<?php

namespace Nghh\Lib\Wordpress\Models\Singular;

class Model
{

    protected $metaboxes;
    protected $post;
    protected $post_meta;
    protected $author;
    protected $post_thumbnail;
    protected $opengraph_defaults = [];

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
        // return __date($this->post->post_date);
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
        // Must run through the_content filter so dynamic blocks will be rendered
        return apply_filters('the_content', $this->post->post_content);
    }

    public function imageurl($size = 'thumbnail')
    {
        if (!$url = get_the_post_thumbnail_url($this->post, $size)) {
            $url = 'https://picsum.photos/id/' . rand(1, 200) . '/1200/1200';
        }
        return $url;
    }

    public function permalink()
    {
        return get_the_permalink($this->post);
    }

    public function getPostThumbnail(string $size = 'thumnail')
    {
        $test = 0;
    }

    public function image(string $string = '')
    {
        /**
         * $article->image() -> to retrieve the full Media Object
         * $article->image(id) -> to get the id -> id, title, caption, description, mime, alt, copyright 
         * $article->image(sizes.medium) -> to get image array [url, width, height]
         */

        // Get Infos about the post thumbnail
        if (is_null($this->post_thumbnail)) {
            // Get Image ID
            $image_id = get_post_thumbnail_id($this->post->ID);
            // Construct Media Object
            // $this->post_thumbnail = new Media($image_id);
        }

        return ($string) ? $this->post_thumbnail->get($string) : $this->post_thumbnail;
    }

    public function postMeta($key)
    {
        return $this->post_meta[$key];
    }

    public function attachmentURL($id)
    {
        return wp_get_attachment_url($id);
    }

    public function attachmentType($id)
    {
        return get_post_mime_type($id);
    }

    /**
     * Returns Fields from ACF
     *
     * @param string $field
     * @return void
     */
    public function getField(string $field)
    {
        if (function_exists('get_field')) {
            return get_field($field);
        }

        return false;
    }

    public function getAuthor()
    {

        // if (is_null($this->author)) {
        //     return $this->author = new Author((int) $this->post->post_author);
        // }

        // return $this->_author;
    }
}
