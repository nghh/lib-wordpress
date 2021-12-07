<?php

namespace Nghh\Lib\Wordpress\Models;

use Nghh\Lib\Wordpress\Image;
use Nghh\Theme\Utils\Author;

class Singular
{
    // If Post can't be found
    public $has_post = true;

    /**
     * Stores post data
     *
     * @var array
     */
    protected $data = [
        'id'                => 0,
        'name'              => '',
        'slug'              => '',
        'date_created'      => null,
        'date_modified'     => null,
        'status'            => false,
        'permalink'         => '',
        'content'           => null,
        'excerpt'           => null,
        'parent_id'         => 0,
        'menu_order'        => 0,
        'post_password'     => '',
        'category_ids'      => [],
        'tag_ids'           => [],
        'image_id'          => '',
        'author'            => 0
    ];
    protected $post;
    protected $author;

    public function __construct($post)
    {
        if (is_numeric($post) && $post > 0) {
            // Set from Post ID
            $this->post = \WP_Post::get_instance($post);
        } elseif ($post instanceof \WP_Post) {
            // Set passed Post Object
            $this->post = $post;
        } elseif (
            ($post === null && isset($GLOBALS['post'])) &&
            ($GLOBALS['post']->post_type == $this->post_type) &&
            ($GLOBALS['post'] instanceof \WP_Post)
        ) {
            // Set Global Post Object
            $this->post = $GLOBALS['post'];
        }

        // No Post found
        if (!$this->post) {
            return $this->has_post = false;
        } else {
            $this->_setData();
            return true;
        }
    }

    public function getID()
    {
        return $this->_getProp('ID');
    }

    public function getTitle()
    {
        return $this->_getProp('title');
    }
    public function getKicker()
    {
        return $this->_getProp('kicker');
    }
    public function getAddress()
    {
        return $this->_getProp('address');
    }
    public function getLat()
    {
        return $this->_getProp('lat');
    }
    public function getLong()
    {
        return $this->_getProp('long');
    }
    public function getMail()
    {
        return $this->_getProp('email');
    }
    public function getFon()
    {
        return $this->_getProp('fon');
    }
    public function openingHours()
    {
        return $this->_getProp('opening_hours');
    }
    public function getLinks()
    {
        return $this->_getProp('opening_hours');
    }

    public function getSlug()
    {
        return $this->_getProp('slug');
    }

    public function getPermalink()
    {
        return $this->_getProp('permalink');
    }

    public function getDateCreated()
    {
        return $this->_getProp('date_created');
    }

    public function getDateModified()
    {
        return $this->_getProp('date_modified');
    }

    public function getStatus()
    {
        return $this->_getProp('status');
    }

    public function getExcerpt($length = 10, $strip_tags = false)
    {
        // Get the Excerpt
        if (is_null($this->_getProp('excerpt'))) {
            $this->_setProp('excerpt', get_the_excerpt($this->post));
        }

        // Prepare Excerpt
        $excerpt = $this->_getProp('excerpt') ?: $this->_getProp('content');
        $excerpt = wp_trim_words($excerpt, $length, '…');
        $excerpt = ($strip_tags) ? strip_tags($excerpt) : $excerpt;

        return $excerpt;
    }

    public function getContent($more_link_text = null, $strip_teaser = false)
    {
        // Get the Content
        if (is_null($this->_getProp('content'))) {
            $this->_setProp('content', get_the_content($more_link_text, $strip_teaser, $this->post));
        }

        return $this->_getProp('content');
    }

    public function getImage()
    {
        return new Image($this->_getProp('image_id'));
    }

    public function getAuthor()
    {
        return new Author($this->_getProp('author'));
    }

    protected function _setProp($prop, $value)
    {
        $this->data[$prop] = $value;
    }

    protected function _getProp($prop)
    {
        $value = null;

        if (array_key_exists($prop, $this->custom_data)) {
            $value = $this->custom_data[$prop];
        } elseif(array_key_exists($prop, $this->data)) {
            $value = $this->data[$prop];
        }

        return $value;
    }

    private function _setData()
    {
        $this->data = [
            'id'                 => $this->post->ID,
            'title'              => get_the_title($this->post),
            'slug'               => $this->post->post_name,
            'date_created'       => \Nghh\Lib\Wordpress\func\date($this->post->post_date),
            'date_modified'      => \Nghh\Lib\Wordpress\func\date($this->post->post_modified),
            'status'             => $this->post->post_status,
            'permalink'          => get_permalink($this->post),
            'content'            => null, // will be set, when calling get_content method
            'excerpt'            => null, // will be set, when calling get_excerpt method
            'parent_id'          => $this->post->post_parent,
            'menu_order'         => $this->post->menu_order,
            'post_password'      => $this->post->post_password,
            'category_ids'       => [],
            'tag_ids'            => [],
            'image_id'           => get_post_thumbnail_id($this->post),
            'author'             => $this->post->post_author
        ];
    }
}
