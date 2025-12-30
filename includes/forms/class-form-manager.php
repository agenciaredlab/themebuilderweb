<?php
/**
 * Form Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Form_Manager {

    /**
     * Registered Actions
     */
    private $actions = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_actions();
        add_action('wp_ajax_tbp_form_submit', [$this, 'ajax_submit']);
        add_action('wp_ajax_nopriv_tbp_form_submit', [$this, 'ajax_submit']);
    }

    /**
     * Register Actions
     */
    private function register_actions() {
        $this->actions = [
            'email' => [
                'label' => __('Email', 'theme-builder-pro'),
                'callback' => [$this, 'action_email'],
                'settings' => [
                    'to' => [
                        'type' => 'text',
                        'label' => __('To', 'theme-builder-pro'),
                        'default' => get_option('admin_email'),
                    ],
                    'subject' => [
                        'type' => 'text',
                        'label' => __('Subject', 'theme-builder-pro'),
                        'default' => __('New Form Submission', 'theme-builder-pro'),
                    ],
                    'from_name' => [
                        'type' => 'text',
                        'label' => __('From Name', 'theme-builder-pro'),
                        'default' => get_bloginfo('name'),
                    ],
                    'from_email' => [
                        'type' => 'text',
                        'label' => __('From Email', 'theme-builder-pro'),
                        'default' => get_option('admin_email'),
                    ],
                    'reply_to' => [
                        'type' => 'text',
                        'label' => __('Reply To', 'theme-builder-pro'),
                        'dynamic' => true,
                    ],
                    'cc' => [
                        'type' => 'text',
                        'label' => __('CC', 'theme-builder-pro'),
                    ],
                    'bcc' => [
                        'type' => 'text',
                        'label' => __('BCC', 'theme-builder-pro'),
                    ],
                    'message' => [
                        'type' => 'wysiwyg',
                        'label' => __('Message', 'theme-builder-pro'),
                        'description' => __('Use {field_id} to include field values', 'theme-builder-pro'),
                    ],
                ],
            ],
            'email2' => [
                'label' => __('Email 2', 'theme-builder-pro'),
                'callback' => [$this, 'action_email'],
                'settings' => [], // Same as email
            ],
            'redirect' => [
                'label' => __('Redirect', 'theme-builder-pro'),
                'callback' => [$this, 'action_redirect'],
                'settings' => [
                    'redirect_url' => [
                        'type' => 'url',
                        'label' => __('Redirect URL', 'theme-builder-pro'),
                    ],
                ],
            ],
            'webhook' => [
                'label' => __('Webhook', 'theme-builder-pro'),
                'callback' => [$this, 'action_webhook'],
                'settings' => [
                    'webhook_url' => [
                        'type' => 'url',
                        'label' => __('Webhook URL', 'theme-builder-pro'),
                    ],
                    'method' => [
                        'type' => 'select',
                        'label' => __('Method', 'theme-builder-pro'),
                        'options' => [
                            'POST' => 'POST',
                            'GET' => 'GET',
                        ],
                        'default' => 'POST',
                    ],
                ],
            ],
            'mailchimp' => [
                'label' => __('Mailchimp', 'theme-builder-pro'),
                'callback' => [$this, 'action_mailchimp'],
                'settings' => [
                    'api_key' => [
                        'type' => 'text',
                        'label' => __('API Key', 'theme-builder-pro'),
                    ],
                    'list_id' => [
                        'type' => 'text',
                        'label' => __('List ID', 'theme-builder-pro'),
                    ],
                    'email_field' => [
                        'type' => 'text',
                        'label' => __('Email Field ID', 'theme-builder-pro'),
                    ],
                    'double_optin' => [
                        'type' => 'switcher',
                        'label' => __('Double Opt-in', 'theme-builder-pro'),
                        'default' => 'yes',
                    ],
                ],
            ],
            'slack' => [
                'label' => __('Slack', 'theme-builder-pro'),
                'callback' => [$this, 'action_slack'],
                'settings' => [
                    'webhook_url' => [
                        'type' => 'url',
                        'label' => __('Webhook URL', 'theme-builder-pro'),
                    ],
                    'channel' => [
                        'type' => 'text',
                        'label' => __('Channel', 'theme-builder-pro'),
                    ],
                    'username' => [
                        'type' => 'text',
                        'label' => __('Username', 'theme-builder-pro'),
                        'default' => 'Theme Builder Pro',
                    ],
                ],
            ],
            'discord' => [
                'label' => __('Discord', 'theme-builder-pro'),
                'callback' => [$this, 'action_discord'],
                'settings' => [
                    'webhook_url' => [
                        'type' => 'url',
                        'label' => __('Webhook URL', 'theme-builder-pro'),
                    ],
                ],
            ],
            'database' => [
                'label' => __('Collect Submissions', 'theme-builder-pro'),
                'callback' => [$this, 'action_database'],
                'settings' => [],
            ],
        ];

        $this->actions = apply_filters('tbp/forms/actions', $this->actions);
    }

    /**
     * Get Actions
     */
    public function get_actions() {
        return $this->actions;
    }

    /**
     * Ajax Submit
     */
    public function ajax_submit() {
        check_ajax_referer('tbp_frontend', 'nonce');

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $fields = isset($_POST['fields']) ? $_POST['fields'] : [];
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        $result = $this->process_submission([
            'form_id' => $form_id,
            'fields' => $fields,
            'post_id' => $post_id,
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'errors' => $result->get_error_data(),
            ]);
        }

        wp_send_json_success($result);
    }

    /**
     * Process Submission
     */
    public function process_submission($data) {
        $form_id = $data['form_id'] ?? 0;
        $fields = $data['fields'] ?? [];
        $post_id = $data['post_id'] ?? 0;

        // Validate honeypot
        if (!empty($fields['tbp_hp'])) {
            return new WP_Error('spam', __('Spam detected', 'theme-builder-pro'));
        }

        unset($fields['tbp_hp']);

        // Get form settings
        $form_settings = $this->get_form_settings($form_id);

        // Validate fields
        $validation = $this->validate_fields($fields, $form_settings);

        if (is_wp_error($validation)) {
            return $validation;
        }

        // Sanitize fields
        $fields = $this->sanitize_fields($fields, $form_settings);

        // Run actions
        $actions = $form_settings['actions'] ?? ['database', 'email'];

        foreach ($actions as $action_id) {
            $action_settings = $form_settings['action_' . $action_id] ?? [];

            if (isset($this->actions[$action_id])) {
                $callback = $this->actions[$action_id]['callback'];

                if (is_callable($callback)) {
                    $action_result = call_user_func($callback, $fields, $action_settings, $form_settings);

                    if (is_wp_error($action_result)) {
                        // Log error but continue
                        error_log('TBP Form Action Error: ' . $action_result->get_error_message());
                    }
                }
            }
        }

        // Store submission
        TBP_DB::insert_form_submission([
            'form_id' => $form_id,
            'post_id' => $post_id,
            'fields' => $fields,
            'meta' => [
                'actions_run' => $actions,
            ],
        ]);

        do_action('tbp/form/submission', $fields, $form_settings, $form_id);

        $success_message = $form_settings['success_message'] ?? __('Thank you for your submission!', 'theme-builder-pro');
        $redirect_url = $form_settings['redirect_url'] ?? '';

        return [
            'message' => $success_message,
            'redirect' => $redirect_url,
        ];
    }

    /**
     * Get Form Settings
     */
    private function get_form_settings($form_id) {
        // Form settings could be stored as post meta or in the element settings
        $settings = get_post_meta($form_id, '_tbp_form_settings', true);

        if ($settings) {
            return json_decode($settings, true);
        }

        // Default settings
        return [
            'fields' => [],
            'actions' => ['database', 'email'],
            'success_message' => __('Thank you for your submission!', 'theme-builder-pro'),
            'redirect_url' => '',
            'submit_button_text' => __('Submit', 'theme-builder-pro'),
        ];
    }

    /**
     * Validate Fields
     */
    private function validate_fields($fields, $form_settings) {
        $form_fields = $form_settings['fields'] ?? [];
        $errors = [];

        foreach ($form_fields as $field_id => $field_config) {
            $value = isset($fields[$field_id]) ? $fields[$field_id] : '';

            // Required check
            if (!empty($field_config['required']) && empty($value)) {
                $errors[$field_id] = sprintf(
                    __('%s is required', 'theme-builder-pro'),
                    $field_config['label'] ?? $field_id
                );
                continue;
            }

            if (empty($value)) {
                continue;
            }

            // Type validation
            $type = $field_config['type'] ?? 'text';

            switch ($type) {
                case 'email':
                    if (!is_email($value)) {
                        $errors[$field_id] = __('Please enter a valid email address', 'theme-builder-pro');
                    }
                    break;

                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[$field_id] = __('Please enter a valid URL', 'theme-builder-pro');
                    }
                    break;

                case 'number':
                    if (!is_numeric($value)) {
                        $errors[$field_id] = __('Please enter a valid number', 'theme-builder-pro');
                    }

                    if (isset($field_config['min']) && $value < $field_config['min']) {
                        $errors[$field_id] = sprintf(__('Minimum value is %s', 'theme-builder-pro'), $field_config['min']);
                    }

                    if (isset($field_config['max']) && $value > $field_config['max']) {
                        $errors[$field_id] = sprintf(__('Maximum value is %s', 'theme-builder-pro'), $field_config['max']);
                    }
                    break;

                case 'tel':
                    if (!preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
                        $errors[$field_id] = __('Please enter a valid phone number', 'theme-builder-pro');
                    }
                    break;

                case 'date':
                    if (!strtotime($value)) {
                        $errors[$field_id] = __('Please enter a valid date', 'theme-builder-pro');
                    }
                    break;
            }

            // Pattern validation
            if (!empty($field_config['pattern']) && !preg_match('/' . $field_config['pattern'] . '/', $value)) {
                $errors[$field_id] = $field_config['pattern_message'] ?? __('Invalid format', 'theme-builder-pro');
            }

            // Min/Max length
            if (!empty($field_config['minlength']) && strlen($value) < $field_config['minlength']) {
                $errors[$field_id] = sprintf(
                    __('Minimum %d characters required', 'theme-builder-pro'),
                    $field_config['minlength']
                );
            }

            if (!empty($field_config['maxlength']) && strlen($value) > $field_config['maxlength']) {
                $errors[$field_id] = sprintf(
                    __('Maximum %d characters allowed', 'theme-builder-pro'),
                    $field_config['maxlength']
                );
            }
        }

        if (!empty($errors)) {
            return new WP_Error('validation_failed', __('Validation failed', 'theme-builder-pro'), $errors);
        }

        return true;
    }

    /**
     * Sanitize Fields
     */
    private function sanitize_fields($fields, $form_settings) {
        $form_fields = $form_settings['fields'] ?? [];
        $sanitized = [];

        foreach ($fields as $field_id => $value) {
            $field_config = $form_fields[$field_id] ?? [];
            $type = $field_config['type'] ?? 'text';

            switch ($type) {
                case 'email':
                    $sanitized[$field_id] = sanitize_email($value);
                    break;

                case 'url':
                    $sanitized[$field_id] = esc_url_raw($value);
                    break;

                case 'textarea':
                    $sanitized[$field_id] = sanitize_textarea_field($value);
                    break;

                case 'number':
                    $sanitized[$field_id] = floatval($value);
                    break;

                case 'checkbox':
                case 'radio':
                case 'select':
                    if (is_array($value)) {
                        $sanitized[$field_id] = array_map('sanitize_text_field', $value);
                    } else {
                        $sanitized[$field_id] = sanitize_text_field($value);
                    }
                    break;

                default:
                    $sanitized[$field_id] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }

    /**
     * Action: Email
     */
    public function action_email($fields, $settings, $form_settings) {
        $to = $settings['to'] ?? get_option('admin_email');
        $subject = $settings['subject'] ?? __('New Form Submission', 'theme-builder-pro');
        $from_name = $settings['from_name'] ?? get_bloginfo('name');
        $from_email = $settings['from_email'] ?? get_option('admin_email');

        // Build message
        $message = $settings['message'] ?? '';

        if (empty($message)) {
            $message = $this->build_default_email($fields, $form_settings);
        } else {
            $message = $this->replace_field_tags($message, $fields);
        }

        // Headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_email}>",
        ];

        if (!empty($settings['reply_to'])) {
            $reply_to = $this->replace_field_tags($settings['reply_to'], $fields);
            $headers[] = "Reply-To: {$reply_to}";
        }

        if (!empty($settings['cc'])) {
            $headers[] = "Cc: {$settings['cc']}";
        }

        if (!empty($settings['bcc'])) {
            $headers[] = "Bcc: {$settings['bcc']}";
        }

        $result = wp_mail($to, $subject, $message, $headers);

        if (!$result) {
            return new WP_Error('email_failed', __('Failed to send email', 'theme-builder-pro'));
        }

        return true;
    }

    /**
     * Build Default Email
     */
    private function build_default_email($fields, $form_settings) {
        $form_fields = $form_settings['fields'] ?? [];

        $html = '<html><body>';
        $html .= '<h2>' . __('New Form Submission', 'theme-builder-pro') . '</h2>';
        $html .= '<table style="border-collapse: collapse; width: 100%;">';

        foreach ($fields as $field_id => $value) {
            $label = $form_fields[$field_id]['label'] ?? $field_id;

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 10px; font-weight: bold;">' . esc_html($label) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 10px;">' . esc_html($value) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Replace Field Tags
     */
    private function replace_field_tags($content, $fields) {
        foreach ($fields as $field_id => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $content = str_replace('{' . $field_id . '}', $value, $content);
            $content = str_replace('{{' . $field_id . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Action: Redirect
     */
    public function action_redirect($fields, $settings, $form_settings) {
        // Redirect is handled in the response
        return true;
    }

    /**
     * Action: Webhook
     */
    public function action_webhook($fields, $settings, $form_settings) {
        $url = $settings['webhook_url'] ?? '';
        $method = $settings['method'] ?? 'POST';

        if (empty($url)) {
            return new WP_Error('webhook_error', __('Webhook URL is required', 'theme-builder-pro'));
        }

        $args = [
            'method' => $method,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        if ($method === 'POST') {
            $args['body'] = wp_json_encode($fields);
        } else {
            $url = add_query_arg($fields, $url);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code < 200 || $code >= 300) {
            return new WP_Error('webhook_error', sprintf(__('Webhook returned status %d', 'theme-builder-pro'), $code));
        }

        return true;
    }

    /**
     * Action: Mailchimp
     */
    public function action_mailchimp($fields, $settings, $form_settings) {
        $api_key = $settings['api_key'] ?? '';
        $list_id = $settings['list_id'] ?? '';
        $email_field = $settings['email_field'] ?? 'email';
        $double_optin = $settings['double_optin'] ?? 'yes';

        if (empty($api_key) || empty($list_id)) {
            return new WP_Error('mailchimp_error', __('Mailchimp API key and List ID are required', 'theme-builder-pro'));
        }

        $email = $fields[$email_field] ?? '';

        if (empty($email)) {
            return new WP_Error('mailchimp_error', __('Email is required', 'theme-builder-pro'));
        }

        $dc = substr($api_key, strpos($api_key, '-') + 1);
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members";

        $data = [
            'email_address' => $email,
            'status' => $double_optin === 'yes' ? 'pending' : 'subscribed',
            'merge_fields' => [],
        ];

        // Map fields to merge fields
        foreach ($fields as $field_id => $value) {
            if ($field_id !== $email_field && !is_array($value)) {
                $merge_tag = strtoupper($field_id);
                $data['merge_fields'][$merge_tag] = $value;
            }
        }

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200 && $code !== 201) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return new WP_Error('mailchimp_error', $body['detail'] ?? __('Failed to subscribe', 'theme-builder-pro'));
        }

        return true;
    }

    /**
     * Action: Slack
     */
    public function action_slack($fields, $settings, $form_settings) {
        $webhook_url = $settings['webhook_url'] ?? '';
        $channel = $settings['channel'] ?? '';
        $username = $settings['username'] ?? 'Theme Builder Pro';

        if (empty($webhook_url)) {
            return new WP_Error('slack_error', __('Slack webhook URL is required', 'theme-builder-pro'));
        }

        $text = "*New Form Submission*\n";
        foreach ($fields as $field_id => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $text .= "*{$field_id}:* {$value}\n";
        }

        $data = [
            'text' => $text,
            'username' => $username,
        ];

        if ($channel) {
            $data['channel'] = $channel;
        }

        $response = wp_remote_post($webhook_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return true;
    }

    /**
     * Action: Discord
     */
    public function action_discord($fields, $settings, $form_settings) {
        $webhook_url = $settings['webhook_url'] ?? '';

        if (empty($webhook_url)) {
            return new WP_Error('discord_error', __('Discord webhook URL is required', 'theme-builder-pro'));
        }

        $embed_fields = [];
        foreach ($fields as $field_id => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $embed_fields[] = [
                'name' => $field_id,
                'value' => $value,
                'inline' => true,
            ];
        }

        $data = [
            'embeds' => [
                [
                    'title' => __('New Form Submission', 'theme-builder-pro'),
                    'color' => 5814783,
                    'fields' => $embed_fields,
                    'timestamp' => date('c'),
                ],
            ],
        ];

        $response = wp_remote_post($webhook_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return true;
    }

    /**
     * Action: Database
     */
    public function action_database($fields, $settings, $form_settings) {
        // Already stored in process_submission
        return true;
    }

    /**
     * Get Field Types
     */
    public function get_field_types() {
        return [
            'text' => __('Text', 'theme-builder-pro'),
            'email' => __('Email', 'theme-builder-pro'),
            'textarea' => __('Textarea', 'theme-builder-pro'),
            'number' => __('Number', 'theme-builder-pro'),
            'tel' => __('Phone', 'theme-builder-pro'),
            'url' => __('URL', 'theme-builder-pro'),
            'date' => __('Date', 'theme-builder-pro'),
            'time' => __('Time', 'theme-builder-pro'),
            'select' => __('Select', 'theme-builder-pro'),
            'radio' => __('Radio', 'theme-builder-pro'),
            'checkbox' => __('Checkbox', 'theme-builder-pro'),
            'file' => __('File Upload', 'theme-builder-pro'),
            'hidden' => __('Hidden', 'theme-builder-pro'),
            'password' => __('Password', 'theme-builder-pro'),
            'html' => __('HTML', 'theme-builder-pro'),
            'recaptcha' => __('reCAPTCHA', 'theme-builder-pro'),
            'acceptance' => __('Acceptance', 'theme-builder-pro'),
        ];
    }
}
