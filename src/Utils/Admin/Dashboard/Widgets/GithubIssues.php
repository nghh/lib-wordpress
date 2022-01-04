<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Dashboard\Widgets;

use Nghh\Lib\Wordpress\Utils\Admin\Github\Client;

use function Nghh\Theme\func\view;

class GithubIssues extends WPDashboardWidget
{
  private $args;
  private $_client;

  public function __construct($args)
  {
    $this->args = wp_parse_args($args, $this->default_args);

    // Add Admin Styles and Scripts
    $this->styles[] = 'github-issues.css';
    $this->scripts[] = 'github-issues.js';
  }

  private function _getData()
  {
    // Create Client
    $this->_client = Client::create()->withAuth(['token' =>  $this->args['config']['github_token']]);

    $query = <<<'GRAPHQL'
        query GetRepositoryData($name: String!, $owner: String!) {
          repository(name: $name, owner: $owner) {
            id
            labels(first: 10) {
              nodes {
                id
                description
                name
              }
            }
            issues(first: 20) {
              nodes {
                bodyHTML
                createdAt
                id
                number
                title
                url
                state
                author {
                  login
                }
                milestone {
                  dueOn
                  description
                  title
                }
                comments(first: 30) {
                  nodes {
                    bodyHTML
                    url
                    id
                    createdAt
                  }
                }
                labels(first: 10) {
                  nodes {
                    color
                    description
                    name
                  }
                }
              }
            }
          }
        }
        GRAPHQL;

    $variables = [
      'owner' => $this->args['config']['github_owner'],
      'name' => $this->args['config']['github_repo']
    ];

    return $this->_client->api('graphql')->execute($query, $variables);
  }

  private function _parseResponse($response)
  {
    $data = [
      'errors' => [],
      'repository' => [],
      'issues' => [
        'open' => [],
        'closed' => []
      ],
      'labels' => []
    ];

    // Check for errors
    if (isset($response['errors'])) {
      $data['errors'] = array_map(function ($error) {
        return $error['message'];
      }, $response['errors']);
      return $data;
    }


    // Parse Repository Infos
    $data['repository']['id'] = $response['data']['repository']['id'];

    // Parse Issues
    $issues = $response['data']['repository']['issues']['nodes'];

    foreach ($issues as $issue) {
      // State of Issue
      $state = strtolower($issue['state']);

      // Parse Comments
      if (isset($issue['comments']['nodes'])) {
        $issue['comments'] = $issue['comments']['nodes'];
      }

      // Parse Labels
      if (isset($issue['labels']['nodes'])) {
        $issue['labels'] = $issue['labels']['nodes'];
      }
      // Store to Data
      $data['issues'][$state][] = $issue;
    }

    // Parse Labels
    $data['labels'] = $response['data']['repository']['labels']['nodes'];

    return $data;
  }

  public function renderDashboardWidget()
  {
    // Prepare Data
    $data = [
      'issues' => [],
      'labels' => [],
    ];

    $response = $this->_getData();
    $data = $this->_parseResponse($response);

    // Render
    echo view('admin.dashboard.github-issues', $data);
  }

  public function ajaxCreateIssue()
  {
    // Create Client
    $this->_client = Client::create()->withAuth(['token' =>  $this->args['config']['github_token']]);

    // Sanitize Repo ID
    $repo_id = sanitize_text_field($_POST['repository-id']);

    // Sanitize Title
    $title = sanitize_text_field($_POST['issue-title']);

    // Sanitize Body and convert to markdown
    $body = wp_filter_post_kses($_POST['issue-message']);

    // Sanitize Label
    $label = sanitize_text_field($_POST['issue-label']);

    $query = <<<'GRAPHQL'
        mutation createIssue($repoId: ID!, $title: String!, $body: String, $labelId: [ID!]) {
          createIssue(input: {
            repositoryId: $repoId, 
            title: $title, 
            labelIds: $labelId, 
            body: $body}
          ) {
            clientMutationId
          }
        }
        GRAPHQL;

    $variables = [
      'repoId' => $repo_id,
      'title' => $title,
      'body' => $body,
      'labelId' => $label,
    ];

    // Execute API Call
    $this->_client->api('graphql')->execute($query, $variables);

    // Send Feedback
    echo json_encode(['error' => false]);

    // WP Die
    wp_die();
  }

  public function ajaxCreateComment()
  {
    // Create Client
    $this->_client = Client::create()->withAuth(['token' =>  $this->args['config']['github_token']]);

    // Sanitize Message
    $comment_body = wp_filter_post_kses($_POST['issue-comment-message']);

    if (!$comment_body || empty($comment_body)) {
      echo json_encode(['error' => true]);
      wp_die();
    }

    // Sanitize Label
    $issue_id = sanitize_text_field($_POST['issue-id']);

    // Send to Github
    // $client = Client::create()->withAuth(['token' =>  $this->args['config']['github_token']]);

    $query = <<<'GRAPHQL'
        mutation($subjectId: ID!, $body: String!) {
          addComment(input:{ 
            subjectId: $subjectId,
            body:$body
          }) {
            clientMutationId
          }
        }
        GRAPHQL;

    $variables = [
      'subjectId' => $issue_id,
      'body' => $comment_body
    ];

    // Execute API Call
    $this->_client->api('graphql')->execute($query, $variables);

    // Send Feedback
    echo json_encode(['error' => false]);
    wp_die();
  }

  public function registerDashboardWidget()
  {
    // Add Dashboard Widget
    add_meta_box(
      $this->args['slug'],
      $this->args['title'],
      [$this, 'renderDashboardWidget'],
      'dashboard',
      $this->args['context'],
      $this->args['priority'],
    );
  }

  private function _renderTemplate($file, $args)
  {
    // ensure the file exists
    if (!file_exists($file)) return '';

    // Make values in the associative array easier to access by extracting them
    if (is_array($args)) extract($args);

    // buffer the output (including the file is "output")
    ob_start();
    include $file;
    return ob_get_clean();
  }
  public function registerHooks()
  {
    // Hook into the Dashboard
    add_action('wp_dashboard_setup', [$this, 'registerDashboardWidget']);

    // Listen for Ajax Events
    add_action('wp_ajax_create_issue', [$this, 'ajaxCreateIssue']);
    add_action('wp_ajax_create_comment', [$this, 'ajaxCreateComment']);

    // Register WPDashboardWidget Hooks (styles and scripts)
    $this->registerStylesAndScripts();
  }
}
