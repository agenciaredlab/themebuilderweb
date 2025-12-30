<?php
/**
 * Theme Builder Pro - Canvas Template
 * Full-width blank canvas for the visual editor
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get document settings
$post_id = get_the_ID();
$document_settings = get_post_meta($post_id, '_tbp_document_settings', true);
$page_settings = is_array($document_settings) ? $document_settings : [];

// Page settings
$page_title = isset($page_settings['hide_title']) && $page_settings['hide_title'] ? false : true;
$page_layout = isset($page_settings['layout']) ? $page_settings['layout'] : 'default';
$content_width = isset($page_settings['content_width']) ? $page_settings['content_width'] : 'full';
$page_background = isset($page_settings['background']) ? $page_settings['background'] : '';

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php if (!current_theme_supports('title-tag')): ?>
        <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <?php endif; ?>

    <?php wp_head(); ?>

    <?php if ($page_background): ?>
    <style>
        body.tbp-canvas {
            <?php if (isset($page_background['color'])): ?>
                background-color: <?php echo esc_attr($page_background['color']); ?>;
            <?php endif; ?>
            <?php if (isset($page_background['image'])): ?>
                background-image: url('<?php echo esc_url($page_background['image']); ?>');
                background-size: <?php echo isset($page_background['size']) ? esc_attr($page_background['size']) : 'cover'; ?>;
                background-position: <?php echo isset($page_background['position']) ? esc_attr($page_background['position']) : 'center center'; ?>;
                background-repeat: <?php echo isset($page_background['repeat']) ? esc_attr($page_background['repeat']) : 'no-repeat'; ?>;
                background-attachment: <?php echo isset($page_background['attachment']) ? esc_attr($page_background['attachment']) : 'scroll'; ?>;
            <?php endif; ?>
        }
    </style>
    <?php endif; ?>
</head>

<body <?php body_class('tbp-canvas tbp-canvas-' . esc_attr($page_layout)); ?>>

<?php wp_body_open(); ?>

<?php
// Render Theme Builder header if assigned
do_action('tbp_before_header');

if (function_exists('tbp_render_theme_location')) {
    tbp_render_theme_location('header');
}

do_action('tbp_after_header');
?>

<main id="tbp-content" class="tbp-content tbp-content-<?php echo esc_attr($content_width); ?>">
    <?php
    do_action('tbp_before_content');

    while (have_posts()):
        the_post();

        // Get the TBP content
        $tbp_content = get_post_meta($post_id, '_tbp_content', true);

        if ($tbp_content && is_array($tbp_content)) {
            // Render TBP elements
            echo '<div class="tbp-document">';
            tbp_render_elements($tbp_content);
            echo '</div>';
        } else {
            // Fallback to standard content
            the_content();
        }

    endwhile;

    do_action('tbp_after_content');
    ?>
</main>

<?php
// Render Theme Builder footer if assigned
do_action('tbp_before_footer');

if (function_exists('tbp_render_theme_location')) {
    tbp_render_theme_location('footer');
}

do_action('tbp_after_footer');
?>

<?php wp_footer(); ?>

</body>
</html>
<?php

/**
 * Render TBP elements recursively
 */
function tbp_render_elements($elements) {
    if (!is_array($elements)) {
        return;
    }

    foreach ($elements as $element) {
        tbp_render_element($element);
    }
}

/**
 * Render a single TBP element
 */
function tbp_render_element($element) {
    if (!isset($element['type'])) {
        return;
    }

    $type = $element['type'];
    $id = isset($element['id']) ? $element['id'] : '';
    $settings = isset($element['settings']) ? $element['settings'] : [];
    $children = isset($element['children']) ? $element['children'] : [];

    // Build CSS classes
    $classes = ['tbp-element', 'tbp-element-' . esc_attr($type)];

    if (isset($settings['cssClasses']) && $settings['cssClasses']) {
        $classes[] = esc_attr($settings['cssClasses']);
    }

    // Animation classes
    if (isset($settings['animation']) && $settings['animation'] !== 'none') {
        $classes[] = 'tbp-has-animation';
    }

    // Responsive visibility
    if (isset($settings['hideDesktop']) && $settings['hideDesktop']) {
        $classes[] = 'tbp-hide-desktop';
    }
    if (isset($settings['hideTablet']) && $settings['hideTablet']) {
        $classes[] = 'tbp-hide-tablet';
    }
    if (isset($settings['hideMobile']) && $settings['hideMobile']) {
        $classes[] = 'tbp-hide-mobile';
    }

    // Build inline styles
    $styles = tbp_build_element_styles($settings);

    // Data attributes for animation
    $data_attrs = '';
    if (isset($settings['animation']) && $settings['animation'] !== 'none') {
        $data_attrs .= ' data-animation="' . esc_attr($settings['animation']) . '"';
        if (isset($settings['animationDuration'])) {
            $data_attrs .= ' data-animation-duration="' . esc_attr($settings['animationDuration']) . '"';
        }
        if (isset($settings['animationDelay'])) {
            $data_attrs .= ' data-animation-delay="' . esc_attr($settings['animationDelay']) . '"';
        }
    }

    // Custom ID
    $element_id = isset($settings['cssId']) && $settings['cssId'] ? $settings['cssId'] : 'tbp-' . $id;

    // Render based on type
    echo '<div id="' . esc_attr($element_id) . '" class="' . esc_attr(implode(' ', $classes)) . '" style="' . esc_attr($styles) . '"' . $data_attrs . '>';

    switch ($type) {
        case 'section':
        case 'container':
            tbp_render_section($element, $settings, $children);
            break;

        case 'columns':
            tbp_render_columns($element, $settings, $children);
            break;

        case 'column':
            tbp_render_column($element, $settings, $children);
            break;

        case 'heading':
            tbp_render_heading($settings);
            break;

        case 'text-editor':
            tbp_render_text_editor($settings);
            break;

        case 'button':
            tbp_render_button($settings);
            break;

        case 'image':
            tbp_render_image($settings);
            break;

        case 'video':
            tbp_render_video($settings);
            break;

        case 'spacer':
            tbp_render_spacer($settings);
            break;

        case 'divider':
            tbp_render_divider($settings);
            break;

        case 'icon':
            tbp_render_icon($settings);
            break;

        case 'icon-box':
            tbp_render_icon_box($settings);
            break;

        case 'image-box':
            tbp_render_image_box($settings);
            break;

        case 'counter':
            tbp_render_counter($settings);
            break;

        case 'progress-bar':
            tbp_render_progress_bar($settings);
            break;

        case 'tabs':
            tbp_render_tabs($settings, $children);
            break;

        case 'accordion':
            tbp_render_accordion($settings, $children);
            break;

        case 'testimonial':
            tbp_render_testimonial($settings);
            break;

        case 'social-icons':
            tbp_render_social_icons($settings);
            break;

        default:
            // Allow custom widgets
            do_action('tbp_render_widget_' . $type, $element, $settings, $children);
            break;
    }

    echo '</div>';
}

/**
 * Build inline styles from settings
 */
function tbp_build_element_styles($settings) {
    $styles = [];

    // Padding
    if (isset($settings['padding']) && is_array($settings['padding'])) {
        $p = $settings['padding'];
        $unit = isset($p['unit']) ? $p['unit'] : 'px';
        $styles[] = sprintf('padding: %s%s %s%s %s%s %s%s',
            $p['top'] ?? 0, $unit,
            $p['right'] ?? 0, $unit,
            $p['bottom'] ?? 0, $unit,
            $p['left'] ?? 0, $unit
        );
    }

    // Margin
    if (isset($settings['margin']) && is_array($settings['margin'])) {
        $m = $settings['margin'];
        $unit = isset($m['unit']) ? $m['unit'] : 'px';
        $styles[] = sprintf('margin: %s%s %s%s %s%s %s%s',
            $m['top'] ?? 0, $unit,
            $m['right'] ?? 0, $unit,
            $m['bottom'] ?? 0, $unit,
            $m['left'] ?? 0, $unit
        );
    }

    // Background color
    if (isset($settings['backgroundColor']) && $settings['backgroundColor']) {
        $styles[] = 'background-color: ' . $settings['backgroundColor'];
    }

    // Text color
    if (isset($settings['textColor']) && $settings['textColor']) {
        $styles[] = 'color: ' . $settings['textColor'];
    }

    // Border
    if (isset($settings['border']) && is_array($settings['border'])) {
        $b = $settings['border'];
        if (isset($b['width']) && $b['width'] > 0) {
            $styles[] = sprintf('border: %spx %s %s',
                $b['width'],
                $b['style'] ?? 'solid',
                $b['color'] ?? '#000000'
            );
        }
        if (isset($b['radius']) && is_array($b['radius'])) {
            $r = $b['radius'];
            $styles[] = sprintf('border-radius: %spx %spx %spx %spx',
                $r['top'] ?? 0,
                $r['right'] ?? 0,
                $r['bottom'] ?? 0,
                $r['left'] ?? 0
            );
        }
    }

    // Box shadow
    if (isset($settings['boxShadow']) && is_array($settings['boxShadow']) && !empty($settings['boxShadow']['enabled'])) {
        $s = $settings['boxShadow'];
        $styles[] = sprintf('box-shadow: %s%spx %spx %spx %spx %s',
            !empty($s['inset']) ? 'inset ' : '',
            $s['horizontal'] ?? 0,
            $s['vertical'] ?? 0,
            $s['blur'] ?? 0,
            $s['spread'] ?? 0,
            $s['color'] ?? 'rgba(0,0,0,0.2)'
        );
    }

    // Typography
    if (isset($settings['typography']) && is_array($settings['typography'])) {
        $t = $settings['typography'];
        if (isset($t['family']) && $t['family'] !== 'Default') {
            $styles[] = "font-family: '" . $t['family'] . "', sans-serif";
        }
        if (isset($t['size'])) {
            $styles[] = 'font-size: ' . $t['size'] . 'px';
        }
        if (isset($t['weight'])) {
            $styles[] = 'font-weight: ' . $t['weight'];
        }
        if (isset($t['lineHeight'])) {
            $styles[] = 'line-height: ' . $t['lineHeight'];
        }
        if (isset($t['letterSpacing'])) {
            $styles[] = 'letter-spacing: ' . $t['letterSpacing'] . 'px';
        }
    }

    return implode('; ', $styles);
}

/**
 * Widget renderers
 */
function tbp_render_section($element, $settings, $children) {
    $content_width = isset($settings['contentWidth']) ? $settings['contentWidth'] : 'boxed';
    $min_height = isset($settings['minHeight']) ? $settings['minHeight'] : 0;

    echo '<div class="tbp-section-inner tbp-section-' . esc_attr($content_width) . '"';
    if ($min_height) {
        echo ' style="min-height: ' . esc_attr($min_height) . 'px"';
    }
    echo '>';

    if (!empty($children)) {
        tbp_render_elements($children);
    }

    echo '</div>';
}

function tbp_render_columns($element, $settings, $children) {
    $columns = isset($settings['columns']) ? intval($settings['columns']) : 2;
    $gap = isset($settings['gap']) ? $settings['gap'] : 20;

    echo '<div class="tbp-columns-inner" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . esc_attr($gap) . 'px;">';

    if (!empty($children)) {
        tbp_render_elements($children);
    }

    echo '</div>';
}

function tbp_render_column($element, $settings, $children) {
    $width = isset($settings['width']) ? $settings['width'] : '';

    echo '<div class="tbp-column-inner"';
    if ($width) {
        echo ' style="width: ' . esc_attr($width) . '%"';
    }
    echo '>';

    if (!empty($children)) {
        tbp_render_elements($children);
    }

    echo '</div>';
}

function tbp_render_heading($settings) {
    $title = isset($settings['title']) ? $settings['title'] : 'Add Heading';
    $tag = isset($settings['tag']) ? $settings['tag'] : 'h2';
    $align = isset($settings['align']) ? $settings['align'] : 'left';
    $link = isset($settings['link']) ? $settings['link'] : '';

    $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div'];
    $tag = in_array($tag, $allowed_tags) ? $tag : 'h2';

    $content = esc_html($title);

    if ($link) {
        $content = '<a href="' . esc_url($link) . '">' . $content . '</a>';
    }

    printf(
        '<%1$s class="tbp-heading" style="text-align: %2$s">%3$s</%1$s>',
        $tag,
        esc_attr($align),
        $content
    );
}

function tbp_render_text_editor($settings) {
    $content = isset($settings['content']) ? $settings['content'] : '<p>Add your content here...</p>';

    echo '<div class="tbp-text-editor">';
    echo wp_kses_post($content);
    echo '</div>';
}

function tbp_render_button($settings) {
    $text = isset($settings['text']) ? $settings['text'] : 'Click Here';
    $link = isset($settings['link']) ? $settings['link'] : '#';
    $size = isset($settings['size']) ? $settings['size'] : 'medium';
    $align = isset($settings['align']) ? $settings['align'] : 'left';
    $target = isset($settings['newWindow']) && $settings['newWindow'] ? '_blank' : '_self';

    echo '<div class="tbp-button-wrapper" style="text-align: ' . esc_attr($align) . '">';
    echo '<a href="' . esc_url($link) . '" class="tbp-button tbp-button-' . esc_attr($size) . '" target="' . esc_attr($target) . '">';
    echo esc_html($text);
    echo '</a>';
    echo '</div>';
}

function tbp_render_image($settings) {
    $image = isset($settings['image']) ? $settings['image'] : null;
    $alt = isset($settings['alt']) ? $settings['alt'] : '';
    $align = isset($settings['align']) ? $settings['align'] : 'left';
    $link = isset($settings['link']) ? $settings['link'] : '';
    $lightbox = isset($settings['lightbox']) && $settings['lightbox'];

    if (!$image || !isset($image['url'])) {
        echo '<div class="tbp-image-placeholder">Select an image</div>';
        return;
    }

    echo '<div class="tbp-image-wrapper" style="text-align: ' . esc_attr($align) . '">';

    if ($link || $lightbox) {
        $href = $lightbox ? $image['url'] : $link;
        echo '<a href="' . esc_url($href) . '"' . ($lightbox ? ' data-lightbox="gallery"' : '') . '>';
    }

    echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($alt) . '" class="tbp-image" />';

    if ($link || $lightbox) {
        echo '</a>';
    }

    if (isset($settings['caption']) && $settings['caption']) {
        echo '<figcaption class="tbp-image-caption">' . esc_html($settings['caption']) . '</figcaption>';
    }

    echo '</div>';
}

function tbp_render_video($settings) {
    $video_type = isset($settings['videoType']) ? $settings['videoType'] : 'youtube';
    $video_url = isset($settings['videoUrl']) ? $settings['videoUrl'] : '';

    if (!$video_url) {
        echo '<div class="tbp-video-placeholder">Add a video URL</div>';
        return;
    }

    echo '<div class="tbp-video-wrapper">';

    // Parse video ID
    if ($video_type === 'youtube') {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches);
        $video_id = isset($matches[1]) ? $matches[1] : '';

        if ($video_id) {
            echo '<iframe src="https://www.youtube.com/embed/' . esc_attr($video_id) . '" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
        }
    } elseif ($video_type === 'vimeo') {
        preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $video_url, $matches);
        $video_id = isset($matches[1]) ? $matches[1] : '';

        if ($video_id) {
            echo '<iframe src="https://player.vimeo.com/video/' . esc_attr($video_id) . '" frameborder="0" allowfullscreen allow="autoplay; fullscreen; picture-in-picture"></iframe>';
        }
    } else {
        echo '<video src="' . esc_url($video_url) . '" controls></video>';
    }

    echo '</div>';
}

function tbp_render_spacer($settings) {
    $space = isset($settings['space']) ? intval($settings['space']) : 50;
    echo '<div class="tbp-spacer" style="height: ' . esc_attr($space) . 'px;"></div>';
}

function tbp_render_divider($settings) {
    $style = isset($settings['style']) ? $settings['style'] : 'solid';
    $weight = isset($settings['weight']) ? $settings['weight'] : 1;
    $color = isset($settings['color']) ? $settings['color'] : '#cccccc';
    $width = isset($settings['width']) ? $settings['width'] : 100;
    $align = isset($settings['align']) ? $settings['align'] : 'center';

    $margin = 'auto';
    if ($align === 'left') $margin = '0 auto 0 0';
    if ($align === 'right') $margin = '0 0 0 auto';

    echo '<hr class="tbp-divider" style="border-style: ' . esc_attr($style) . '; border-width: ' . esc_attr($weight) . 'px; border-color: ' . esc_attr($color) . '; width: ' . esc_attr($width) . '%; margin: ' . $margin . ';" />';
}

function tbp_render_icon($settings) {
    $icon = isset($settings['icon']) ? $settings['icon'] : 'fas fa-star';
    $size = isset($settings['size']) ? $settings['size'] : 50;
    $color = isset($settings['color']) ? $settings['color'] : '#333333';
    $align = isset($settings['align']) ? $settings['align'] : 'center';

    echo '<div class="tbp-icon-wrapper" style="text-align: ' . esc_attr($align) . '">';
    echo '<i class="' . esc_attr($icon) . '" style="font-size: ' . esc_attr($size) . 'px; color: ' . esc_attr($color) . ';"></i>';
    echo '</div>';
}

function tbp_render_icon_box($settings) {
    $icon = isset($settings['icon']) ? $settings['icon'] : 'fas fa-star';
    $title = isset($settings['title']) ? $settings['title'] : 'Icon Box';
    $description = isset($settings['description']) ? $settings['description'] : 'Add description here';

    echo '<div class="tbp-icon-box">';
    echo '<div class="tbp-icon-box-icon"><i class="' . esc_attr($icon) . '"></i></div>';
    echo '<h4 class="tbp-icon-box-title">' . esc_html($title) . '</h4>';
    echo '<p class="tbp-icon-box-description">' . esc_html($description) . '</p>';
    echo '</div>';
}

function tbp_render_image_box($settings) {
    $image = isset($settings['image']) ? $settings['image'] : null;
    $title = isset($settings['title']) ? $settings['title'] : 'Image Box';
    $description = isset($settings['description']) ? $settings['description'] : 'Add description here';

    echo '<div class="tbp-image-box">';
    if ($image && isset($image['url'])) {
        echo '<div class="tbp-image-box-image"><img src="' . esc_url($image['url']) . '" alt="" /></div>';
    }
    echo '<h4 class="tbp-image-box-title">' . esc_html($title) . '</h4>';
    echo '<p class="tbp-image-box-description">' . esc_html($description) . '</p>';
    echo '</div>';
}

function tbp_render_counter($settings) {
    $target = isset($settings['target']) ? intval($settings['target']) : 100;
    $prefix = isset($settings['prefix']) ? $settings['prefix'] : '';
    $suffix = isset($settings['suffix']) ? $settings['suffix'] : '';
    $title = isset($settings['title']) ? $settings['title'] : '';
    $duration = isset($settings['duration']) ? intval($settings['duration']) : 2000;

    echo '<div class="tbp-counter">';
    echo '<div class="tbp-counter-number" data-target="' . esc_attr($target) . '" data-prefix="' . esc_attr($prefix) . '" data-suffix="' . esc_attr($suffix) . '" data-duration="' . esc_attr($duration) . '">';
    echo esc_html($prefix) . '0' . esc_html($suffix);
    echo '</div>';
    if ($title) {
        echo '<div class="tbp-counter-title">' . esc_html($title) . '</div>';
    }
    echo '</div>';
}

function tbp_render_progress_bar($settings) {
    $value = isset($settings['value']) ? intval($settings['value']) : 50;
    $title = isset($settings['title']) ? $settings['title'] : '';
    $show_percentage = isset($settings['showPercentage']) ? $settings['showPercentage'] : true;

    echo '<div class="tbp-progress-bar" data-value="' . esc_attr($value) . '">';
    if ($title || $show_percentage) {
        echo '<div class="tbp-progress-header">';
        if ($title) {
            echo '<span class="tbp-progress-title">' . esc_html($title) . '</span>';
        }
        if ($show_percentage) {
            echo '<span class="tbp-progress-percentage">' . esc_html($value) . '%</span>';
        }
        echo '</div>';
    }
    echo '<div class="tbp-progress-track">';
    echo '<div class="tbp-progress-fill" style="width: 0%;"></div>';
    echo '</div>';
    echo '</div>';
}

function tbp_render_tabs($settings, $children) {
    $tabs = isset($settings['tabs']) ? $settings['tabs'] : [];

    if (empty($tabs)) {
        return;
    }

    echo '<div class="tbp-tabs">';
    echo '<div class="tbp-tabs-nav">';
    foreach ($tabs as $index => $tab) {
        $active = $index === 0 ? ' active' : '';
        echo '<button class="tbp-tab' . $active . '" data-tab="' . esc_attr($index) . '">' . esc_html($tab['title']) . '</button>';
    }
    echo '</div>';

    echo '<div class="tbp-tabs-content">';
    foreach ($tabs as $index => $tab) {
        $active = $index === 0 ? ' active' : '';
        echo '<div class="tbp-tab-panel' . $active . '" data-tab="' . esc_attr($index) . '">';
        echo wp_kses_post($tab['content']);
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
}

function tbp_render_accordion($settings, $children) {
    $items = isset($settings['items']) ? $settings['items'] : [];
    $allow_multiple = isset($settings['multiple']) && $settings['multiple'];

    if (empty($items)) {
        return;
    }

    echo '<div class="tbp-accordion"' . ($allow_multiple ? ' data-multiple="true"' : '') . '>';
    foreach ($items as $index => $item) {
        $active = $index === 0 ? ' active' : '';
        echo '<div class="tbp-accordion-item' . $active . '">';
        echo '<div class="tbp-accordion-header">';
        echo '<span>' . esc_html($item['title']) . '</span>';
        echo '<span class="tbp-accordion-icon">+</span>';
        echo '</div>';
        echo '<div class="tbp-accordion-content"';
        if ($index === 0) {
            echo ' style="max-height: 1000px;"';
        }
        echo '>';
        echo '<div class="tbp-accordion-content-inner">' . wp_kses_post($item['content']) . '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

function tbp_render_testimonial($settings) {
    $content = isset($settings['content']) ? $settings['content'] : 'Add testimonial text here';
    $name = isset($settings['name']) ? $settings['name'] : 'John Doe';
    $title = isset($settings['title']) ? $settings['title'] : 'CEO';
    $image = isset($settings['image']) ? $settings['image'] : null;

    echo '<div class="tbp-testimonial">';
    echo '<div class="tbp-testimonial-content">"' . esc_html($content) . '"</div>';
    echo '<div class="tbp-testimonial-author">';
    if ($image && isset($image['url'])) {
        echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($name) . '" class="tbp-testimonial-image" />';
    }
    echo '<div class="tbp-testimonial-info">';
    echo '<div class="tbp-testimonial-name">' . esc_html($name) . '</div>';
    echo '<div class="tbp-testimonial-title">' . esc_html($title) . '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

function tbp_render_social_icons($settings) {
    $icons = isset($settings['icons']) ? $settings['icons'] : [];
    $size = isset($settings['size']) ? $settings['size'] : 'medium';
    $shape = isset($settings['shape']) ? $settings['shape'] : 'rounded';

    if (empty($icons)) {
        $icons = [
            ['platform' => 'facebook', 'url' => '#'],
            ['platform' => 'twitter', 'url' => '#'],
            ['platform' => 'instagram', 'url' => '#'],
        ];
    }

    $icon_map = [
        'facebook' => 'fab fa-facebook-f',
        'twitter' => 'fab fa-twitter',
        'instagram' => 'fab fa-instagram',
        'linkedin' => 'fab fa-linkedin-in',
        'youtube' => 'fab fa-youtube',
        'pinterest' => 'fab fa-pinterest-p',
        'tiktok' => 'fab fa-tiktok',
    ];

    echo '<div class="tbp-social-icons tbp-social-' . esc_attr($size) . ' tbp-social-' . esc_attr($shape) . '">';
    foreach ($icons as $icon) {
        $platform = isset($icon['platform']) ? $icon['platform'] : 'facebook';
        $url = isset($icon['url']) ? $icon['url'] : '#';
        $icon_class = isset($icon_map[$platform]) ? $icon_map[$platform] : 'fas fa-link';

        echo '<a href="' . esc_url($url) . '" class="tbp-social-icon tbp-social-' . esc_attr($platform) . '" target="_blank" rel="noopener noreferrer">';
        echo '<i class="' . esc_attr($icon_class) . '"></i>';
        echo '</a>';
    }
    echo '</div>';
}
