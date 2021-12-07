<?php

namespace Nghh\Lib\Wordpress;

class GithubDashboard
{
    private $_config;
    private $_client;

    public function __construct(array $config)
    {
        // Get Config
        $this->_config = $config;

        // Requirements
        if (!class_exists('\\Nghh\\Lib\\Apis\\Github')) {
            die('composer require nghh/github-api');
        }

        // Establish connection to GitHub
        $this->_connect();
    }

    private function _connect()
    {
        // Create HTTP Client
        $httpBuilder = new \Github\HttpClient\Builder(new \Http\Adapter\Guzzle6\Client());
        // Connect to Github
        $this->_client = new \Github\Client($httpBuilder);
        $this->_client->authenticate($this->_config['github_token'], null, \Github\Client::AUTH_URL_TOKEN);
    }

    public function addDashboardWidget()
    {
        add_meta_box(
            'ng_metabox_githubissues',          // Widget slug.
            'Website Tickets',                  // Title.
            [$this, 'renderDashboardWidget'],   // Display function.,
            'dashboard',
            'side',  // $context: 'advanced', 'normal', 'side', 'column3', 'column4'
            'high', // $priority: 'high', 'core', 'default', 'low'
        );
    }

    private function getIssues()
    {
        // Get Open Issues from GitHub
        $issues_raw = $this->_client->api('issue')->all(
            $this->_config['github_user'],
            $this->_config['github_repo'],
            ['state' => 'open']
        );

        $issues = [];
        // Loop Issues
        foreach ($issues_raw as $issue) {
            // Comments
            $comments = ($issue['comments'] > 0) ? $this->getComments($issue['number']) : null;
            // Get Body and tranform to HTML
            $body_html = \Michelf\Markdown::defaultTransform($issue['body']);
            // Milestone
            $milestone = [];
            if (isset($issue['milestone']['title'])) {
                $milestone['version'] = $issue['milestone']['title'];
                $milestone['due'] = ($issue['milestone']['due_on']) ?: null;
            }
            // Title
            $title = trim(substr($issue['title'], 4));
            // City
            $city = substr($issue['title'], 0, 4);
            // Set Data
            $issues[] = [
                'number'        => $issue['number'],
                'title'         => $title,
                'city'          => $city,
                'user'          => $issue['user']['login'],
                'date'          => $issue['updated_at'],
                'labels'        => $issue['labels'],
                'body'          => $body_html,
                'comments'      => $comments,
                'milestone'    => $milestone
            ];
        }

        return $issues;
    }

    private function getComments(int $number)
    {
        return $this->_client->api('issue')->comments()->all(
            $this->_config['github_user'],
            $this->_config['github_repo'],
            $number
        );
    }

    private function getLabels()
    {
        return $this->_client->api('issue')->labels()->all(
            $this->_config['github_user'],
            $this->_config['github_repo']
        );
    }

    public function renderDashboardWidget()
    {

        // Get Data
        $data = [
            'issues' => $this->getIssues(),
            'labels' => $this->getLabels(),

        ];

        // Render View
        echo __view('admin.dashboard.github-widget', $data);
    }

    public function sendGithubIssue()
    {
        // Sanitize Title
        $title = sanitize_text_field(strtoupper($this->_config['prefix']) . ': ' . $_POST['issue-title']);

        // Sanitize Body and convert to markdown
        $body = wp_filter_post_kses($_POST['issue-message']);
        $converter = new \League\HTMLToMarkdown\HtmlConverter();
        $body = $converter->convert($body);

        // Sanitize Label
        $label = sanitize_text_field($_POST['issue-label']);

        $data = [
            'title'     => $title,
            'body'      => $body,
            'labels'    => [$label],
            'assignee'  => 'nghh'
        ];
        $response = $this->_client->api('issue')->create(
            $this->_config['github_user'],
            $this->_config['github_repo'],
            $data
        );

        // Send Feedback
        echo json_encode(true);

        // WP Die
        wp_die();
    }

    public function addStylesAndScripts()
    { ?>
        <style>
            .ng-widget-issues {
                max-height: 448px;
                overflow-y: auto;
                margin: 0 -12px 24px;
            }

            .issues {}

            .new-issue-toggle {
                display: block;
                width: 40px;
                height: 40px;
                background: #007cba;
                color: white;
                text-align: center;
                font-size: 24px;
                border-radius: 50%;
                position: absolute;
                right: 20px;
                bottom: -18px;
                cursor: pointer;
            }

            .new-issue-toggle::after {
                content: '+';
                position: absolute;
                display: block;
                width: 24px;
                height: 35px;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            .new-issue-toggle.active::after {
                width: 24px;
                height: 35px;
                content: '-';
            }

            .issues__form {
                margin: 0 -12px -12px;
                background-color: #f1f1f1;
                padding: 0 12px;
                transition: max-height 0.2s ease-out;
                max-height: 0;
                overflow-y: hidden;
            }

            .issues__form-heading {
                margin-top: 12px;
                margin-bottom: 24px;
            }

            .issues__form-heading h2 {
                font-size: 18px;
                font-weight: 600;
                margin: 0;
            }

            .issues__form-heading p {
                font-style: italic;
                color: #7d7d7d;
                margin: 0;
            }

            .issues__form label {
                display: block;
            }

            .issues__form-submit {
                margin-top: 20px;
                margin-bottom: 12px;
            }

            .issues__form .input-wrapper {
                margin-bottom: 12px;
            }

            .issue__footer {
                padding: 4px 12px;
                font-size: 11px;
                font-style: italic;
            }

            .issue__message {
                border: 1px solid #eee;
                border-radius: 5px;
                padding: 12px;
                margin: 12px 0;
            }

            .issue__comments {
                margin-left: 12px;
            }

            .issue__comments-item {
                padding: 6px 12px;
                border-left: 1px solid #eee;
                border-bottom: 1px solid #eee;
                border-bottom-left-radius: 5px;
            }

            .issue__comments-date {
                font-size: 11px;
                margin-right: 3px;
                color: #999;
            }

            .issue__comments-comment {}

            .issue__header {
                display: grid;
                grid-template-columns: 3fr 1fr;
                background-color: #fafafa;
                padding: 8px 12px;
                color: #72777c;
                border-top: 1px solid #eee;
                cursor: pointer;
            }

            .issue__title {
                color: #23282d;
                font-weight: 600;
                display: block;
            }

            .issue__city {
                color: #23282d;
                font-weight: 400;
                display: inline-block;
                font-size: 11px;
                margin-right: 6px;
            }

            .issue__id,
            .issue__author,
            .issue__date {
                font-size: 11px;
                margin-right: 3px;
                color: #999;
            }

            .issue__date {
                font-style: italic;
            }

            .issue__id {
                font-weight: 600;
            }


            .issue__labels {
                width: 100%;
                text-align: right;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
                margin: 0;
            }

            .issue__labels li {
                margin: 0 4px 0 0;
                text-align: center;
                display: inline-block;
            }

            .issue__labels li:last-child {
                margin: 0;
            }

            .issue__labels li a {
                display: inline-block;
                padding: 2px 4px;
                border-radius: 3px;
                color: rgba(0, 0, 0, 0.6);
                text-decoration: none;
            }

            .issue__body {
                padding: 0 12px;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.2s ease-out;
            }

            .issue_body:first-child {
                margin-top: 12px;
            }

            .issue_body:last-child {
                margin-top: 12px;
            }
        </style>
        <script>
            (function($) {
                $(function() {
                    // Accordion
                    var acc = document.getElementsByClassName("issue-trigger");
                    var i;

                    for (i = 0; i < acc.length; i++) {
                        acc[i].addEventListener("click", function() {
                            this.classList.toggle("active");
                            var panel = this.nextElementSibling;
                            if (panel.style.maxHeight) {
                                panel.style.maxHeight = null;
                            } else {
                                panel.style.maxHeight = panel.scrollHeight + "px";
                            }
                        });
                    };

                    // New Issue Toggler
                    var issue_form = $('.issues__form');
                    $('.new-issue-toggle').on('click', function() {
                        $(this).toggleClass('active');


                        if (issue_form.css('max-height') != '0px') {
                            issue_form.css('max-height', '0px');
                        } else {
                            let scrollHeight = issue_form.prop('scrollHeight') + 'px';
                            issue_form.css('max-height', scrollHeight);
                        }
                    });

                    // Submit Form
                    $('#issue-send').on('click', function(e) {
                        e.preventDefault();

                        // Get the Form
                        var $form = $('#issue-form');

                        // Calls the save method on all editor instances in the collection. This can be useful when a form is to be submitted
                        tinyMCE.triggerSave();
                        // Create an FormData object 
                        $.ajax({
                            type: 'POST',
                            url: $form.attr('action'),
                            data: $form.serialize(),
                            cache: false,
                            success: function(data, textStatus, XMLHttpRequest) {
                                console.log(data);
                                $form.trigger("reset");
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                alert(errorThrown);
                            }
                        });
                    });

                });
            })(jQuery);
        </script>
<?php
    }

    public function registerHooks()
    {
        add_action('wp_dashboard_setup', [$this, 'addDashboardWidget']);
        add_action('admin_footer', [$this, 'addStylesAndScripts']);
        add_action('wp_ajax_send_github_issue', [$this, 'sendGithubIssue']);
        add_action('wp_ajax_nopriv_send_github_issue', [$this, 'sendGithubIssue']);
    }
}
