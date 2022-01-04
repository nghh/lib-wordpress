<?php

namespace Nghh\Lib\Wordpress\Models;

class Archive
{

    private $_term;

    protected function __construct($wp_term)
    {
        $this->_term = get_term($wp_term);
    }

    public function getTitle($truncate = false): string
    {
    
        return 'title';
    }

    public function getSlug(): string
    {
        return 'slug';
    }

    public function getPermalink(): string
    {
        return 'link';
    }
}
