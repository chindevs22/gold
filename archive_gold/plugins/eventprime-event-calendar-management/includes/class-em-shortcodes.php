<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EM_Shortcodes' ) ) {
	/**
	 * Default shortcodes
	 */
	class EM_Shortcodes {
		/**
		 * Instance
		 *
		 * @var Instance
		 */
		private static $instance = null;
		/**
		 * Codes
		 *
		 * @var Codes
		 */
		public $codes = array(
			'em_events'            => 'load_events',
			'em_performers'        => 'load_performers',
			'em_event_types'       => 'load_event_types',
			'em_sites'             => 'load_venues',
			'em_booking'           => 'load_booking',
			'em_profile'           => 'load_profile',
			'em_event'             => 'load_single_event',
			'em_event_type'        => 'load_single_event_type',
			'em_performer'         => 'load_single_performer',
			'em_event_site'        => 'load_single_venue',
			'em_event_submit_form' => 'load_event_submit_form',
			'em_booking_details'   => 'load_event_booking_details',
			'em_event_organizers'  => 'load_event_organizers',
			'em_event_organizer'   => 'load_single_event_organizer',
		);

		/**
		 * Class instance
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		/**
		 * Class constructor
		 */
		private function __construct() {
			$this->add_shortcodes();
		}
		/**
		 * Load shortcodes
		 */
		private function add_shortcodes() {
			foreach ( (array) $this->codes as $code => $function ) {
				add_shortcode( $code, array( $this, $function ) );
			}
		}
		/**
		 * Load profile
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_profile( $atts ) {
			$profile_shortcode = apply_filters( 'ep_load_profile', false, $atts );
			if ( empty( $profile_shortcode ) ) {
				ob_start();
				include 'templates/user_profile.php';
				return ob_get_clean();
			} else {
				return do_shortcode( $profile_shortcode );
			}
		}

		/**
		 * Action to load single event page
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_single_event( $atts ) {
			ob_start();
			$section_id = $sectionClass = '';
            if(isset($atts['section_id'])){
                $section_id = $atts['section_id'];
            }
            if(isset($atts['class'])){
                $sectionClass = $atts['class'];
            }
			$atts                 = array_change_key_case( (array) $atts, CASE_LOWER );
			$event_id             = absint( $atts['id'] );
			$post                 = get_post( $event_id );
			$hide_upcoming_events = em_global_settings( 'shortcode_hide_upcoming_events' );
			if ( isset( $atts['upcoming'] ) ) {
				$hide_upcoming_events = 1;
				if ( 1 === $atts['upcoming'] ) {
					$hide_upcoming_events = 0;
				}
			}
			if ( ! empty( $post ) ) {
				if ( 'em_event' === $post->post_type && 'trash' !== $post->post_status ) {
					if ( 'draft' === $post->post_status && ! current_user_can( 'manage_options' ) ) {
						esc_html_e( 'This event is currently in draft state', 'eventprime-event-calendar-management' );
					} else {
						$event_id = $post->ID;
						include 'templates/event.php';
					}
				}
			}
			return ob_get_clean();
		}

		/**
		 * Load events via shortcode
		 *
		 * @param Atts $atts Attributes.
		 *
		 * @param Content $content Content.
		 *
		 * @param Tag $tag Tag.
		 */
		public function load_events( $atts, $content, $tag ) {
			ob_start();
			$event_id = isset( $atts['id'] ) ? $atts['id'] : absint( event_m_get_param( 'event' ) );
			$section_id = $column_class = '';
            if ( isset( $atts['section_id'] ) ) {
                $section_id = $atts['section_id'];
            }
            if ( isset( $atts['column_class'] ) ) {
                $column_class = $atts['column_class'];
            }
			if ( ! empty( $event_id ) && ! isset( $atts['view'] ) ) {
				$post = get_post( $event_id );
				if ( ! empty( $post ) && 'em_event' === $post->post_type && 'trash' !== $post->post_status ) {
					if ( 'draft' === $post->post_status && ! current_user_can( 'manage_options' ) ) {
						esc_html_e( 'This event is currently in draft state', 'eventprime-event-calendar-management' );
					} else {
						include 'templates/event.php';
					}
				}
			} else {
				$gs_service      = EventM_Factory::get_service( 'EventM_Setting_Service' );
				$global_settings = $gs_service->load_model_from_db();
				$atts            = array_change_key_case( (array) $atts, CASE_LOWER );
				$cal_args        = array(
					'view'      => $global_settings->default_cal_view,
					'types'     => array(),
					'sites'     => array(),
					'upcoming'  => '',
					'show'      => '',
					'recurring' => '',
					'individual_events' => '',
					'id' => '',
				);
				$events_atts = shortcode_atts( $cal_args, $atts, $tag );
				if ( 'card' === $global_settings->default_cal_view && 'masonry' === $events_atts['view']  && 'list' === $events_atts['view'] ) {
					$cal_args['show'] = '';
				}
				if ( ! in_array( $events_atts['view'], array( 'card', 'list', 'month', 'week', 'day', 'masonry', 'slider' ), true ) ) {
					$events_atts['view'] = $global_settings->default_cal_view;
				}
				if ( ! is_array( $events_atts['types'] ) ) {
					$events_atts['types'] = explode( ',', $events_atts['types'] );
				}
				if ( ! is_array( $events_atts['sites'] ) ) {
					$events_atts['sites'] = explode( ',', $events_atts['sites'] );
				}
				if ( 'card' !== $events_atts['view'] && 'masonry' !== $events_atts['view']  &&  'list' !== $events_atts['view'] ) {
					$events_atts['show'] = '';
				}
				if( isset( $events_atts['id'] ) ){
					$id = $events_atts['id'];
				}
				include 'templates/events.php';
			}
			return ob_get_clean();
		}
		/**
		 * Single performer page
		 *
		 * @param Atts $atts Attribute.
		 */
		public function load_single_performer( $atts ) {
			ob_start();
			$atts                 = array_change_key_case( (array) $atts, CASE_LOWER );
			$performer_id         = absint( $atts['id'] );
			$post                 = get_post( $performer_id );
			$hide_upcoming_events = em_global_settings( 'shortcode_hide_upcoming_events' );
			if ( isset( $atts['upcoming'] ) ) {
				$hide_upcoming_events = 1;
				if ( 1 === $atts['upcoming'] ) {
					$hide_upcoming_events = 0;
				}
			}
			if ( empty( $post ) || 'em_performer' !== $post->post_type || 'trash' === $post->post_status ) {
				esc_html_e( 'No such performer found', 'eventprime-event-calendar-management' );
				return ob_get_clean();
			}

			include 'templates/single-performer.php';
			return ob_get_clean();
		}
		/**
		 * Load performers
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_performers( $atts ) {
			ob_start();
			$atts = array_change_key_case( (array) $atts, CASE_LOWER );
			$performer_id = absint( event_m_get_param( 'performer' ) );
			if ( ! empty( $performer_id ) ) {
				$post = get_post( $performer_id );
				if ( ! empty( $post ) && 'em_performer' === $post->post_type && 'trash' !== $post->post_status ) {
					include 'templates/single-performer.php';
				}
			} else {
				include 'templates/performers.php';
			}
			return ob_get_clean();
		}
		/**
		 * Load venues
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_venues( $atts ) {
			ob_start();
			$atts = array_change_key_case( (array) $atts, CASE_LOWER );
			$venue_id = absint( event_m_get_param( 'venue' ) );
			$service = EventM_Factory::get_service( 'EventM_Venue_Service' );
			if ( ! empty( $venue_id ) || ! empty( $atts['id'] ) ) {
				$venue_id = !empty( $venue_id ) ? $venue_id : $atts['id'];
				$venue = $service->load_model_from_db( $venue_id );
				if ( is_wp_error( $venue ) || empty( $venue ) || is_null( $venue ) ) {
					esc_html_e( 'No such site exists.', 'eventprime-event-calendar-management' );
					return ob_get_clean();
				} else {
					include 'templates/single-venue.php';
				}
			} else {
				include 'templates/venues.php';
			}
			return ob_get_clean();
		}
		/**
		 * Load single venue page
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_single_venue( $atts ) {
			ob_start();
			$atts                 = array_change_key_case( (array) $atts, CASE_LOWER );
			$venue_page_id        = em_global_settings( 'venues_page' );
			$venue_id             = absint( $atts['id'] );
			$service              = EventM_Factory::get_service( 'EventM_Venue_Service' );
			$venue                = $service->load_model_from_db( $venue_id );
			$hide_upcoming_events = em_global_settings( 'shortcode_hide_upcoming_events' );
			if ( isset( $atts['upcoming'] ) ) {
				$hide_upcoming_events = 1;
				if ( 1 === $atts['upcoming'] ) {
					$hide_upcoming_events = 0;
				}
			}
			if ( empty( $venue->id ) ) {
				esc_html_e( 'No such site exists.', 'eventprime-event-calendar-management' );
			} else {
				include_once 'templates/single-venue.php';
			}
			return ob_get_clean();
		}
		/**
		 * Load booking
		 */
		public function load_booking() {
			ob_start();
			if ( is_user_logged_in() ) { // Removing all the temporary bookings for current user.
				$booking_service = EventM_Factory::get_service( 'EventM_Booking_Service' );
				$user            = wp_get_current_user();
				$booking_service->remove_tmp_bookings_for_user( $user->ID );
			}
			include 'templates/booking.php';
			return ob_get_clean();
		}
		/**
		 * Load event types
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_event_types( $atts ) {
			ob_start();
			$type_id            = absint( event_m_get_param( 'type' ) );
			$event_type_service = EventM_Factory::get_service( 'EventTypeM_Service' );
			if ( ! empty( $type_id ) || ! empty( $atts['id'] ) ) {
				$type_id = !empty( $type_id ) ? $type_id : $atts['id'];
				$type = $event_type_service->load_model_from_db( $type_id );
				if ( is_wp_error( $type ) || empty( $type ) || is_null( $type ) ) {
					esc_html_e( 'No such Event Type exists', 'eventprime-event-calendar-management' );
					return ob_get_clean();
				} else {
					include 'templates/single-event-type.php';
				}
			} else {
				include 'templates/event_types.php';
			}
			return ob_get_clean();
		}
		/**
		 * Load single event type page
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_single_event_type( $atts ) {
			ob_start();
			$atts                 = array_change_key_case( (array) $atts, CASE_LOWER );
			$type_id              = absint( $atts['id'] );
			$event_type_service   = EventM_Factory::get_service( 'EventTypeM_Service' );
			$hide_upcoming_events = em_global_settings( 'shortcode_hide_upcoming_events' );
			if ( isset( $atts['upcoming'] ) ) {
				$hide_upcoming_events = 1;
				if ( 1 === $atts['upcoming'] ) {
					$hide_upcoming_events = 0;
				}
			}
			if ( ! empty( $type_id ) ) {
				$type = $event_type_service->load_model_from_db( $type_id );
				if ( is_wp_error( $type ) || empty( $type ) || is_null( $type ) ) {
					esc_html_e( 'No such Event Type exists', 'eventprime-event-calendar-management' );
					return ob_get_clean();
				} else {
					include 'templates/single-event-type.php';
				}
			} else {
				include 'templates/event_types.php';
			}
			return ob_get_clean();
		}
		/**
		 * Load frontend event submission form
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_event_submit_form( $atts ) {
			ob_start();
			include 'templates/event_submit_form.php';
			return ob_get_clean();
		}

		/**
		 * Load booking details
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_event_booking_details( $atts ) {
			ob_start();
			include 'templates/event_booking_details.php';
			return ob_get_clean();
		}

		/**
		 * Load Event Organizers
		 *
		 * @param Atts $atts Attributes.
		 */
		public function load_event_organizers($atts) {
            ob_start();
            $organizer_id = absint(event_m_get_param('organizer'));
            $event_organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');
            if ( ! empty( $organizer_id ) || ! empty( $atts['id'] ) ) {
				$organizer_id = !empty( $organizer_id ) ? $organizer_id : $atts['id'];
                $organizer = $event_organizer_service->load_model_from_db($organizer_id);
                if (is_wp_error($organizer) || empty($organizer) || is_null($organizer)) {
                    _e('No Such Event Organizer Exists', 'eventprime-event-calendar-management');
                    return ob_get_clean();
                } else {
                    include('templates/single-event-organizer.php');
                }
            } else {
                include('templates/event_organizers.php');
            }
            return ob_get_clean();
        }

        /**
		 * Load Single Event Orrganizer
		 *
		 * @param Atts $atts Attributes.
		 */
        public function load_single_event_organizer($atts) {
            ob_start();
            $atts = array_change_key_case((array)$atts, CASE_LOWER);
            $organizer_id = absint($atts['id']);
            $event_organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');
            $hide_upcoming_events = em_global_settings('shortcode_hide_upcoming_events');
            if(isset($atts['upcoming'])){
                $hide_upcoming_events = 1;
                if($atts['upcoming'] == 1){
                    $hide_upcoming_events = 0;
                }
            }
            if (!empty($organizer_id)) {
                $organizer = $event_organizer_service->load_model_from_db($organizer_id);
                if (is_wp_error($organizer) || empty($organizer) || is_null($organizer)) {
                    _e('No Such Event Organizer Exists', 'eventprime-event-calendar-management');
                    return ob_get_clean();
                } else {
                    include('templates/single-event-organizer.php');
                }
            } else {
                include('templates/event_organizers.php');
            }
            return ob_get_clean();
        }
	}
}

EM_Shortcodes::get_instance();
