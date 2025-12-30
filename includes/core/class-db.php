<?php
/**
 * Database Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_DB {

    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';

    /**
     * Table names
     */
    private static $tables = [
        'form_submissions' => 'tbp_form_submissions',
        'popup_analytics' => 'tbp_popup_analytics',
        'ab_tests' => 'tbp_ab_tests',
        'user_history' => 'tbp_user_history',
    ];

    /**
     * Create Tables
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Form Submissions Table
        $table_name = $wpdb->prefix . self::$tables['form_submissions'];
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned DEFAULT 0,
            user_id bigint(20) unsigned DEFAULT 0,
            data longtext NOT NULL,
            meta longtext DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            referrer varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT 'unread',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);

        // Popup Analytics Table
        $table_name = $wpdb->prefix . self::$tables['popup_analytics'];
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            popup_id bigint(20) unsigned NOT NULL,
            event_type varchar(20) NOT NULL,
            user_id bigint(20) unsigned DEFAULT 0,
            session_id varchar(64) DEFAULT NULL,
            page_url varchar(255) DEFAULT NULL,
            referrer varchar(255) DEFAULT NULL,
            device varchar(20) DEFAULT NULL,
            browser varchar(50) DEFAULT NULL,
            country varchar(2) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY popup_id (popup_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);

        // A/B Tests Table
        $table_name = $wpdb->prefix . self::$tables['ab_tests'];
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            test_name varchar(255) NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            variants longtext NOT NULL,
            traffic_split varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT 'draft',
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            winner_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // User History Table (Undo/Redo)
        $table_name = $wpdb->prefix . self::$tables['user_history'];
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            action varchar(50) NOT NULL,
            data longtext NOT NULL,
            timestamp bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        dbDelta($sql);

        update_option('tbp_db_version', self::DB_VERSION);
    }

    /**
     * Get Table Name
     */
    public static function get_table($name) {
        global $wpdb;

        if (isset(self::$tables[$name])) {
            return $wpdb->prefix . self::$tables[$name];
        }

        return null;
    }

    /**
     * Insert Form Submission
     */
    public static function insert_form_submission($data) {
        global $wpdb;

        $table = self::get_table('form_submissions');

        return $wpdb->insert($table, [
            'form_id' => $data['form_id'],
            'post_id' => $data['post_id'] ?? 0,
            'user_id' => get_current_user_id(),
            'data' => wp_json_encode($data['fields']),
            'meta' => isset($data['meta']) ? wp_json_encode($data['meta']) : null,
            'ip_address' => self::get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
            'status' => 'unread',
        ], [
            '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
        ]);
    }

    /**
     * Get Form Submissions
     */
    public static function get_form_submissions($form_id, $args = []) {
        global $wpdb;

        $table = self::get_table('form_submissions');
        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'status' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $offset = ($args['page'] - 1) * $args['per_page'];

        $where = $wpdb->prepare("WHERE form_id = %d", $form_id);

        if ($args['status']) {
            $where .= $wpdb->prepare(" AND status = %s", $args['status']);
        }

        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

        $query = "SELECT * FROM $table $where ORDER BY $orderby LIMIT %d OFFSET %d";
        $results = $wpdb->get_results($wpdb->prepare($query, $args['per_page'], $offset));

        // Decode JSON data
        foreach ($results as &$row) {
            $row->data = json_decode($row->data, true);
            $row->meta = $row->meta ? json_decode($row->meta, true) : [];
        }

        return $results;
    }

    /**
     * Count Form Submissions
     */
    public static function count_form_submissions($form_id, $status = '') {
        global $wpdb;

        $table = self::get_table('form_submissions');

        $where = $wpdb->prepare("WHERE form_id = %d", $form_id);

        if ($status) {
            $where .= $wpdb->prepare(" AND status = %s", $status);
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
    }

    /**
     * Update Submission Status
     */
    public static function update_submission_status($submission_id, $status) {
        global $wpdb;

        $table = self::get_table('form_submissions');

        return $wpdb->update($table, [
            'status' => $status,
        ], [
            'id' => $submission_id,
        ], ['%s'], ['%d']);
    }

    /**
     * Delete Submission
     */
    public static function delete_submission($submission_id) {
        global $wpdb;

        $table = self::get_table('form_submissions');

        return $wpdb->delete($table, ['id' => $submission_id], ['%d']);
    }

    /**
     * Track Popup Event
     */
    public static function track_popup_event($popup_id, $event_type) {
        global $wpdb;

        $table = self::get_table('popup_analytics');

        return $wpdb->insert($table, [
            'popup_id' => $popup_id,
            'event_type' => $event_type,
            'user_id' => get_current_user_id(),
            'session_id' => self::get_session_id(),
            'page_url' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
            'device' => self::get_device_type(),
            'browser' => self::get_browser(),
            'country' => self::get_country(),
        ], [
            '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
        ]);
    }

    /**
     * Get Popup Analytics
     */
    public static function get_popup_analytics($popup_id, $date_from = '', $date_to = '') {
        global $wpdb;

        $table = self::get_table('popup_analytics');

        $where = $wpdb->prepare("WHERE popup_id = %d", $popup_id);

        if ($date_from) {
            $where .= $wpdb->prepare(" AND created_at >= %s", $date_from);
        }

        if ($date_to) {
            $where .= $wpdb->prepare(" AND created_at <= %s", $date_to);
        }

        $query = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
            SUM(CASE WHEN event_type = 'close' THEN 1 ELSE 0 END) as closes,
            SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions
            FROM $table $where";

        return $wpdb->get_row($query);
    }

    /**
     * Save History State
     */
    public static function save_history_state($post_id, $action, $data) {
        global $wpdb;

        $table = self::get_table('user_history');
        $user_id = get_current_user_id();

        // Clean old history (keep last 50 states per post per user)
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE post_id = %d AND user_id = %d",
            $post_id,
            $user_id
        ));

        if ($count >= 50) {
            $oldest = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE post_id = %d AND user_id = %d ORDER BY timestamp ASC LIMIT 1",
                $post_id,
                $user_id
            ));

            if ($oldest) {
                $wpdb->delete($table, ['id' => $oldest], ['%d']);
            }
        }

        return $wpdb->insert($table, [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'action' => $action,
            'data' => wp_json_encode($data),
            'timestamp' => time(),
        ], [
            '%d', '%d', '%s', '%s', '%d'
        ]);
    }

    /**
     * Get History States
     */
    public static function get_history_states($post_id, $limit = 50) {
        global $wpdb;

        $table = self::get_table('user_history');
        $user_id = get_current_user_id();

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d AND user_id = %d ORDER BY timestamp DESC LIMIT %d",
            $post_id,
            $user_id,
            $limit
        ));

        foreach ($results as &$row) {
            $row->data = json_decode($row->data, true);
        }

        return $results;
    }

    /**
     * Clear History
     */
    public static function clear_history($post_id) {
        global $wpdb;

        $table = self::get_table('user_history');
        $user_id = get_current_user_id();

        return $wpdb->delete($table, [
            'post_id' => $post_id,
            'user_id' => $user_id,
        ], ['%d', '%d']);
    }

    /**
     * Get User IP
     */
    private static function get_user_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return sanitize_text_field($_SERVER[$key]);
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get Session ID
     */
    private static function get_session_id() {
        if (!session_id()) {
            return md5(self::get_user_ip() . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        }
        return session_id();
    }

    /**
     * Get Device Type
     */
    private static function get_device_type() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return 'unknown';
        }

        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $user_agent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad/i', $user_agent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Get Browser
     */
    private static function get_browser() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return 'unknown';
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $browsers = [
            'Edge' => '/Edge\/([0-9.]+)/',
            'Chrome' => '/Chrome\/([0-9.]+)/',
            'Firefox' => '/Firefox\/([0-9.]+)/',
            'Safari' => '/Safari\/([0-9.]+)/',
            'Opera' => '/Opera\/([0-9.]+)/',
            'IE' => '/MSIE ([0-9.]+)/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $browser;
            }
        }

        return 'unknown';
    }

    /**
     * Get Country (placeholder - needs GeoIP)
     */
    private static function get_country() {
        // This would require a GeoIP service
        return '';
    }

    /**
     * Export Data
     */
    public static function export_submissions($form_id, $format = 'csv') {
        $submissions = self::get_form_submissions($form_id, ['per_page' => -1]);

        if (empty($submissions)) {
            return null;
        }

        if ($format === 'csv') {
            return self::to_csv($submissions);
        }

        if ($format === 'json') {
            return wp_json_encode($submissions);
        }

        return null;
    }

    /**
     * Convert to CSV
     */
    private static function to_csv($data) {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Get all field keys from first submission
        $headers = ['ID', 'Date', 'Status'];
        if (!empty($data[0]->data)) {
            $headers = array_merge($headers, array_keys($data[0]->data));
        }

        fputcsv($output, $headers);

        foreach ($data as $row) {
            $csv_row = [
                $row->id,
                $row->created_at,
                $row->status,
            ];

            foreach (array_slice($headers, 3) as $key) {
                $csv_row[] = isset($row->data[$key]) ? $row->data[$key] : '';
            }

            fputcsv($output, $csv_row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
