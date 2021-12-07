<?php

namespace Nghh\Lib\Wordpress;

class Templates
{
    private $_templates;

    public function __construct(array $config)
    {
        $this->_templates = $config;
    }

    public function registerTemplates($templates, $theme, $post)
    {
        if (!$post) return $templates;

        if (!isset($this->_templates[$post->post_type])) return $templates;

        return array_merge($templates, $this->_templates[$post->post_type]);
    }

    public function registerHooks()
    {
        foreach ($this->_templates as $post_type => $template) {
            if (empty($template)) continue;
            add_filter('theme_' . $post_type . '_templates', [$this, 'registerTemplates'], 10, 3);
        }
    }
}
