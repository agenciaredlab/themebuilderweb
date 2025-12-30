<?php
/**
 * Templates Library
 * Pre-designed templates and blocks
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Templates_Library {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_tbp_get_library_templates', [$this, 'get_library_templates']);
        add_action('wp_ajax_tbp_import_template', [$this, 'import_template']);
    }

    public function get_library_templates() {
        check_ajax_referer('tbp_admin', 'nonce');

        $category = sanitize_key($_POST['category'] ?? 'all');

        $templates = $this->get_templates($category);

        wp_send_json_success($templates);
    }

    public function import_template() {
        check_ajax_referer('tbp_admin', 'nonce');

        $template_id = sanitize_key($_POST['template_id'] ?? '');

        if (!$template_id) {
            wp_send_json_error(['message' => 'Invalid template']);
        }

        $templates = $this->get_all_templates();

        if (!isset($templates[$template_id])) {
            wp_send_json_error(['message' => 'Template not found']);
        }

        wp_send_json_success([
            'content' => $templates[$template_id]['content']
        ]);
    }

    private function get_templates($category = 'all') {
        $all = $this->get_all_templates();

        if ($category === 'all') {
            return array_map(function($t) {
                unset($t['content']);
                return $t;
            }, $all);
        }

        return array_filter(array_map(function($t) use ($category) {
            if ($t['category'] === $category) {
                unset($t['content']);
                return $t;
            }
            return null;
        }, $all));
    }

    private function get_all_templates() {
        return [
            // =============================================
            // HEADERS
            // =============================================
            'header-simple' => [
                'id' => 'header-simple',
                'title' => 'Simple Header',
                'category' => 'header',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/header-simple.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 15, 'right' => 0, 'bottom' => 15, 'left' => 0], 'backgroundColor' => '#ffffff'],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 30],
                                'children' => [
                                    [
                                        'type' => 'site-logo',
                                        'settings' => ['width' => 150]
                                    ],
                                    [
                                        'type' => 'nav-menu',
                                        'settings' => ['align' => 'right']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            'header-centered' => [
                'id' => 'header-centered',
                'title' => 'Centered Header',
                'category' => 'header',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/header-centered.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 20, 'right' => 0, 'bottom' => 20, 'left' => 0], 'backgroundColor' => '#ffffff'],
                        'children' => [
                            [
                                'type' => 'site-logo',
                                'settings' => ['width' => 180, 'align' => 'center']
                            ],
                            [
                                'type' => 'nav-menu',
                                'settings' => ['align' => 'center', 'spacing' => 30]
                            ]
                        ]
                    ]
                ]
            ],

            'header-dark' => [
                'id' => 'header-dark',
                'title' => 'Dark Header',
                'category' => 'header',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/header-dark.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 15, 'right' => 0, 'bottom' => 15, 'left' => 0], 'backgroundColor' => '#1e293b'],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 3, 'gap' => 30],
                                'children' => [
                                    ['type' => 'site-logo', 'settings' => ['width' => 150]],
                                    ['type' => 'nav-menu', 'settings' => ['align' => 'center', 'textColor' => '#ffffff']],
                                    ['type' => 'button', 'settings' => ['text' => 'Get Started', 'align' => 'right', 'size' => 'sm']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // HERO SECTIONS
            // =============================================
            'hero-classic' => [
                'id' => 'hero-classic',
                'title' => 'Classic Hero',
                'category' => 'hero',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/hero-classic.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => [
                            'padding' => ['top' => 100, 'right' => 0, 'bottom' => 100, 'left' => 0],
                            'backgroundColor' => '#f8fafc',
                            'contentWidth' => 'boxed'
                        ],
                        'children' => [
                            [
                                'type' => 'heading',
                                'settings' => ['title' => 'Build Something Amazing', 'tag' => 'h1', 'align' => 'center', 'typography' => ['size' => 56, 'weight' => '700']]
                            ],
                            [
                                'type' => 'text-editor',
                                'settings' => ['content' => '<p style="text-align: center; font-size: 20px; color: #64748b; max-width: 600px; margin: 20px auto;">Create stunning websites with our powerful drag & drop builder. No coding required.</p>']
                            ],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 20, 'align' => 'center'],
                                'children' => [
                                    ['type' => 'button', 'settings' => ['text' => 'Get Started', 'size' => 'lg', 'align' => 'right']],
                                    ['type' => 'button', 'settings' => ['text' => 'Learn More', 'size' => 'lg', 'align' => 'left', 'style' => 'outline']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            'hero-split' => [
                'id' => 'hero-split',
                'title' => 'Split Hero',
                'category' => 'hero',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/hero-split.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 60, 'verticalAlign' => 'center'],
                                'children' => [
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Welcome to the Future', 'tag' => 'h1', 'typography' => ['size' => 48, 'weight' => '700']]],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="font-size: 18px; color: #64748b;">Discover the most powerful website builder in the world. Create anything you can imagine.</p>']],
                                            ['type' => 'button', 'settings' => ['text' => 'Start Free Trial', 'size' => 'lg']]
                                        ]
                                    ],
                                    [
                                        'type' => 'image',
                                        'settings' => ['size' => 'full']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            'hero-video' => [
                'id' => 'hero-video',
                'title' => 'Video Background Hero',
                'category' => 'hero',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/hero-video.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => [
                            'padding' => ['top' => 150, 'right' => 0, 'bottom' => 150, 'left' => 0],
                            'backgroundType' => 'video',
                            'overlay' => 'rgba(0,0,0,0.6)'
                        ],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Your Vision. Our Platform.', 'tag' => 'h1', 'align' => 'center', 'textColor' => '#ffffff', 'typography' => ['size' => 64, 'weight' => '700']]],
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; color: rgba(255,255,255,0.9); font-size: 22px;">The ultimate website building experience.</p>']],
                            ['type' => 'button', 'settings' => ['text' => 'Watch Demo', 'size' => 'lg', 'align' => 'center', 'icon' => 'play']]
                        ]
                    ]
                ]
            ],

            // =============================================
            // FEATURES
            // =============================================
            'features-3col' => [
                'id' => 'features-3col',
                'title' => '3 Column Features',
                'category' => 'features',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/features-3col.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Why Choose Us', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; max-width: 600px; margin: 0 auto 40px;">Discover the features that make us the best choice.</p>']],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 3, 'gap' => 30],
                                'children' => [
                                    ['type' => 'icon-box', 'settings' => ['icon' => 'rocket', 'title' => 'Fast & Reliable', 'description' => 'Lightning fast performance with 99.9% uptime.']],
                                    ['type' => 'icon-box', 'settings' => ['icon' => 'shield', 'title' => 'Secure', 'description' => 'Enterprise-grade security for your peace of mind.']],
                                    ['type' => 'icon-box', 'settings' => ['icon' => 'headphones', 'title' => '24/7 Support', 'description' => 'Our team is always here to help you succeed.']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            'features-alternating' => [
                'id' => 'features-alternating',
                'title' => 'Alternating Features',
                'category' => 'features',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/features-alt.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 60, 'verticalAlign' => 'center'],
                                'children' => [
                                    ['type' => 'image', 'settings' => ['size' => 'full', 'borderRadius' => 12]],
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Powerful Features', 'tag' => 'h2']],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p>Our platform offers all the tools you need to build and grow your online presence.</p>']],
                                            ['type' => 'icon-box', 'settings' => ['icon' => 'check', 'title' => 'Drag & Drop Builder', 'layout' => 'inline']],
                                            ['type' => 'icon-box', 'settings' => ['icon' => 'check', 'title' => 'Responsive Design', 'layout' => 'inline']],
                                            ['type' => 'icon-box', 'settings' => ['icon' => 'check', 'title' => 'SEO Optimized', 'layout' => 'inline']]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // TESTIMONIALS
            // =============================================
            'testimonials-grid' => [
                'id' => 'testimonials-grid',
                'title' => 'Testimonials Grid',
                'category' => 'testimonials',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/testimonials-grid.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0], 'backgroundColor' => '#f8fafc'],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'What Our Customers Say', 'tag' => 'h2', 'align' => 'center']],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 3, 'gap' => 30],
                                'children' => [
                                    ['type' => 'testimonial', 'settings' => ['content' => 'Amazing product! It has completely transformed how we work.', 'name' => 'John Smith', 'title' => 'CEO, TechCorp', 'rating' => 5]],
                                    ['type' => 'testimonial', 'settings' => ['content' => 'The best tool we have ever used. Highly recommended!', 'name' => 'Sarah Johnson', 'title' => 'Marketing Director', 'rating' => 5]],
                                    ['type' => 'testimonial', 'settings' => ['content' => 'Outstanding support and incredible features.', 'name' => 'Mike Davis', 'title' => 'Developer', 'rating' => 5]]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            'testimonials-carousel' => [
                'id' => 'testimonials-carousel',
                'title' => 'Testimonials Carousel',
                'category' => 'testimonials',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/testimonials-carousel.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Trusted by Thousands', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'testimonial-carousel', 'settings' => ['slidesToShow' => 1, 'autoplay' => true]]
                        ]
                    ]
                ]
            ],

            // =============================================
            // PRICING
            // =============================================
            'pricing-3col' => [
                'id' => 'pricing-3col',
                'title' => '3 Column Pricing',
                'category' => 'pricing',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/pricing-3col.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Simple, Transparent Pricing', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; max-width: 600px; margin: 0 auto 40px;">Choose the plan that works best for you.</p>']],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 3, 'gap' => 30],
                                'children' => [
                                    ['type' => 'pricing-table', 'settings' => ['title' => 'Starter', 'price' => '$9', 'period' => '/month', 'features' => ['5 Projects', '10GB Storage', 'Email Support'], 'buttonText' => 'Get Started']],
                                    ['type' => 'pricing-table', 'settings' => ['title' => 'Professional', 'price' => '$29', 'period' => '/month', 'features' => ['Unlimited Projects', '100GB Storage', 'Priority Support', 'API Access'], 'buttonText' => 'Get Started', 'featured' => true]],
                                    ['type' => 'pricing-table', 'settings' => ['title' => 'Enterprise', 'price' => '$99', 'period' => '/month', 'features' => ['Unlimited Everything', 'Dedicated Support', 'Custom Integrations', 'SLA'], 'buttonText' => 'Contact Us']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // CTA
            // =============================================
            'cta-simple' => [
                'id' => 'cta-simple',
                'title' => 'Simple CTA',
                'category' => 'cta',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/cta-simple.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0], 'backgroundColor' => '#6366f1'],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Ready to Get Started?', 'tag' => 'h2', 'align' => 'center', 'textColor' => '#ffffff']],
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; color: rgba(255,255,255,0.9);">Join thousands of satisfied customers today.</p>']],
                            ['type' => 'button', 'settings' => ['text' => 'Start Free Trial', 'size' => 'lg', 'align' => 'center', 'backgroundColor' => '#ffffff', 'textColor' => '#6366f1']]
                        ]
                    ]
                ]
            ],

            'cta-newsletter' => [
                'id' => 'cta-newsletter',
                'title' => 'Newsletter CTA',
                'category' => 'cta',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/cta-newsletter.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0], 'backgroundColor' => '#1e293b'],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 40, 'verticalAlign' => 'center'],
                                'children' => [
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Subscribe to our newsletter', 'tag' => 'h3', 'textColor' => '#ffffff']],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="color: rgba(255,255,255,0.7);">Get the latest updates and offers.</p>']]
                                        ]
                                    ],
                                    ['type' => 'form', 'settings' => ['fields' => [['type' => 'email', 'placeholder' => 'Enter your email']], 'buttonText' => 'Subscribe', 'layout' => 'inline']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // TEAM
            // =============================================
            'team-grid' => [
                'id' => 'team-grid',
                'title' => 'Team Grid',
                'category' => 'team',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/team-grid.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Meet Our Team', 'tag' => 'h2', 'align' => 'center']],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 4, 'gap' => 30],
                                'children' => [
                                    ['type' => 'image-box', 'settings' => ['title' => 'John Doe', 'description' => 'CEO & Founder', 'imageRounded' => true]],
                                    ['type' => 'image-box', 'settings' => ['title' => 'Jane Smith', 'description' => 'CTO', 'imageRounded' => true]],
                                    ['type' => 'image-box', 'settings' => ['title' => 'Mike Johnson', 'description' => 'Lead Designer', 'imageRounded' => true]],
                                    ['type' => 'image-box', 'settings' => ['title' => 'Sarah Wilson', 'description' => 'Marketing Lead', 'imageRounded' => true]]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // CONTACT
            // =============================================
            'contact-split' => [
                'id' => 'contact-split',
                'title' => 'Split Contact',
                'category' => 'contact',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/contact-split.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 60],
                                'children' => [
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Get in Touch', 'tag' => 'h2']],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p>Have questions? We would love to hear from you.</p>']],
                                            ['type' => 'icon-box', 'settings' => ['icon' => 'map-marker', 'title' => '123 Main Street, City, Country', 'layout' => 'inline']],
                                            ['type' => 'icon-box', 'settings' => ['icon' => 'envelope', 'title' => 'contact@example.com', 'layout' => 'inline']],
                                            ['type' => 'icon-box', 'settings' => ['icon' => 'phone', 'title' => '+1 (555) 123-4567', 'layout' => 'inline']],
                                            ['type' => 'social-icons', 'settings' => ['icons' => ['facebook', 'twitter', 'linkedin', 'instagram']]]
                                        ]
                                    ],
                                    ['type' => 'form', 'settings' => [
                                        'fields' => [
                                            ['type' => 'text', 'label' => 'Name', 'required' => true],
                                            ['type' => 'email', 'label' => 'Email', 'required' => true],
                                            ['type' => 'textarea', 'label' => 'Message', 'required' => true]
                                        ],
                                        'buttonText' => 'Send Message'
                                    ]]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // FOOTERS
            // =============================================
            'footer-4col' => [
                'id' => 'footer-4col',
                'title' => '4 Column Footer',
                'category' => 'footer',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/footer-4col.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0], 'backgroundColor' => '#1e293b'],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 4, 'gap' => 30],
                                'children' => [
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'site-logo', 'settings' => ['width' => 150]],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="color: rgba(255,255,255,0.7);">Building the future, one website at a time.</p>']],
                                            ['type' => 'social-icons', 'settings' => ['icons' => ['facebook', 'twitter', 'linkedin']]]
                                        ]
                                    ],
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Product', 'tag' => 'h4', 'textColor' => '#ffffff']],
                                            ['type' => 'nav-menu', 'settings' => ['layout' => 'vertical', 'textColor' => 'rgba(255,255,255,0.7)']]
                                        ]
                                    ],
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Company', 'tag' => 'h4', 'textColor' => '#ffffff']],
                                            ['type' => 'nav-menu', 'settings' => ['layout' => 'vertical', 'textColor' => 'rgba(255,255,255,0.7)']]
                                        ]
                                    ],
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Newsletter', 'tag' => 'h4', 'textColor' => '#ffffff']],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="color: rgba(255,255,255,0.7);">Subscribe for updates</p>']],
                                            ['type' => 'form', 'settings' => ['fields' => [['type' => 'email', 'placeholder' => 'Your email']], 'buttonText' => 'Subscribe', 'layout' => 'stacked']]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 20, 'right' => 0, 'bottom' => 20, 'left' => 0], 'backgroundColor' => '#0f172a'],
                        'children' => [
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; color: rgba(255,255,255,0.5); margin: 0;">&copy; 2024 Your Company. All rights reserved.</p>']]
                        ]
                    ]
                ]
            ],

            // =============================================
            // FAQ
            // =============================================
            'faq-accordion' => [
                'id' => 'faq-accordion',
                'title' => 'FAQ Accordion',
                'category' => 'faq',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/faq-accordion.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Frequently Asked Questions', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'accordion', 'settings' => [
                                'items' => [
                                    ['title' => 'How do I get started?', 'content' => 'Getting started is easy! Simply sign up for an account and follow our quick setup guide.'],
                                    ['title' => 'What payment methods do you accept?', 'content' => 'We accept all major credit cards, PayPal, and bank transfers.'],
                                    ['title' => 'Can I cancel my subscription?', 'content' => 'Yes, you can cancel your subscription at any time. No long-term contracts.'],
                                    ['title' => 'Do you offer refunds?', 'content' => 'We offer a 30-day money-back guarantee on all plans.']
                                ]
                            ]]
                        ]
                    ]
                ]
            ],

            // =============================================
            // PORTFOLIO
            // =============================================
            'portfolio-grid' => [
                'id' => 'portfolio-grid',
                'title' => 'Portfolio Grid',
                'category' => 'portfolio',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/portfolio-grid.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Our Work', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'portfolio', 'settings' => ['columns' => 3, 'gap' => 20, 'filter' => true]]
                        ]
                    ]
                ]
            ],

            // =============================================
            // BLOG
            // =============================================
            'blog-grid' => [
                'id' => 'blog-grid',
                'title' => 'Blog Grid',
                'category' => 'blog',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/blog-grid.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Latest Articles', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'posts', 'settings' => ['columns' => 3, 'postsPerPage' => 6, 'showExcerpt' => true, 'showMeta' => true]]
                        ]
                    ]
                ]
            ],

            // =============================================
            // STATS
            // =============================================
            'stats-counters' => [
                'id' => 'stats-counters',
                'title' => 'Stats Counters',
                'category' => 'stats',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/stats-counters.jpg',
                'content' => [
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0], 'backgroundColor' => '#6366f1'],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 4, 'gap' => 30],
                                'children' => [
                                    ['type' => 'counter', 'settings' => ['number' => 10000, 'suffix' => '+', 'title' => 'Happy Customers', 'textColor' => '#ffffff']],
                                    ['type' => 'counter', 'settings' => ['number' => 500, 'suffix' => '+', 'title' => 'Projects Completed', 'textColor' => '#ffffff']],
                                    ['type' => 'counter', 'settings' => ['number' => 50, 'suffix' => '+', 'title' => 'Team Members', 'textColor' => '#ffffff']],
                                    ['type' => 'counter', 'settings' => ['number' => 99, 'suffix' => '%', 'title' => 'Client Satisfaction', 'textColor' => '#ffffff']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // =============================================
            // FULL PAGES
            // =============================================
            'page-landing' => [
                'id' => 'page-landing',
                'title' => 'Complete Landing Page',
                'category' => 'page',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/page-landing.jpg',
                'content' => [
                    // Hero
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 120, 'right' => 0, 'bottom' => 120, 'left' => 0], 'backgroundType' => 'gradient', 'backgroundGradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Build Your Dream Website', 'tag' => 'h1', 'align' => 'center', 'textColor' => '#ffffff', 'typography' => ['size' => 56]]],
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; color: rgba(255,255,255,0.9); font-size: 20px; max-width: 600px; margin: 20px auto;">The most powerful drag & drop builder for WordPress.</p>']],
                            ['type' => 'button', 'settings' => ['text' => 'Get Started Free', 'size' => 'lg', 'align' => 'center', 'backgroundColor' => '#ffffff', 'textColor' => '#6366f1']]
                        ]
                    ],
                    // Features
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Everything You Need', 'tag' => 'h2', 'align' => 'center']],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 3, 'gap' => 30],
                                'children' => [
                                    ['type' => 'icon-box', 'settings' => ['icon' => 'paint-brush', 'title' => 'Visual Editor', 'description' => 'Design in real-time with our intuitive drag & drop builder.']],
                                    ['type' => 'icon-box', 'settings' => ['icon' => 'mobile', 'title' => 'Responsive', 'description' => 'Your site looks perfect on any device automatically.']],
                                    ['type' => 'icon-box', 'settings' => ['icon' => 'bolt', 'title' => 'Fast', 'description' => 'Optimized code for lightning-fast load times.']]
                                ]
                            ]
                        ]
                    ],
                    // CTA
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0], 'backgroundColor' => '#f8fafc'],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Ready to Get Started?', 'tag' => 'h2', 'align' => 'center']],
                            ['type' => 'button', 'settings' => ['text' => 'Start Building Now', 'size' => 'lg', 'align' => 'center']]
                        ]
                    ]
                ]
            ],

            'page-about' => [
                'id' => 'page-about',
                'title' => 'About Us Page',
                'category' => 'page',
                'thumbnail' => TBP_ASSETS_URL . 'images/templates/page-about.jpg',
                'content' => [
                    // Hero
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 100, 'right' => 0, 'bottom' => 100, 'left' => 0], 'backgroundColor' => '#1e293b'],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'About Us', 'tag' => 'h1', 'align' => 'center', 'textColor' => '#ffffff']],
                            ['type' => 'text-editor', 'settings' => ['content' => '<p style="text-align: center; color: rgba(255,255,255,0.8);">Our story, our mission, our team.</p>']]
                        ]
                    ],
                    // Story
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 2, 'gap' => 60, 'verticalAlign' => 'center'],
                                'children' => [
                                    ['type' => 'image', 'settings' => ['borderRadius' => 12]],
                                    [
                                        'type' => 'container',
                                        'children' => [
                                            ['type' => 'heading', 'settings' => ['title' => 'Our Story', 'tag' => 'h2']],
                                            ['type' => 'text-editor', 'settings' => ['content' => '<p>Founded in 2020, we started with a simple mission: to make website building accessible to everyone. Today, we serve thousands of customers worldwide.</p>']]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    // Stats
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0], 'backgroundColor' => '#6366f1'],
                        'children' => [
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 4, 'gap' => 30],
                                'children' => [
                                    ['type' => 'counter', 'settings' => ['number' => 10000, 'suffix' => '+', 'title' => 'Users', 'textColor' => '#ffffff']],
                                    ['type' => 'counter', 'settings' => ['number' => 50, 'title' => 'Countries', 'textColor' => '#ffffff']],
                                    ['type' => 'counter', 'settings' => ['number' => 24, 'suffix' => '/7', 'title' => 'Support', 'textColor' => '#ffffff']],
                                    ['type' => 'counter', 'settings' => ['number' => 99, 'suffix' => '%', 'title' => 'Uptime', 'textColor' => '#ffffff']]
                                ]
                            ]
                        ]
                    ],
                    // Team
                    [
                        'type' => 'section',
                        'settings' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                        'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Meet the Team', 'tag' => 'h2', 'align' => 'center']],
                            [
                                'type' => 'columns',
                                'settings' => ['columns' => 4, 'gap' => 30],
                                'children' => [
                                    ['type' => 'image-box', 'settings' => ['title' => 'John Doe', 'description' => 'CEO', 'imageRounded' => true]],
                                    ['type' => 'image-box', 'settings' => ['title' => 'Jane Smith', 'description' => 'CTO', 'imageRounded' => true]],
                                    ['type' => 'image-box', 'settings' => ['title' => 'Mike Johnson', 'description' => 'Designer', 'imageRounded' => true]],
                                    ['type' => 'image-box', 'settings' => ['title' => 'Sarah Wilson', 'description' => 'Marketing', 'imageRounded' => true]]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function get_categories() {
        return [
            'all' => ['title' => 'All', 'icon' => 'grid'],
            'header' => ['title' => 'Headers', 'icon' => 'layout-top'],
            'hero' => ['title' => 'Hero', 'icon' => 'star'],
            'features' => ['title' => 'Features', 'icon' => 'check-circle'],
            'testimonials' => ['title' => 'Testimonials', 'icon' => 'message-circle'],
            'pricing' => ['title' => 'Pricing', 'icon' => 'dollar-sign'],
            'cta' => ['title' => 'Call to Action', 'icon' => 'zap'],
            'team' => ['title' => 'Team', 'icon' => 'users'],
            'contact' => ['title' => 'Contact', 'icon' => 'mail'],
            'footer' => ['title' => 'Footers', 'icon' => 'layout-bottom'],
            'faq' => ['title' => 'FAQ', 'icon' => 'help-circle'],
            'portfolio' => ['title' => 'Portfolio', 'icon' => 'image'],
            'blog' => ['title' => 'Blog', 'icon' => 'file-text'],
            'stats' => ['title' => 'Stats', 'icon' => 'bar-chart'],
            'page' => ['title' => 'Full Pages', 'icon' => 'file']
        ];
    }
}

// Initialize
TBP_Templates_Library::instance();
