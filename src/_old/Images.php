<?php

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Utils\Func\logger;

class Images
{
    private $compression;
    private $max_width;
    private $max_height;
    private $_config;
    private $_image_names = [];

    public function __construct(array $config)
    {
        // Get Config
        $this->_config = $config;

        // Set properties
        $this->compression  = $this->_config['setup']['compression'];
        $this->max_width    = $this->_config['setup']['max_width'];
        $this->max_height   = $this->_config['setup']['max_height'];
    }

    public function registerHooks()
    {   
        // Resize Image after Upload
        add_filter('wp_handle_upload', [$this, 'handleImageUpload'], 10, 2);
        // Sanitize Filename
        add_filter('sanitize_file_name', [$this, 'sanitizeFilename'], 10);
        // Jpg Compression
        add_filter('jpeg_quality', [$this, 'jpgCompression']);
        // Add Image Sizes
        add_action('after_setup_theme', [$this, 'addImageSizes']);
        // Add Image Sizes to Media Library
        add_filter('image_size_names_choose', [$this, 'addImageSizesToMediaLibrary']);
        // Automatically set the image Title, Alt-Text
        add_action( 'add_attachment', [$this, 'setImageMetaUponImageUpload'] );
    }

    public function setImageMetaUponImageUpload($post_id)
    {
        // Check if uploaded file is not an image, exit
        if ( !wp_attachment_is_image( $post_id ) ) return;

        $image_title = get_post( $post_id )->post_title;

		// Sanitize the title:  remove hyphens, underscores & extra spaces:
		$image_title = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $image_title );

		// Sanitize the title:  capitalize first letter of every word (other letters lower case):
		$image_title = ucwords( strtolower( $image_title ) );

		// Create an array with the image meta (Title, Caption, Description) to be updated
		// Note:  comment out the Excerpt/Caption or Content/Description lines if not needed
		$image_meta = array(
			'ID'		        => $post_id,			// Specify the image (ID) to be updated
			'post_title'	    => $image_title,		// Set image Title to sanitized title
			// 'post_excerpt'	=> $image_title,		// Set image Caption (Excerpt) to sanitized title (BESCHRIFTUNG)
			// 'post_content'	=> $image_title,		// Set image Description (Content) to sanitized title (BESCHREIBUNG)
		);

		// Set the image Alt-Text
		update_post_meta( $post_id, '_wp_attachment_image_alt', $image_title );

		// Set the image meta (e.g. Title, Excerpt, Content)
		wp_update_post( $image_meta );


    }
    /**
     * Undocumented function
     *
     * @return void
     */
    public function addImageSizes()
    {
        // Add Image Sizes
        foreach ($this->_config['image_sizes'] as $image) {
            add_image_size(
                $image['slug'],
                $image['width'],
                $image['height'],
                $image['crop']
            );
            // Add Image Name and Slug to Array (used to add to Media Library)
            $this->_image_names[$image['slug']] = $image['name'];
        }
    }

    public function addImageSizesToMediaLibrary($sizes)
    {
        return array_merge($sizes, $this->_image_names);
    }

    public function jpgCompression($arg)
    {
        return $this->compression;
    }

    /**
     * Undocumented function
     *
     * @param [array] $image_data file, url, type
     * @param [string] $context upload/sideload
     * @return void
     */
    public function handleImageUpload($image_data, $context)
    {
        // Exit if file is uploaded via Wordpress Importer
        if ($context === 'sideload')
            return $image_data;

        // Get out without file type
        if (empty($image_data['type']))
            return $image_data;

        // Handle Mime Type
        switch ($image_data['type']) {
            case 'image/jpg':
            case 'image/jpeg':
                return $this->_handleJPG($image_data);
                break;

            case 'image/png':
                return $this->_handlePNG($image_data);
                break;

            case 'image/svg+xml':
                return $this->_handleSVG($image_data);
                break;

            default:
                return $image_data;
                break;
        }
    }

    private function _handleJPG($image_data, $compression = null)
    {
        // Create Image Editor
        if (!$image = $this->_getImageEditor($image_data)) {
            return $image_data;
        }

        // Set Compression
        $this->compression = $compression ?: $this->_config['setup']['compression'];

        // Get Image Sizes
        $sizes = $image->get_size();

        // Check if we need to resize image
        if (
            (isset($sizes['width']) && $sizes['width'] > $this->max_width) || (isset($sizes['height']) && $sizes['height'] > $this->max_height)
        ) {
            $image->resize($this->max_width, $this->max_height, false);
        } else {
            return $image_data;
        }

        // Set Compression
        $image->set_quality($this->compression);

        // Save Image
        $saved_image = $image->save($image_data['file']);

        return $image_data;
    }

    private function _handlePNG($image_data)
    {

        // if (!$this->_isAlphaPNG($image_data['file'])) {
        //     // Set Compression to min 90 for PNG to JPG
        //     $this->compression = 95;
        //     $image_data = $this->_convertPNGtoJPG($image_data);

        //     // Handle as JPG
        //     return $this->_handleJPG($image_data, 95);
        // }

        // Create Image Editor
        if (!$image = $this->_getImageEditor($image_data)) {
            return $image_data;
        }

        // Get Image Sizes
        $sizes = $image->get_size();

        // Check if we need to resize image
        if (
            (isset($sizes['width']) && $sizes['width'] > $this->max_width) || (isset($sizes['height']) && $sizes['height'] > $this->max_height)
        ) {
            $image->resize($this->max_width, $this->max_height, false);
        } else {
            return $image_data;
        }

        // Save Image
        $saved_image = $image->save($image_data['file']);

        return $image_data;
    }

    private function _handleSVG($image_data)
    {
        if (!class_exists('enshrined\\svgSanitize\\Sanitizer')) {
            logger('theme')->warn('enshrined\svgSanitize\Sanitizer not available');
            return $image_data;
        }

        // Create a new sanitizer instance
        $sanitizer = new \enshrined\svgSanitize\Sanitizer();
        $sanitizer->minify(true);

        // Load the dirty svg
        $dirtySVG = file_get_contents($image_data['file']);

        // Pass it to the sanitizer and get it back clean
        $cleanSVG = $sanitizer->sanitize($dirtySVG);

        // Write to file
        file_put_contents($image_data['file'], $cleanSVG);

        return $image_data;
    }

    private function _getImageEditor($image_data)
    {
        // Create Image Editor
        $image = wp_get_image_editor($image_data['file']);

        // Return on error
        if (is_wp_error($image)) {
            // Debug::log('Error while wp_get_image_editor()');
            // var_dump($image);
            return false;
        }

        return $image;
    }

    public function sanitizeFilename($filename)
    {
        $file = pathinfo($filename);
        $name = $file['filename'];

        $name = remove_accents($name);
        $name = sanitize_title($name);

        // Special chars äüö
        $name = str_replace('a%cc%88', 'ae', $name);
        $name = str_replace('u%cc%88', 'ue', $name);
        $name = str_replace('o%cc%88', 'oe', $name);

        // Underscore to dashes
        $name = str_replace('_', '-', $name);

        // all lowercase
        $name = strtolower($name);

        return $name . '.' . $file['extension'];
    }

    private function _convertPNGtoJPG($image_data)
    {
        $image = imagecreatefrompng($image_data['file']);
        $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagealphablending($bg, TRUE);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        // Get new URL and Path
        $newPath = preg_replace("/\.png$/", ".jpg", $image_data['file']);
        $newUrl = preg_replace("/\.png$/", ".jpg", $image_data['url']);

        // Dont know what this should be good for?
        for ($i = 1; file_exists($newPath); $i++) {
            $newPath = preg_replace("/\.png$/", "-" . $i . ".jpg", $image_data['file']);
        }

        // Destroy created Image
        imagedestroy($image);

        if (imagejpeg($bg, $newPath, $this->compression)) {
            unlink($image_data['file']);
            $image_data['file'] = $newPath;
            $image_data['url'] = $newUrl;
            $image_data['type'] = 'image/jpeg';
        }

        // Destroy created BG
        imagedestroy($bg);

        return $image_data;
    }

    private function _isAlphaPNG($image)
    {


        if (!(ord(@file_get_contents($image, NULL, NULL, 25, 1)) == 6)) {
            return false;
        }

        // run through pixels until transparent pixel is found:
        $has_transparent_pixel = false;
        $img = imagecreatefrompng($image);
        $w = imagesx($img); // Get the width of the image
        $h = imagesy($img); // Get the height of the image
        //run through pixels until transparent pixel is found:
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $rgba = imagecolorat($img, $i, $j);
                if (($rgba & 0x7F000000) >> 24) {
                    $has_transparent_pixel = true;
                    break;
                }
            }
        }

        return $has_transparent_pixel;
    }
}
