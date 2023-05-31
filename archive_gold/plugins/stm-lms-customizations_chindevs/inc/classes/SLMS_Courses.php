<?php

namespace StmLmsElementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MsLmsCoursesChild extends Widget_Base {

	use \MsLmsAddControls;

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
		wp_register_style( 'ms_lms_courses', STM_LMS_URL . 'assets/css/elementor-widgets/courses/courses.css', array(), STM_LMS_VERSION, false );
		wp_register_style( 'ms_lms_courses_select2', STM_LMS_URL . 'assets/vendors/select2.min.css', array(), STM_LMS_VERSION, false );
		wp_register_script( 'ms_lms_courses_select2', STM_LMS_URL . 'assets/vendors/select2.min.js', array( 'elementor-frontend' ), STM_LMS_VERSION, true );
		wp_register_script( 'ms_lms_courses', STM_LMS_URL . 'assets/js/elementor-widgets/courses/courses.js', array( 'elementor-frontend' ), STM_LMS_VERSION, true );
		wp_localize_script(
			'ms_lms_courses',
			'ms_lms_courses_archive_filter',
			array(
				'nonce'    => wp_create_nonce( 'filtering' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	public function get_style_depends() {
		return array( 'ms_lms_courses', 'ms_lms_courses_select2' );
	}

	public function get_script_depends() {
		return array( 'ms_lms_courses', 'ms_lms_courses_select2' );
	}

	public function get_name() {
		return 'ms_lms_courses';
	}

	public function get_title() {
		return esc_html__( 'Courses', 'masterstudy-lms-learning-management-system' );
	}

	public function get_icon() {
		return 'stmlms-courses-grid lms-icon';
	}

	public function get_categories() {
		return array( 'stm_lms' );
	}

	protected function register_controls() {
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/type.php';

        $this->start_controls_section(
            'lite_courses_section',
            array(
                'label'      => esc_html__( 'Lite courses', 'slms' ),
                'tab'        => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control( 'show_lite_courses',
            array(
                'type'               => Controls_Manager::SWITCHER,
                'label'              => esc_html__( 'Show only lite courses', 'slms' ),
                'label_on'           => esc_html__( 'Yes', 'masterstudy-lms-learning-management-system' ),
                'label_off'          => esc_html__( 'No', 'masterstudy-lms-learning-management-system' ),
                'frontend_available' => false,
            )
        );
		
		//ChinDevs code to add a control for showing a dropdown of options on the elementor widget
        $this->add_control( 'lite_category_name',
            array(
                'label'   => esc_html__( 'Lite Category Name', 'slms' ),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'event' => esc_html__( 'Event', 'slms' ),
                    'shravana_mangalam' => esc_html__( 'Shravana Mangalam', 'slms' ),
                    'webinar' => esc_html__( 'Webinar', 'slms' ),
                )
            )
        );

//        $this->add_control(
//            'show_lite_courses',
//            array(
//                'name'        => 'show_lite_courses',
//                'label'       => __( 'Show only lite courses', 'slms' ),
//                'type'        => \Elementor\Controls_Manager::SELECT2,
//                'label_block' => true,
//                'multiple'    => true,
//                'options'     => stm_lms_elementor_autocomplete_terms( 'stm_lms_course_taxonomy' ),
//            )
//        );

//        $this->add_control(
//            'taxonomy',
//            array(
//                'name'        => 'taxonomy',
//                'label'       => __( 'Select taxonomy', 'masterstudy-lms-learning-management-system' ),
//                'type'        => \Elementor\Controls_Manager::SELECT2,
//                'label_block' => true,
//                'multiple'    => true,
//                'options'     => stm_lms_elementor_autocomplete_terms( 'stm_lms_course_taxonomy' ),
//            )
//        );

        $this->end_controls_section();

		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/header.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/filter.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/sorting.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/pagination.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/card.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/content/popup.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/title.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/filter.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/filter-toggle.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/sorting.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/sorting-style-two.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/pagination-button.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/pagination-pages.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-excerpt.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-infoblock.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-image.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-category.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-title.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-progress.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-slots.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-rating.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-price.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-preview-button.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-status.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/card-wishlist.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-author-image.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-author-name.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-title.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-excerpt.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-slots.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-button.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-wishlist.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/popup-price.php';
		require STM_LMS_ELEMENTOR_WIDGETS . '/courses/styles/no-courses-find.php';
	}

	public function sorting_options( $value ) {
		$sorting_options = array(
			'date_high'  => esc_html__( 'Newest', 'masterstudy-lms-learning-management-system' ),
			'date_low'   => esc_html__( 'Oldest', 'masterstudy-lms-learning-management-system' ),
			'price_high' => esc_html__( 'Price high', 'masterstudy-lms-learning-management-system' ),
			'price_low'  => esc_html__( 'Price low', 'masterstudy-lms-learning-management-system' ),
			'rating'     => esc_html__( 'Overall Rating', 'masterstudy-lms-learning-management-system' ),
			'popular'    => esc_html__( 'Most Viewed', 'masterstudy-lms-learning-management-system' ),
		);
		if ( ! empty( $value ) ) {
			$array = array_filter(
				$sorting_options,
				function( $a ) use ( $value ) {
					return $a === $value;
				},
				ARRAY_FILTER_USE_KEY
			);
			return $array[ $value ];
		}
	}

	public function filter_options( $value ) {
		$filter_options = array(
			'category'    => array(
				'label'    => esc_html__( 'Category', 'masterstudy-lms-learning-management-system' ),
				'template' => 'category',
				'terms'    => get_terms(
					'stm_lms_course_taxonomy',
					array(
						'orderby' => 'count',
						'order'   => 'DESC',
						'parent'  => false,
					)
				),
			),
			'subcategory' => array(
				'label'    => esc_html__( 'Subcategory', 'masterstudy-lms-learning-management-system' ),
				'template' => 'subcategory',
			),
			'status'      => array(
				'label'    => esc_html__( 'Status', 'masterstudy-lms-learning-management-system' ),
				'template' => 'status',
				'statuses' => \STM_LMS_Course::get_all_statuses(),
			),
			'level'       => array(
				'label'    => esc_html__( 'Level', 'masterstudy-lms-learning-management-system' ),
				'template' => 'level',
				'levels'   => \STM_LMS_Helpers::get_course_levels(),
			),
			'rating'      => array(
				'label'    => esc_html__( 'Rating', 'masterstudy-lms-learning-management-system' ),
				'template' => 'rating',
				'ratings'  => $this->rating_options(),
			),
			'instructors' => array(
				'label'       => esc_html__( 'Instructors', 'masterstudy-lms-learning-management-system' ),
				'template'    => 'instructors',
				'instructors' => \STM_LMS_Instructor::get_instructors(
					array(
						'orderby' => 'registered',
						'order'   => 'DESC',
					),
				),
			),
			'price'       => array(
				'label'    => esc_html__( 'Price', 'masterstudy-lms-learning-management-system' ),
				'template' => 'price',
				'prices'   => array(
					'free_courses' => esc_html__( 'Free Courses', 'masterstudy-lms-learning-management-system' ),
					'paid_courses' => esc_html__( 'Paid Courses', 'masterstudy-lms-learning-management-system' ),
					'subscription' => esc_html__( 'Only Subscription', 'masterstudy-lms-learning-management-system' ),
				),
			),
		);
		if ( ! empty( $value ) ) {
			$array = array_filter(
				$filter_options,
				function( $a ) use ( $value ) {
					return $a === $value;
				},
				ARRAY_FILTER_USE_KEY
			);
			return $array[ $value ];
		}
	}

	public function rating_options() {
		return array(
			array(
				'rate'  => 4.5,
				'label' => esc_html__( '4.5 & up', 'masterstudy-lms-learning-management-system' ),
			),
			array(
				'rate'  => 4,
				'label' => esc_html__( '4.0 & up', 'masterstudy-lms-learning-management-system' ),
			),
			array(
				'rate'  => 3.5,
				'label' => esc_html__( '3.5 & up', 'masterstudy-lms-learning-management-system' ),
			),
			array(
				'rate'  => 3,
				'label' => esc_html__( '3.0 & up', 'masterstudy-lms-learning-management-system' ),
			),
		);
	}

	public function sorting_term_options( $value ) {
		$terms           = get_terms(
			'stm_lms_course_taxonomy',
			array(
				'orderby'    => 'count',
				'order'      => 'DESC',
				'hide_empty' => true,
			)
		);
		$sorting_options = array();
		foreach ( $terms as $term ) {
			$sorting_options[ $term->term_id ] = $term->name;
		}
		if ( ! empty( $value ) ) {
			$array = array_filter(
				$sorting_options,
				function( $a ) use ( $value ) {
					return $a === $value;
				},
				ARRAY_FILTER_USE_KEY
			);
			return $array[ $value ];
		}
	}

	protected function get_widget_data( $type ) {
		if ( ! empty( $type ) ) {
			$widgets_data = array(
				'courses-archive' => $this->courses_archive_data(),
				'courses-grid'    => $this->courses_grid_data(),
			);
			return $widgets_data[ $type ];
		}
	}

	protected function courses_grid_data() {

		$settings = $this->get_settings_for_display();

		/* sorting options */
		$sort_options = array();
		if ( ! empty( $settings['sort_by_cat'] ) ) {
			if ( ! empty( $settings['sort_options_by_cat'] ) ) {
				foreach ( $settings['sort_options_by_cat'] as $option ) {
					$sort_options[ intval( $option ) ] = $this->sorting_term_options( intval( $option ) );
				}
			}
		} else {
			if ( ! empty( $settings['sort_options'] ) ) {
				foreach ( $settings['sort_options'] as $option ) {
					$sort_options[ $option ] = $this->sorting_options( $option );
				}
			}
		}
		/* courses query */
		$posts_per_page = ( empty( $settings['cards_to_show_choice'] ) || 'all' === $settings['cards_to_show_choice'] ) ? -1 : intval( $settings['cards_to_show'] );
		$pp_featured    = ( empty( $settings['cards_to_show_choice_featured'] ) || 'all' === $settings['cards_to_show_choice_featured'] ) ? -1 : intval( $settings['cards_to_show_featured'] );
		$default_args   = array(
			'posts_per_page' => $posts_per_page,
			'meta_query'     => array(
				'relation' => 'AND',
				'featured' => array(
					'relation' => 'OR',
					array(
						'key'     => 'featured',
						'value'   => 'on',
						'compare' => '!=',
					),
					array(
						'key'     => 'featured',
						'compare' => 'NOT EXISTS',
					),
				),
			),
		);
		$featured_args  = array(
			'posts_per_page' => $pp_featured,
			'meta_query'     => array(
				array(
					'key'     => 'featured',
					'value'   => 'on',
					'compare' => '=',
				),
			),
		);
		
		//Chindevs edit to query on both is_lite_category and by category name
        if(isset($settings['show_lite_courses']) && !empty($settings['show_lite_courses'])) {
            $tax_query = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' => get_terms('stm_lms_course_taxonomy', array(
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'is_lite_category',
                            'value' => '1',
                        ),
                        array(
                            'key' => 'lite_category_name',
                            'value' => $settings['lite_category_name'],
                        ),
                    ),
                    'fields' => 'ids',
                )),
                'operator' => 'IN',
            );

            $default_args['tax_query'][] = $tax_query;
            $featured_args['tax_query'][] = $tax_query;
        }

//        if(isset($settings['taxonomy']) && !empty($settings['taxonomy'])) {
//            $tax_query = array(
//                'taxonomy' => 'stm_lms_course_taxonomy',
//                'field' => 'term_id',
//                'terms' => array_map('intval', $settings['taxonomy']),
//                'operator' => 'IN',
//            );
//
//            $default_args['tax_query'][] = $tax_query;
//            $featured_args['tax_query'][] = $tax_query;
//        }

		$default_args   = apply_filters( 'stm_lms_filter_courses', $default_args, array(), array(), $settings['sort_by'] );
		$featured_args  = apply_filters( 'stm_lms_filter_courses', $featured_args, array(), array(), $settings['sort_by'] );
		if ( 0 !== $posts_per_page ) {
			$courses = \STM_LMS_Courses::get_all_courses( $default_args );
		}
		if ( 0 !== $pp_featured ) {
			$featured_courses = \STM_LMS_Courses::get_all_courses( $featured_args );
		}
		$total_pages = 1;
		$total_posts = false;

		if ( ! empty( $courses ) && is_array( $courses ) ) {
			$total_pages = $courses['total_pages'];
			$total_posts = $courses['total_posts'];
		}

		/* all options for templates */
		$atts = array(
			'show_header'        => $settings['show_header'],
			'header_presets'     => $settings['header_presets'],
			'title_text'         => $settings['title_text'],
			'show_sorting'       => $settings['show_sorting'],
			'sort_presets'       => $settings['sort_presets'],
			'show_pagination'    => $settings['show_pagination'],
			'pagination_presets' => $settings['pagination_presets'],
			'courses'            => $courses['posts'] ?? array(),
			'featured_courses'   => $featured_courses['posts'] ?? array(),
			'sorting_data'       => array(
				'sort_options'        => $sort_options,
				'sort_by'             => $settings['sort_by'],
				'sort_options_by_cat' => $settings['sort_options_by_cat'] ?? '',
			),
			'pagination_data'    => array(
				'current_page'   => 1,
				'total_pages'    => $total_pages,
				'total_posts'    => $total_posts,
				'posts_per_page' => $posts_per_page,
				'offset'         => $posts_per_page,
			),
		);

		return $atts;
	}

	protected function courses_archive_data() {

		$settings = $this->get_settings_for_display();

		/* sorting options */
		$sort_options = array();
		if ( ! empty( $settings['sort_options'] ) ) {
			foreach ( $settings['sort_options'] as $option ) {
				$sort_options[ $option ] = $this->sorting_options( $option );
			}
		}

		/* filter options */
		$filter_options = array();
		if ( ! empty( $settings['filter_options'] ) ) {
			foreach ( $settings['filter_options'] as $option ) {
				$filter_options[ $option ] = $this->filter_options( $option );
			}
		}

		/* courses query */
		$posts_per_page = ( empty( $settings['cards_to_show_choice'] ) || 'all' === $settings['cards_to_show_choice'] ) ? -1 : intval( $settings['cards_to_show'] );
		$pp_featured    = ( empty( $settings['cards_to_show_choice_featured'] ) || 'all' === $settings['cards_to_show_choice_featured'] ) ? -1 : intval( $settings['cards_to_show_featured'] );
		$terms          = ( isset( $_GET['terms'] ) ) ? \STM_LMS_Helpers::array_sanitize( wp_unslash( $_GET['terms'] ) ) : array();
		$metas          = \STM_LMS_Courses::get_query_metas_from_url();
		$current_page   = ( isset( $_GET['current_page'] ) && 'pagination-style-1' !== $settings['pagination_presets'] ) ? intval( $_GET['current_page'] ) : 1;
		$sort_by        = ( isset( $_GET['sort'] ) ) ? sanitize_text_field( $_GET['sort'] ) : $settings['sort_by'];
		$default_args   = array(
			'posts_per_page' => $posts_per_page,
			'meta_query'     => array(
				'relation' => 'AND',
				'featured' => array(
					'relation' => 'OR',
					array(
						'key'     => 'featured',
						'value'   => 'on',
						'compare' => '!=',
					),
					array(
						'key'     => 'featured',
						'compare' => 'NOT EXISTS',
					),
				),
			),
		);
		if ( ! empty( $current_page ) ) {
			$default_args['paged'] = $current_page;
		}
		if ( ! empty( $metas ) || ! empty( $terms ) ) {
			$default_args['meta_query']['featured'] = array();
		}

		$default_args     = apply_filters( 'stm_lms_filter_courses', $default_args, $terms, $metas, $sort_by );
		$featured_args    = array(
			'posts_per_page' => $pp_featured,
			'meta_query'     => array(
				array(
					'key'     => 'featured',
					'value'   => 'on',
					'compare' => '=',
				),
			),
		);

		//Chindevs edit to query on both is_lite_category and by category name
        if(isset($settings['show_lite_courses']) && !empty($settings['show_lite_courses'])) {
            $tax_query = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' => get_terms('stm_lms_course_taxonomy', array(
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'is_lite_category',
                            'value' => '1',
                        ),
                        array(
                            'key' => 'lite_category_name',
                            'value' => $settings['lite_category_name'],
                        ),
                    ),
                    'fields' => 'ids',
                )),
                'operator' => 'IN',
            );

            $default_args['tax_query'][] = $tax_query;
            $featured_args['tax_query'][] = $tax_query;
        }

//        if(isset($settings['taxonomy']) && !empty($settings['taxonomy'])) {
//            $tax_query = array(
//                'taxonomy' => 'stm_lms_course_taxonomy',
//                'field' => 'term_id',
//                'terms' => array_map('intval', $settings['taxonomy']),
//                'operator' => 'IN',
//            );
//
//            $default_args['tax_query'][] = $tax_query;
//            $featured_args['tax_query'][] = $tax_query;
//        }

		$courses          = \STM_LMS_Courses::get_all_courses( $default_args );
		$featured_courses = \STM_LMS_Courses::get_all_courses( $featured_args );
		$total_pages      = 1;
		$total_posts      = false;

		if ( ! empty( $courses ) && is_array( $courses ) ) {
			$total_pages = $courses['total_pages'];
			$total_posts = $courses['total_posts'];
		}

		/* all options for templates */
		$atts = array(
			'show_header'        => $settings['show_header'],
			'header_presets'     => $settings['header_presets'],
			'title_text'         => $settings['title_text'],
			'show_sorting'       => $settings['show_sorting'],
			'show_filter'        => $settings['show_filter'],
			'sort_presets'       => $settings['sort_presets'],
			'show_pagination'    => $settings['show_pagination'],
			'pagination_presets' => $settings['pagination_presets'],
			'courses'            => ( isset( $courses['posts'] ) ) ? $courses['posts'] : array(),
			'featured_courses'   => ( isset( $featured_courses['posts'] ) ) ? $featured_courses['posts'] : array(),
			'filter_data'        => array(
				'filter_position' => $settings['filter_position'],
				'filter_options'  => $filter_options,
				'terms'           => $terms,
				'metas'           => $metas,
			),
			'sorting_data'       => array(
				'sort_options' => $sort_options,
				'sort_by'      => $sort_by,
			),
			'pagination_data'    => array(
				'current_page'   => $current_page,
				'total_pages'    => $total_pages,
				'total_posts'    => $total_posts,
				'posts_per_page' => $posts_per_page,
				'offset'         => $posts_per_page,
			),
		);

		return $atts;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		/* ajax turn off for editor mode */
		if ( Plugin::$instance->editor->is_edit_mode() ) {
			?>
			<script>
				let edit_mode = true;
			</script>
			<?php
		}

		/* card's & popup's slots */
		$meta_slots = array(
			'card_slot_1'  => $settings['card_slot_1'],
			'card_slot_2'  => $settings['card_slot_2'],
			'card_slot_3'  => $settings['card_slot_3'],
			'card_slot_4'  => $settings['card_slot_4'],
			'popup_slot_1' => $settings['popup_slot_1'],
			'popup_slot_2' => $settings['popup_slot_2'],
			'popup_slot_3' => $settings['popup_slot_3'],
		);

		/* card options for templates */
		$atts = array(
			'course_card_presets' => $settings['course_card_presets'],
//			'taxonomy' => $settings['taxonomy'],
			'show_lite_courses' => $settings['show_lite_courses'],
			//Chindevs code,
			'lite_category_name' => $settings['lite_category_name'],
			'meta_slots'          => $meta_slots,
			'card_data'           => array(
				'show_popup'        => $settings['show_popup'],
				'show_category'     => $settings['show_category'],
				'show_progress'     => $settings['show_progress'],
				'show_excerpt'      => $settings['show_excerpt'],
				'show_divider'      => $settings['show_divider'],
				'show_rating'       => $settings['show_rating'],
				'show_price'        => $settings['show_price'],
				'show_slots'        => $settings['show_slots'],
				'show_wishlist'     => $settings['show_wishlist'],
				'status_presets'    => $settings['status_presets'],
				'status_position'   => $settings['status_position'],
				'featured_position' => $settings['featured_position'],
			),
			'popup_data'          => array(
				'popup_show_author_name'  => $settings['popup_show_author_name'],
				'popup_show_author_image' => $settings['popup_show_author_image'],
				'popup_show_wishlist'     => $settings['popup_show_wishlist'],
				'popup_show_price'        => $settings['popup_show_price'],
				'popup_show_excerpt'      => $settings['popup_show_excerpt'],
				'popup_show_slots'        => $settings['popup_show_slots'],
			),
		);

		$widget_atts = $this->get_widget_data( $settings['type'] );
		$atts        = wp_parse_args( $widget_atts, $atts );

        set_query_var( 'show_lite_courses', $settings['show_lite_courses'] );
		//ChinDevs code to add this variable as query var
		set_query_var( 'lite_category_name', $settings['lite_category_name'] );
		\STM_LMS_Templates::show_lms_template( "elementor-widgets/courses/{$settings['type']}/main", $atts );
	}

	protected function content_template() {
	}
}
