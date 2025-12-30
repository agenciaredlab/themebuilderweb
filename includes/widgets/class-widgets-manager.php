<?php
/**
 * Widgets Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widgets_Manager {

    private $widgets = [];
    private $widget_types = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_default_widgets();

        do_action('tbp/widgets/register', $this);
    }

    /**
     * Register Default Widgets
     */
    private function register_default_widgets() {
        // Basic Widgets
        $this->register_widget('heading', [
            'title' => __('Heading', 'theme-builder-pro'),
            'icon' => 'eicon-t-letter',
            'categories' => ['basic'],
            'keywords' => ['heading', 'title', 'text', 'h1', 'h2', 'h3'],
            'class' => 'TBP_Widget_Heading',
        ]);

        $this->register_widget('text-editor', [
            'title' => __('Text Editor', 'theme-builder-pro'),
            'icon' => 'eicon-text',
            'categories' => ['basic'],
            'keywords' => ['text', 'editor', 'paragraph', 'content'],
            'class' => 'TBP_Widget_Text_Editor',
        ]);

        $this->register_widget('image', [
            'title' => __('Image', 'theme-builder-pro'),
            'icon' => 'eicon-image',
            'categories' => ['basic'],
            'keywords' => ['image', 'photo', 'picture'],
            'class' => 'TBP_Widget_Image',
        ]);

        $this->register_widget('video', [
            'title' => __('Video', 'theme-builder-pro'),
            'icon' => 'eicon-youtube',
            'categories' => ['basic'],
            'keywords' => ['video', 'youtube', 'vimeo', 'media'],
            'class' => 'TBP_Widget_Video',
        ]);

        $this->register_widget('button', [
            'title' => __('Button', 'theme-builder-pro'),
            'icon' => 'eicon-button',
            'categories' => ['basic'],
            'keywords' => ['button', 'link', 'cta'],
            'class' => 'TBP_Widget_Button',
        ]);

        $this->register_widget('icon', [
            'title' => __('Icon', 'theme-builder-pro'),
            'icon' => 'eicon-star',
            'categories' => ['basic'],
            'keywords' => ['icon', 'symbol'],
            'class' => 'TBP_Widget_Icon',
        ]);

        $this->register_widget('divider', [
            'title' => __('Divider', 'theme-builder-pro'),
            'icon' => 'eicon-divider',
            'categories' => ['basic'],
            'keywords' => ['divider', 'separator', 'line'],
            'class' => 'TBP_Widget_Divider',
        ]);

        $this->register_widget('spacer', [
            'title' => __('Spacer', 'theme-builder-pro'),
            'icon' => 'eicon-spacer',
            'categories' => ['basic'],
            'keywords' => ['spacer', 'space', 'gap'],
            'class' => 'TBP_Widget_Spacer',
        ]);

        $this->register_widget('icon-box', [
            'title' => __('Icon Box', 'theme-builder-pro'),
            'icon' => 'eicon-icon-box',
            'categories' => ['basic'],
            'keywords' => ['icon', 'box', 'feature'],
            'class' => 'TBP_Widget_Icon_Box',
        ]);

        $this->register_widget('image-box', [
            'title' => __('Image Box', 'theme-builder-pro'),
            'icon' => 'eicon-image-box',
            'categories' => ['basic'],
            'keywords' => ['image', 'box', 'feature'],
            'class' => 'TBP_Widget_Image_Box',
        ]);

        // General Widgets
        $this->register_widget('gallery', [
            'title' => __('Gallery', 'theme-builder-pro'),
            'icon' => 'eicon-gallery-grid',
            'categories' => ['general'],
            'keywords' => ['gallery', 'images', 'photos'],
            'class' => 'TBP_Widget_Gallery',
        ]);

        $this->register_widget('carousel', [
            'title' => __('Carousel', 'theme-builder-pro'),
            'icon' => 'eicon-slider-push',
            'categories' => ['general'],
            'keywords' => ['carousel', 'slider', 'images'],
            'class' => 'TBP_Widget_Carousel',
        ]);

        $this->register_widget('tabs', [
            'title' => __('Tabs', 'theme-builder-pro'),
            'icon' => 'eicon-tabs',
            'categories' => ['general'],
            'keywords' => ['tabs', 'content', 'sections'],
            'class' => 'TBP_Widget_Tabs',
        ]);

        $this->register_widget('accordion', [
            'title' => __('Accordion', 'theme-builder-pro'),
            'icon' => 'eicon-accordion',
            'categories' => ['general'],
            'keywords' => ['accordion', 'toggle', 'faq'],
            'class' => 'TBP_Widget_Accordion',
        ]);

        $this->register_widget('toggle', [
            'title' => __('Toggle', 'theme-builder-pro'),
            'icon' => 'eicon-toggle',
            'categories' => ['general'],
            'keywords' => ['toggle', 'accordion'],
            'class' => 'TBP_Widget_Toggle',
        ]);

        $this->register_widget('counter', [
            'title' => __('Counter', 'theme-builder-pro'),
            'icon' => 'eicon-counter',
            'categories' => ['general'],
            'keywords' => ['counter', 'number', 'stats'],
            'class' => 'TBP_Widget_Counter',
        ]);

        $this->register_widget('progress-bar', [
            'title' => __('Progress Bar', 'theme-builder-pro'),
            'icon' => 'eicon-skill-bar',
            'categories' => ['general'],
            'keywords' => ['progress', 'bar', 'skill'],
            'class' => 'TBP_Widget_Progress_Bar',
        ]);

        $this->register_widget('testimonial', [
            'title' => __('Testimonial', 'theme-builder-pro'),
            'icon' => 'eicon-testimonial',
            'categories' => ['general'],
            'keywords' => ['testimonial', 'review', 'quote'],
            'class' => 'TBP_Widget_Testimonial',
        ]);

        $this->register_widget('testimonial-carousel', [
            'title' => __('Testimonial Carousel', 'theme-builder-pro'),
            'icon' => 'eicon-testimonial-carousel',
            'categories' => ['general'],
            'keywords' => ['testimonial', 'carousel', 'slider'],
            'class' => 'TBP_Widget_Testimonial_Carousel',
        ]);

        $this->register_widget('social-icons', [
            'title' => __('Social Icons', 'theme-builder-pro'),
            'icon' => 'eicon-social-icons',
            'categories' => ['general'],
            'keywords' => ['social', 'icons', 'share'],
            'class' => 'TBP_Widget_Social_Icons',
        ]);

        $this->register_widget('star-rating', [
            'title' => __('Star Rating', 'theme-builder-pro'),
            'icon' => 'eicon-rating',
            'categories' => ['general'],
            'keywords' => ['star', 'rating', 'review'],
            'class' => 'TBP_Widget_Star_Rating',
        ]);

        $this->register_widget('alert', [
            'title' => __('Alert', 'theme-builder-pro'),
            'icon' => 'eicon-alert',
            'categories' => ['general'],
            'keywords' => ['alert', 'notice', 'message'],
            'class' => 'TBP_Widget_Alert',
        ]);

        $this->register_widget('countdown', [
            'title' => __('Countdown', 'theme-builder-pro'),
            'icon' => 'eicon-countdown',
            'categories' => ['general'],
            'keywords' => ['countdown', 'timer', 'date'],
            'class' => 'TBP_Widget_Countdown',
        ]);

        $this->register_widget('pricing-table', [
            'title' => __('Pricing Table', 'theme-builder-pro'),
            'icon' => 'eicon-price-table',
            'categories' => ['general'],
            'keywords' => ['pricing', 'table', 'plan'],
            'class' => 'TBP_Widget_Pricing_Table',
        ]);

        $this->register_widget('flip-box', [
            'title' => __('Flip Box', 'theme-builder-pro'),
            'icon' => 'eicon-flip-box',
            'categories' => ['general'],
            'keywords' => ['flip', 'box', 'hover'],
            'class' => 'TBP_Widget_Flip_Box',
        ]);

        $this->register_widget('call-to-action', [
            'title' => __('Call to Action', 'theme-builder-pro'),
            'icon' => 'eicon-call-to-action',
            'categories' => ['general'],
            'keywords' => ['cta', 'call', 'action'],
            'class' => 'TBP_Widget_Call_To_Action',
        ]);

        // Pro Widgets
        $this->register_widget('posts', [
            'title' => __('Posts', 'theme-builder-pro'),
            'icon' => 'eicon-post-list',
            'categories' => ['pro'],
            'keywords' => ['posts', 'blog', 'articles'],
            'class' => 'TBP_Widget_Posts',
        ]);

        $this->register_widget('portfolio', [
            'title' => __('Portfolio', 'theme-builder-pro'),
            'icon' => 'eicon-gallery-masonry',
            'categories' => ['pro'],
            'keywords' => ['portfolio', 'gallery', 'projects'],
            'class' => 'TBP_Widget_Portfolio',
        ]);

        $this->register_widget('nav-menu', [
            'title' => __('Nav Menu', 'theme-builder-pro'),
            'icon' => 'eicon-nav-menu',
            'categories' => ['pro'],
            'keywords' => ['menu', 'navigation', 'nav'],
            'class' => 'TBP_Widget_Nav_Menu',
        ]);

        $this->register_widget('search', [
            'title' => __('Search', 'theme-builder-pro'),
            'icon' => 'eicon-search',
            'categories' => ['pro'],
            'keywords' => ['search', 'find'],
            'class' => 'TBP_Widget_Search',
        ]);

        $this->register_widget('share-buttons', [
            'title' => __('Share Buttons', 'theme-builder-pro'),
            'icon' => 'eicon-share',
            'categories' => ['pro'],
            'keywords' => ['share', 'social', 'buttons'],
            'class' => 'TBP_Widget_Share_Buttons',
        ]);

        $this->register_widget('table-of-contents', [
            'title' => __('Table of Contents', 'theme-builder-pro'),
            'icon' => 'eicon-post-list',
            'categories' => ['pro'],
            'keywords' => ['toc', 'table', 'contents'],
            'class' => 'TBP_Widget_Table_Of_Contents',
        ]);

        $this->register_widget('animated-headline', [
            'title' => __('Animated Headline', 'theme-builder-pro'),
            'icon' => 'eicon-animated-headline',
            'categories' => ['pro'],
            'keywords' => ['animated', 'headline', 'text'],
            'class' => 'TBP_Widget_Animated_Headline',
        ]);

        $this->register_widget('price-list', [
            'title' => __('Price List', 'theme-builder-pro'),
            'icon' => 'eicon-price-list',
            'categories' => ['pro'],
            'keywords' => ['price', 'list', 'menu'],
            'class' => 'TBP_Widget_Price_List',
        ]);

        $this->register_widget('hotspots', [
            'title' => __('Hotspots', 'theme-builder-pro'),
            'icon' => 'eicon-image-hotspot',
            'categories' => ['pro'],
            'keywords' => ['hotspot', 'image', 'marker'],
            'class' => 'TBP_Widget_Hotspots',
        ]);

        $this->register_widget('lottie', [
            'title' => __('Lottie', 'theme-builder-pro'),
            'icon' => 'eicon-lottie',
            'categories' => ['pro'],
            'keywords' => ['lottie', 'animation', 'json'],
            'class' => 'TBP_Widget_Lottie',
        ]);

        // Form Widgets
        $this->register_widget('form', [
            'title' => __('Form', 'theme-builder-pro'),
            'icon' => 'eicon-form-horizontal',
            'categories' => ['form'],
            'keywords' => ['form', 'contact', 'input'],
            'class' => 'TBP_Widget_Form',
        ]);

        $this->register_widget('login', [
            'title' => __('Login', 'theme-builder-pro'),
            'icon' => 'eicon-lock-user',
            'categories' => ['form'],
            'keywords' => ['login', 'user', 'auth'],
            'class' => 'TBP_Widget_Login',
        ]);

        // Site Widgets
        $this->register_widget('site-logo', [
            'title' => __('Site Logo', 'theme-builder-pro'),
            'icon' => 'eicon-site-logo',
            'categories' => ['site'],
            'keywords' => ['logo', 'site', 'brand'],
            'class' => 'TBP_Widget_Site_Logo',
        ]);

        $this->register_widget('site-title', [
            'title' => __('Site Title', 'theme-builder-pro'),
            'icon' => 'eicon-site-title',
            'categories' => ['site'],
            'keywords' => ['title', 'site', 'name'],
            'class' => 'TBP_Widget_Site_Title',
        ]);

        $this->register_widget('page-title', [
            'title' => __('Page Title', 'theme-builder-pro'),
            'icon' => 'eicon-post-title',
            'categories' => ['site'],
            'keywords' => ['page', 'title', 'heading'],
            'class' => 'TBP_Widget_Page_Title',
        ]);

        // Single Post Widgets
        $this->register_widget('post-title', [
            'title' => __('Post Title', 'theme-builder-pro'),
            'icon' => 'eicon-post-title',
            'categories' => ['single'],
            'keywords' => ['post', 'title'],
            'class' => 'TBP_Widget_Post_Title',
        ]);

        $this->register_widget('post-excerpt', [
            'title' => __('Post Excerpt', 'theme-builder-pro'),
            'icon' => 'eicon-post-excerpt',
            'categories' => ['single'],
            'keywords' => ['post', 'excerpt', 'summary'],
            'class' => 'TBP_Widget_Post_Excerpt',
        ]);

        $this->register_widget('post-content', [
            'title' => __('Post Content', 'theme-builder-pro'),
            'icon' => 'eicon-post-content',
            'categories' => ['single'],
            'keywords' => ['post', 'content', 'body'],
            'class' => 'TBP_Widget_Post_Content',
        ]);

        $this->register_widget('post-featured-image', [
            'title' => __('Featured Image', 'theme-builder-pro'),
            'icon' => 'eicon-featured-image',
            'categories' => ['single'],
            'keywords' => ['featured', 'image', 'thumbnail'],
            'class' => 'TBP_Widget_Post_Featured_Image',
        ]);

        $this->register_widget('author-box', [
            'title' => __('Author Box', 'theme-builder-pro'),
            'icon' => 'eicon-person',
            'categories' => ['single'],
            'keywords' => ['author', 'box', 'bio'],
            'class' => 'TBP_Widget_Author_Box',
        ]);

        $this->register_widget('post-comments', [
            'title' => __('Post Comments', 'theme-builder-pro'),
            'icon' => 'eicon-comments',
            'categories' => ['single'],
            'keywords' => ['comments', 'post', 'discussion'],
            'class' => 'TBP_Widget_Post_Comments',
        ]);

        $this->register_widget('post-navigation', [
            'title' => __('Post Navigation', 'theme-builder-pro'),
            'icon' => 'eicon-post-navigation',
            'categories' => ['single'],
            'keywords' => ['navigation', 'post', 'prev', 'next'],
            'class' => 'TBP_Widget_Post_Navigation',
        ]);

        $this->register_widget('post-info', [
            'title' => __('Post Info', 'theme-builder-pro'),
            'icon' => 'eicon-post-info',
            'categories' => ['single'],
            'keywords' => ['post', 'info', 'meta'],
            'class' => 'TBP_Widget_Post_Info',
        ]);

        // Archive Widgets
        $this->register_widget('archive-title', [
            'title' => __('Archive Title', 'theme-builder-pro'),
            'icon' => 'eicon-archive-title',
            'categories' => ['archive'],
            'keywords' => ['archive', 'title'],
            'class' => 'TBP_Widget_Archive_Title',
        ]);

        $this->register_widget('archive-posts', [
            'title' => __('Archive Posts', 'theme-builder-pro'),
            'icon' => 'eicon-archive-posts',
            'categories' => ['archive'],
            'keywords' => ['archive', 'posts', 'loop'],
            'class' => 'TBP_Widget_Archive_Posts',
        ]);

        // WordPress Widgets
        $this->register_widget('sidebar', [
            'title' => __('Sidebar', 'theme-builder-pro'),
            'icon' => 'eicon-sidebar',
            'categories' => ['wordpress'],
            'keywords' => ['sidebar', 'widget'],
            'class' => 'TBP_Widget_Sidebar',
        ]);

        $this->register_widget('shortcode', [
            'title' => __('Shortcode', 'theme-builder-pro'),
            'icon' => 'eicon-shortcode',
            'categories' => ['wordpress'],
            'keywords' => ['shortcode', 'code'],
            'class' => 'TBP_Widget_Shortcode',
        ]);

        $this->register_widget('html', [
            'title' => __('HTML', 'theme-builder-pro'),
            'icon' => 'eicon-code',
            'categories' => ['wordpress'],
            'keywords' => ['html', 'code', 'embed'],
            'class' => 'TBP_Widget_HTML',
        ]);

        $this->register_widget('wordpress-widget', [
            'title' => __('WordPress Widget', 'theme-builder-pro'),
            'icon' => 'eicon-wordpress',
            'categories' => ['wordpress'],
            'keywords' => ['wordpress', 'widget'],
            'class' => 'TBP_Widget_WordPress_Widget',
        ]);
    }

    /**
     * Register Widget
     */
    public function register_widget($name, $config) {
        $this->widget_types[$name] = $config;
    }

    /**
     * Unregister Widget
     */
    public function unregister_widget($name) {
        if (isset($this->widget_types[$name])) {
            unset($this->widget_types[$name]);
        }
    }

    /**
     * Get Widget Types
     */
    public function get_widget_types() {
        return $this->widget_types;
    }

    /**
     * Get Widget
     */
    public function get_widget($name) {
        if (!isset($this->widgets[$name])) {
            if (!isset($this->widget_types[$name])) {
                return null;
            }

            $config = $this->widget_types[$name];
            $this->widgets[$name] = new TBP_Widget_Base($name, $config);
        }

        return $this->widgets[$name];
    }

    /**
     * Get Widgets by Category
     */
    public function get_widgets_by_category($category) {
        $widgets = [];

        foreach ($this->widget_types as $name => $config) {
            if (in_array($category, $config['categories'])) {
                $widgets[$name] = $config;
            }
        }

        return $widgets;
    }

    /**
     * Get Categories
     */
    public function get_categories() {
        return apply_filters('tbp/widgets/categories', [
            'basic' => [
                'title' => __('Basic', 'theme-builder-pro'),
                'icon' => 'eicon-font',
            ],
            'general' => [
                'title' => __('General', 'theme-builder-pro'),
                'icon' => 'eicon-apps',
            ],
            'pro' => [
                'title' => __('Pro', 'theme-builder-pro'),
                'icon' => 'eicon-star',
            ],
            'form' => [
                'title' => __('Form', 'theme-builder-pro'),
                'icon' => 'eicon-form-horizontal',
            ],
            'site' => [
                'title' => __('Site', 'theme-builder-pro'),
                'icon' => 'eicon-site-identity',
            ],
            'single' => [
                'title' => __('Single', 'theme-builder-pro'),
                'icon' => 'eicon-single-post',
            ],
            'archive' => [
                'title' => __('Archive', 'theme-builder-pro'),
                'icon' => 'eicon-archive',
            ],
            'wordpress' => [
                'title' => __('WordPress', 'theme-builder-pro'),
                'icon' => 'eicon-wordpress',
            ],
            'woocommerce' => [
                'title' => __('WooCommerce', 'theme-builder-pro'),
                'icon' => 'eicon-woocommerce',
            ],
        ]);
    }

    /**
     * Search Widgets
     */
    public function search_widgets($query) {
        $results = [];

        foreach ($this->widget_types as $name => $config) {
            $search_string = strtolower($config['title'] . ' ' . implode(' ', $config['keywords'] ?? []));

            if (strpos($search_string, strtolower($query)) !== false) {
                $results[$name] = $config;
            }
        }

        return $results;
    }
}
