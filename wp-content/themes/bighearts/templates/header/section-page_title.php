<?php

defined('ABSPATH') || exit;

use BigHearts_Theme_Helper as BigHearts;

if (!class_exists('BigHearts_Get_Page_Title')) {
    /**
     *  Page Title Area
     *
     *  @package bighearts\templates
     *  @author WebGeniusLab <webgeniuslab@gmail.com>
     *  @since 1.0.0
     */
    class BigHearts_Get_Page_Title
    {
        private static $instance;
        private $page_title_tag;
        private $mb_page_title_switch;
        private $single;
        private $post_type;
        private $post_query;
        private $post_has_individual_styles;

        public function __construct()
        {
            $page_title = apply_filters('bighearts/page_title/enable', true);

            $page_title_disabled = 'on' === $page_title['page_title_switch'] ? false : true;

            if ($page_title_disabled) {
                // Bailout.
                return;
            }

            $this->mb_page_title_switch = $page_title['mb_page_title_switch'];
            $this->single = $page_title['single'];

            $this->render_page_title_html();
        }

        public function render_page_title_html()
        {
            echo '<div ', $this->get_page_header_attributes(), '>';
            echo '<div class="page-header_wrapper">';
            echo '<div class="wgl-container">';
            echo '<div class="page-header_content">';

            $title_text = $this->get_title_text();
            if ($title_text) {
                printf(
                    '<%1$s class="page-header_title" %2$s>%3$s</%1$s>',
                    $this->get_title_html_tag(),
                    $this->get_title_style(),
                    $title_text
                );
            }

            if ($this->is_breadcrumbs_enabled()) {
                list(
                    $breadcrumbs_style,
                    $breadcrumbs_html
                ) = $this->get_breadcrumbs();

                echo '<div class="page-header_breadcrumbs"', $breadcrumbs_style, '>',
                    $breadcrumbs_html,
                '</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        public function get_page_header_attributes()
        {
            $this->determine_post_type_and_query();

            // Parallax
            $parallax_enabled = BigHearts::get_mb_option('page_title_parallax', 'mb_page_title_switch', 'on');
            if ($parallax_enabled) {
                wp_enqueue_script('jquery-paroller', get_template_directory_uri() . '/js/jquery.paroller.min.js');
                $parallax_class = ' page_title_parallax';
                $parallax_speed = apply_filters('bighearts/page_title/parallax_speed', BigHearts::get_mb_option('page_title_parallax_speed', 'mb_page_title_switch', 'on'));
                $parallax_data_speed = $parallax_speed ?? '0.3';
            }

            $classes = $this->get_page_header_classes();
            $styles = $this->get_page_header_styles();
            $data_attr = $parallax_enabled ? ' data-paroller-factor=' . $parallax_data_speed : '';

            return ' class="page-header' . $classes . ($parallax_class ?? '') . '"'
                . $styles
                . $data_attr;
        }

        public function determine_post_type_and_query()
        {
            $queried_post_type = get_post_type();
            switch (true) {
                // ↓ WooCommerce
                case function_exists('is_shop') && is_shop():
                case function_exists('is_product_taxonomy') && is_product_taxonomy():
                    $type = 'shop';
                    $query = 'catalog';
                    break;
                case function_exists('is_product') && is_product():
                    $type = 'shop';
                    $query = 'single';
                    $individual_options = true;
                    break;
                case function_exists('is_cart') && is_cart():
                    $type = 'shop';
                    $query = 'cart';
                    break;
                case function_exists('is_checkout') && is_checkout():
                    $type = 'shop';
                    $query = 'checkout';
                    break;
                // ↑ woocommerce
                // ↓ Give-WP
                case 'give_forms' === $queried_post_type:
                    $type = 'give';
                    $query = is_single() ? 'single' : 'archive';
                    $individual_options = true;
                    break;
                // ↑ give-wp
                case 'post' === $queried_post_type:
                case 'team' === $queried_post_type:
                case 'portfolio' === $queried_post_type:
                    $type = $queried_post_type;
                    $query = is_single() ? 'single' : 'archive';
                    $individual_options = is_single() ? true : false;
                    break;
                case is_404():
                    $type = '404';
                    $query = 'page';
                    $individual_options = true;
                    break;
            }

            $this->post_type = $type ?? '';
            $this->post_query = $query ?? '';
            $this->post_has_individual_styles = $individual_options ?? false;
        }

        public function get_page_header_classes()
        {
            if (
                is_singular('portfolio')
                || function_exists('is_product') && is_product()
            ) {
                // Mentioned post types have individual options for fine customization
                switch (true) {
                    case (is_singular('portfolio')):
                        $post_type = 'portfolio';
                        break;
                    case (function_exists('is_product') && is_product()):
                        $post_type = 'shop';
                        break;
                }

                if ($this->RWMB_is_active()) {
                    $page_title_align = rwmb_meta('mb_page_title_align');
                    $breadcrumbs_align = rwmb_meta('mb_page_title_breadcrumbs_align');
                    $breadcrumbs_block = rwmb_meta('mb_page_title_breadcrumbs_block_switch');
                } else {
                    $page_title_align = BigHearts::get_option($post_type . '_single_title_align');
                    $breadcrumbs_align = BigHearts::get_option($post_type . '_single_breadcrumbs_align');
                    $breadcrumbs_block = BigHearts::get_option($post_type . '_single_breadcrumbs_block_switch');
                }
            } else {
                $page_title_align = BigHearts::get_mb_option('page_title_align', 'mb_page_title_switch', 'on');
                $breadcrumbs_align = BigHearts::get_mb_option('page_title_breadcrumbs_align', 'mb_page_title_switch', 'on');
                $breadcrumbs_block = BigHearts::get_mb_option('page_title_breadcrumbs_block_switch', 'mb_page_title_switch', 'on');
            }

            $breadcrumbs_align_class = $breadcrumbs_align != $page_title_align ? ' breadcrumbs_align_' . esc_attr($breadcrumbs_align) : '';
            $breadcrumbs_align_class .= !$breadcrumbs_block ? ' breadcrumbs_inline' : '';

            $pt_classes = ' page-header_align_' . (!empty($page_title_align) ? esc_attr($page_title_align) : 'left');
            $pt_classes .= $breadcrumbs_align_class;

            return esc_attr($pt_classes);
        }

        public function get_page_header_styles()
        {
            list(
                $bg_enabled,
                $bg_color,
                $min_height,
                $paddings,
                $margin_bottom
            ) = $this->get_style_options_list();

            $style = $bg_enabled ? $this->get_bg_image_style() : '';
            $style .= $bg_enabled && !empty($bg_color) ? 'background-color: ' . $bg_color . ';' : '';

            if ($bg_enabled && $min_height) {
                if (0 === intval($min_height)) {
                    $style .= ' min-height: auto;';
                } else {
                    $style .= ' min-height: ' . (int) $min_height . 'px;';
                }
            }

            $style .= '' !== $margin_bottom ? ' margin-bottom: ' . (int) $margin_bottom . 'px;' : '';

            $style .= isset($paddings['padding-top']) && '' !== $paddings['padding-top'] ? ' padding-top: ' . (int) $paddings['padding-top'] . 'px;' : '';
            $style .= isset($paddings['padding-bottom']) && '' !== $paddings['padding-bottom'] ? ' padding-bottom: ' . (int) $paddings['padding-bottom'] . 'px;' : '';

            return $style ? ' style="' . esc_attr($style) . '"' : '';
        }

        public function get_style_options_list()
        {
            if ($this->RWMB_is_active()) {
                return $this->get_RWMB_options();
            }

            if ($this->post_has_individual_styles) {
                return $this->get_individual_options();
            }

            return $this->get_default_options();
        }

        protected function get_RWMB_options()
        {
            $bg_enabled = rwmb_meta('mb_page_title_bg_switch');
            $bg_color = rwmb_meta('mb_page_title_bg')['color'];
            $min_height = rwmb_meta('mb_page_title_height');
            $paddings = rwmb_meta('mb_page_title_padding');
            $margin_bottom = rwmb_meta('mb_page_title_margin')['margin-bottom'] ?? '';

            return [
                $bg_enabled,
                $bg_color,
                $min_height,
                $paddings,
                $margin_bottom
            ];
        }

        protected function get_individual_options()
        {
            $bg_enabled = BigHearts::get_option($this->post_type . '_' . $this->post_query . '__page_title_bg_switch')
                ?? BigHearts::get_option('page_title_bg_switch');

            $min_height = BigHearts::get_option($this->post_type . '_' . $this->post_query . '__page_title_height')['height'] ?? '';
            $min_height = $min_height ?: 'px';
            $min_height = 'px' !== $min_height ? $min_height : BigHearts::get_option('page_title_height')['height'];

            $bg_color = BigHearts::get_option($this->post_type . '_' . $this->post_query . '__page_title_bg_image')['background-color'] ?? '';
            $bg_color = $bg_color ?: BigHearts::get_option('page_title_bg_image')['background-color'];

            $paddings = BigHearts::get_option($this->post_type . '_' . $this->post_query . '__page_title_padding') ?: [];
            if (!isset($paddings['padding-top']) || '' === $paddings['padding-top']) {
                $paddings['padding-top'] = BigHearts::get_option('page_title_padding')['padding-top'] ?? '';
            }
            if (!isset($paddings['padding-bottom']) || '' === $paddings['padding-bottom']) {
                $paddings['padding-bottom'] = BigHearts::get_option('page_title_padding')['padding-bottom'] ?? '';
            }

            $margin_bottom = BigHearts::get_option($this->post_type . '_' . $this->post_query . '__page_title_margin')['margin-bottom'] ?? '';
            $margin_bottom = '' !== $margin_bottom ? $margin_bottom : BigHearts::get_option('page_title_margin')['margin-bottom'];

            return [
                $bg_enabled,
                $bg_color,
                $min_height,
                $paddings,
                $margin_bottom
            ];
        }

        protected function get_default_options()
        {
            $bg_enabled = BigHearts::get_option('page_title_bg_switch');
            $bg_color = BigHearts::get_option('page_title_bg_image')['background-color'];
            $min_height = BigHearts::get_option('page_title_height')['height'];
            $paddings = BigHearts::get_option('page_title_padding');
            $margin_bottom = BigHearts::get_option('page_title_margin')['margin-bottom'] ?? '';

            return [
                $bg_enabled,
                $bg_color,
                $min_height,
                $paddings,
                $margin_bottom
            ];
        }

        public function get_bg_image_style()
        {
            if (is_404()) {
                return BigHearts::bg_render('404_page__page_title') ?: BigHearts::bg_render('page_title');
            }

            if (
                function_exists('is_woocommerce') && is_woocommerce()
                && !empty($bg_shop = BigHearts::bg_render($this->post_type . '_' . $this->post_query . '_page_title'))
            ) {
                return !is_product()
                    ? $bg_shop
                    : BigHearts::bg_render('shop_single_page_title');
            }

            if (
                $this->post_type
                && !empty($bg_cpt = BigHearts::bg_render($this->post_type . '_' . $this->post_query . '__page_title'))
            ) {
                return $bg_cpt;
            }

            return BigHearts::bg_render('page_title', 'mb_page_title_switch', 'on');
        }

        public function RWMB_is_active()
        {
            $id = !is_archive() ? get_queried_object_id() : 0;

            return class_exists('RWMB_Loader')
                && 0 !== $id
                && 'on' === rwmb_meta('mb_page_title_switch');
        }

        public function get_title_text()
        {
            if (is_home() || is_front_page()) {
                $title = '';
            } elseif (is_category()) {
                $title = single_cat_title('', false);
            } elseif (is_tag()) {
                $title = single_term_title('', false) . esc_html__(' Tag', 'bighearts');
            } elseif (is_date()) {
                $title = get_the_time('F Y');
            } elseif (is_author()) {
                $title = esc_html__('Author:', 'bighearts') . ' ' . get_the_author();
            } elseif (is_search()) {
                $title = esc_html__('Search', 'bighearts');
            } elseif (is_404()) {
                $this->page_title_tag = 'h1';
                $title = BigHearts::get_option('404_custom_title_switch')
                    ? esc_html(BigHearts::get_option('404_page_title_text'))
                    : esc_html__('Error Page', 'bighearts');
            } elseif (is_singular('portfolio')) {
                $title = BigHearts::get_option('portfolio_title_conditional')
                    ? esc_html__('Portfolio', 'bighearts')
                    : esc_html(get_the_title());
                $title = apply_filters('bighearts/page_title/portfolio_single', $title);
            } elseif (is_singular('team')) {
                $title = BigHearts::get_option('team_title_conditional')
                    ? esc_html__('Team', 'bighearts')
                    : esc_html(get_the_title());
                $title = apply_filters('bighearts/page_title/team_single', $title);
            } elseif (function_exists('is_product') && is_product()) {
                $title = BigHearts::get_option('shop_title_conditional')
                    ? esc_html__('Product', 'bighearts')
                    : esc_html(get_the_title());
                $title = apply_filters('bighearts/page_title/shop_single', $title);
            } elseif (is_archive()) {
                if (function_exists('is_shop')
                    && (is_shop() || is_product_category() || is_product_tag())
                ) {
                    $title = esc_html__('Shop', 'bighearts');
                } elseif (is_tax(['portfolio_tag', 'portfolio-category'])) {
                    $title = esc_html__('Portfolio', 'bighearts');
                    $title = apply_filters('bighearts/page_title/portfolio_archive', $title);
                } else {
                    $title = esc_html__('Archive', 'bighearts');
                }
            } else {
                global $post;
                if (!empty($post)) {
                    $this->page_title_tag = apply_filters('bighearts/page_title/page_title_tag', 'h1');
                    if ('post' == get_post_type($post)) {
                        $this->page_title_tag = apply_filters('bighearts/page_title/post_title_tag', 'div');

                        $title = BigHearts::get_option('blog_title_conditional')
                            ? esc_html__('Blog Post', 'bighearts')
                            : esc_html(get_the_title($post->ID));
                        $title = apply_filters('bighearts/page_title/blog_single', $title);
                    } else {
                        $title = esc_html(get_the_title($post->ID));
                    }
                } else {
                    $title = esc_html__('No Posts', 'bighearts');
                }
            }

            if (
                'on' == $this->mb_page_title_switch
                && !empty(rwmb_meta('mb_page_change_tile_switch'))
            ) {
                $custom_title = rwmb_meta('mb_page_change_tile');
                $title = !empty($custom_title) ? esc_html($custom_title) : '';
                $title = apply_filters('bighearts/page_title/custom_text', $title);
            }

            return $title;
        }

        public function get_title_html_tag()
        {
            $user_tag = BigHearts::get_mb_option('page_title_tag', 'mb_page_title_switch', 'on');
            $theme_tag = !empty($this->page_title_tag) ? $this->page_title_tag : 'div';

            return !empty($user_tag) && 'def' !== $user_tag ? $user_tag : apply_filters('bighearts/page_title/title_tag', $theme_tag);
        }

        public function get_title_style()
        {
            $pt_font = BigHearts::get_mb_option('page_title_font', 'mb_page_title_switch', 'on');
            $color = !empty($pt_font['color']) ? 'color: ' . $pt_font['color'] . ';' : '';
            $font_size = !empty($pt_font['font-size']) ? ' font-size: ' . (int) $pt_font['font-size'] . 'px;' : '';
            $line_height = !empty($pt_font['line-height']) ? ' line-height: ' . (int) $pt_font['line-height'] . 'px;' : '';

            return 'style="' . $color . $font_size . $line_height . '"';
        }

        public function is_breadcrumbs_enabled()
        {
            if (
                'post' === $this->single['type']
                && in_array($this->single['layout'], range(1, 2))
            ) {
                // Blog types 1-2 can be customized separately
                return 'on' == $this->mb_page_title_switch
                    ? rwmb_meta('mb_page_title_breadcrumbs_switch')
                    : BigHearts::get_option('blog_single_page_title_breadcrumbs_switch');
            }

            return BigHearts::get_mb_option('page_title_breadcrumbs_switch', 'mb_page_title_switch', 'on');
        }

        public function get_breadcrumbs()
        {
            $breadcrumbs_font = BigHearts::get_mb_option('page_title_breadcrumbs_font', 'mb_page_title_switch', 'on');
            $breadcrumbs_font_color = !empty($breadcrumbs_font['color']) ? 'color: ' . $breadcrumbs_font['color'] . ';' : '';
            $breadcrumbs_font_size = !empty($breadcrumbs_font['font-size']) ? ' font-size: ' . (int) $breadcrumbs_font['font-size'] . 'px;' : '';
            $breadcrumbs_font_height = !empty($breadcrumbs_font['line-height']) ? ' line-height: ' . (int) $breadcrumbs_font['line-height'] . 'px;' : '';
            $breadcrumbs_style = ' style="' . $breadcrumbs_font_color . $breadcrumbs_font_size . $breadcrumbs_font_height . '"';

            ob_start();
            get_template_part('templates/breadcrumbs');
            $breadcrums_html = ob_get_clean();

            return [
                $breadcrumbs_style,
                $breadcrums_html
            ];
        }

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }

    new BigHearts_Get_Page_Title();
}