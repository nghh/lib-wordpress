<?php

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Helper\func\dot_reader;

class Image extends Media
{
    public function __construct($image)
    {
        // Get Post
        if (is_numeric($image) && $image !== 0) {
            if (!$post = get_post($image)) {
                return false;
            }
        } elseif ($image instanceof \WP_Post) {
            $post = $image;
        } else {
            return false;
        }

        parent::__construct($post);

        // Set Properties
        $this->_setImageMeta();
        $this->_setImageSizes();
    }

    public function ratio(string $size)
    {
        if (isset($this->_meta['sizes'][$size])) {
            $width = $this->_meta['sizes'][$size]['width'];
            $height = $this->_meta['sizes'][$size]['height'];

            if ($width > $height) {
                return 'landscape';
            } elseif ($height > $width) {
                return 'portrait';
            } else {
                return 'square';
            }
        }
    }

    public function get(string $key)
    {
        // if (isset($this->_meta[$key])) {
        //     return $this->_meta[$key];
        // }

        return dot_reader($key, $this->_meta);
    }

    public function url($size = 'thumbnail')
    {

        if (isset($this->_meta['sizes'][$size]['url'])) {
            return $this->_meta['sizes'][$size]['url'];
        }

        return '';
    }

    public function alt()
    {
        return $this->get('alt');
    }

    public function title()
    {
        return $this->get('title');
    }

    public function copyright()
    {
        return $this->get('copyright');
    }

    public function caption()
    {
        return $this->get('caption');
    }

    private function _setImageSizes()
    {
        $sizes = get_post_meta($this->_post->ID, '_wp_attachment_metadata', true);
        $upload_dir = dirname($sizes['file']);
        $image_name = wp_basename($sizes['file']);

        $uploads = wp_get_upload_dir();
        $uploads_path = $uploads['basedir'] . '/' . $upload_dir . '/';
        $uploads_url = $uploads['baseurl'] . '/' . $upload_dir . '/';



        $this->_meta['sizes']['full']['url']       = $uploads_url . $image_name;
        $this->_meta['sizes']['full']['path']      = $uploads_path . $image_name;
        $this->_meta['sizes']['full']['width']     = $sizes['width'];
        $this->_meta['sizes']['full']['height']    = $sizes['height'];

        // Additional Sizes
        foreach ($sizes['sizes'] as $name => $size) {
            $this->_meta['sizes'][$name]['url']       = $uploads_url . $size['file'];
            $this->_meta['sizes'][$name]['path']      = $uploads_path . $size['file'];
            $this->_meta['sizes'][$name]['width']     = $size['width'];
            $this->_meta['sizes'][$name]['height']    = $size['height'];
        }
    }

    private function _setImageMeta()
    {
        $meta_default = [
            'id'            => '',
            'alt'           => '',
            'title'         => '',
            'caption'       => '',
            'description'   => '',
            'copyright'     => '',
            'mime'          => '',
            'sizes'         => [],
        ];


        $meta = [];
        $meta['id']             = $this->_post->ID;
        $meta['title']          = $this->_post->post_title;
        $meta['alt']            = get_post_meta( $this->_post->ID, '_wp_attachment_image_alt', true );
        $meta['caption']        = $this->_post->post_excerpt;
        $meta['description']    = $this->_post->post_content;
        $meta['copyright']      = isset($this->_post->copyright) ? $this->_post->copyright : '';
        $meta['mime']           = $this->_post->post_mime_type;
        
        // Use caption if no alt supplied
        $meta['alt'] = ($meta['alt']) ?: $meta['caption'];
        // Use title if no caption supplied
        $meta['alt'] = ($meta['alt']) ?: $meta['title'];
        
        // Sanitize the alt and title tag:  remove hyphens, underscores & extra spaces:
		$meta['alt'] = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $meta['alt'] );
		$meta['title'] = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $meta['title'] );

        // Add Copyright
        $meta['alt'] .= ($meta['copyright']) ? ' – ©' . $meta['copyright'] : '';
        

        $this->_meta = wp_parse_args($meta, $meta_default);
    }
}
