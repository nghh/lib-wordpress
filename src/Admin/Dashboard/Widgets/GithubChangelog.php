<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Dashboard\Widgets;

use function Nghh\Theme\func\view;

class GithubChangelog extends WPDashboardWidget
{
    private $_args;

    public function __construct($args)
    {
        $this->_args = wp_parse_args($args, $this->default_args);

        // Add Admin Styles and Scripts
        $this->styles[] = 'github-changelog.css';
    }

    public function renderDashboardWidget()
    {
        // Check File
        if (!file_exists($this->_args['file'])) {
            echo '<p>No Git Changelog File found</p>';
            return;
        }

        // Read Log File
        $entries = $this->_readLogFile() ?: [];
        // Prepare Data
        $data = [
            'entries' => $entries
        ];

        // Render
        echo view('admin.dashboard.github-changelog', $data);
    }

    public function registerDashboardWidget()
    {
        // Add Dashboard Widget
        add_meta_box(
            $this->_args['slug'],
            $this->_args['title'],
            [$this, 'renderDashboardWidget'],
            'dashboard',
            $this->_args['context'],
            $this->_args['priority'],
        );
    }

    public function registerHooks()
    {
        // Hook into the Dashboard
        add_action('wp_dashboard_setup', [$this, 'registerDashboardWidget']);

        // Register WPDashboardWidget Hooks
        $this->registerStylesAndScripts();
    }

    private function _readLogFile()
    {
        // Output
        $output = [];

        // Read File
        $entries = file($this->_args['file'], FILE_SKIP_EMPTY_LINES);

        // Reverse entries – newest comes first
        $entries = array_reverse($entries);

        // Return if no entries
        if (empty($entries)) return null;

        // Loop Logfile line by line
        foreach ($entries as $entry) {

            // process the line read.
            $row = explode('___', $entry); // [0] = Author, [1] = Message, [2] = Date/Timestamp

            // Skip if Message is empty
            if (!isset($row[1]) || !$row[1]) continue;

            $output[] = [
                'author' => esc_attr($row[0]),
                'message' => explode(';', trim(wp_kses_post($row[1]))),
                'date' => esc_html(date("d. F Y @ H:i", (int) $row[2])),
            ];
        }

        return $output;
    }
}
