<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin default hooks
 */
class EventM_Do_Actions {
	/**
	 * Initilize for class
	 */
	public function __construct() {
		add_action( 'event_magic_booking_confirmed', array( $this, 'booking_confirmed' ), 10, 1 );
		add_action( 'event_magic_booking_cancelled', array( $this, 'booking_cancelled' ), 10, 1 );
		add_action( 'wp_head', array( $this, 'twitter_meta' ) );
		add_filter( 'tribe_events_rewrite_rules_custom', array( $this, 'tribe_events_rewrite_rules_custom' ), 10, 3 );
		add_filter( 'event_magic_refund_booking', array( $this, 'refund_booking' ) );
		add_filter( 'em_cpt_event', array( $this, 'change_post_type_info' ) );
		// offline payment filter.
		add_filter( 'event_magic_add_offline_payment_response', array( $this, 'add_offline_payment_response' ), 10, 2 );
		// stripe payment filter.
		add_filter( 'event_magic_add_stripe_payment_response', array( $this, 'add_stripe_payment_response' ), 10, 2 );
		// without payment filter.
		add_filter( 'event_magic_add_without_payment_response', array( $this, 'add_without_payment_response' ), 10, 2 );
		// action on paypal data verification.
		add_filter( 'event_magic_paypal_data_verify_booking', array( $this, 'paypal_data_verify_booking' ), 10, 2 );
		// confirm booking order info.
		add_filter( 'event_magic_add_booking_order_info', array( $this, 'add_booking_order_info' ), 10, 2 );
		// global setting extensions at inactive state.
		add_action( 'event_magic_inactive_gs_settings', array( $this, 'inactive_extensions_gs_settings' ) );
		// price filter for multi price option
        add_filter('event_magic_load_calender_ticket_price', array($this, 'em_event_default_ticket_price_filter'), 10, 2);
        add_filter('event_magic_single_event_ticket_price', array($this, 'em_event_default_ticket_price_filter'), 10, 2);
        // template include for the SEO urls
        add_filter( 'template_include', array( $this, 'em_event_template'), 100 );
        // add theme name in body class
        add_action( 'body_class', array( $this, 'em_add_body_class' ), 1 );
	}

	/**
	 * Method call after seat booked
	 *
	 * @param Booking $booking Booking object.
	 */
	public function after_seat_booked( $booking ) {
		$event_service = EventM_Factory::get_service( 'EventM_Service' );
		$booking_service = EventM_Factory::get_service( 'EventM_Booking_Service' );
		$event = $event_service->load_from_db( $booking->event );
		if ( empty( $event->ticket_price ) ) { // Handling 0 price booking
			// Changing booking status.
			$booking->status = 'completed';
			$booking = $booking_service->save( $booking );
			EventM_Notification_Service::booking_confirmed( $booking->id );
		} else {
			if ( 'pending' === $booking->status ) {
				$booking->status = 'completed';
				$booking = $booking_service->save( $booking );
				EventM_Notification_Service::booking_confirmed( $booking->id );
			}
		}
	}

	/**
	 * Method call on cancel booking
	 *
	 * @param Booking $booking Booking object.
	 */
	public function booking_cancelled( $booking ) {
		EventM_Notification_Service::booking_cancel( $booking );
	}

	/**
	 * Methos call on confirm booking
	 *
	 * @param Booking $booking Booking object.
	 */
	public function booking_confirmed( $booking ) {
		EventM_Notification_Service::booking_confirmed( $booking );
	}

	/**
	 * Twitter meta
	 */
	public function twitter_meta() {
		global $wp;
		$event_id = isset( $_GET['event'] ) ? absint( $_GET['event'] ) : false;
		if ( empty( $event_id ) ) {
			return;
		}
		$setting_service = EventM_Factory::get_service( 'EventM_Setting_Service' );
		$options = $setting_service->load_model_from_db();
		if ( empty( $options->social_sharing ) ) {
			return;
		}
		$event_service = EventM_Factory::get_service( 'EventM_Service' );
		$event = $event_service->load_model_from_db( $event_id );
		if ( ! empty( $event->cover_image_id ) ) {
			$image = wp_get_attachment_image_url( $event->cover_image_id, 'full' );
		} else {
			$image = esc_url( EM_BASE_URL . 'includes/templates/images/dummy_image.png' );
		}
		echo '<meta name="twitter:card" content="' . esc_attr( 'summary_large_image' ) . '"/> ';
		echo "<meta name='twitter:title' content='" . esc_html( $event->name ) . "'>";
		echo "<meta name='twitter:description' content='" . esc_html( $event->description ) . "'>";
		echo "<meta name='twitter:image' content='" . esc_url( $image ) . "'>";
		$current_url = home_url( add_query_arg( array( 'event' => $event->id ), $wp->request ) );
		echo '<meta property="og:url" content="' . esc_url( $current_url ) . '" />';
		echo '<meta property="og:title" content="' . esc_html( $event->name ) . '" />';
		echo '<meta property="og:description" content="' . esc_html( $event->description ) . '"/>';
		echo '<meta property="og:image" content="' . esc_url( $image ) . '" />';
		echo '<meta property="og:type" content="' . esc_attr( 'article' ) . '"/>';
		echo '<meta property="fb:app_id" content="' . esc_attr( $options->fb_api_key ) . '">';
	}

	/**
	 * Removes rewrite rule for "/events" page.
	 *
	 * @param Rules   $rules Redirect Rules.
	 *
	 * @param Object  $obj object.
	 *
	 * @param Rewrite $wp_rewrite WP Rewrite.
	 */
	public function tribe_events_rewrite_rules_custom( $rules, $obj, $wp_rewrite ) {
		$found = false;
		foreach ( $rules as $rule => $rewrite ) {
			if ( preg_match( '/\?:events\)/', $rule ) ) {
				unset( $rules[ $rule ] );
				$found = true;
			}
		}
		if ( $found ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
		return $rules;
	}

	/**
	 * Method call on refund booking
	 *
	 * @param Booking $booking Booking object.
	 */
	public function refund_booking( $booking ) {
		if ( isset( $booking->order_info['payment_gateway'] ) ) {
			if ( 'paypal' == $booking->order_info['payment_gateway'] ) {
				$payment_service = EventM_Factory::get_service( 'EventM_Paypal_Service' );
				$payment_response = $payment_service->refund( $booking );
				if ( ! empty( $payment_response ) && 'Success' == $payment_response['ACK'] ) {
					$booking->status = 'refunded';
				}
			}
		}
		return $booking;
	}

	/**
	 * Resetting publicly queryable for 'events/?event=' frontend URLs.
	 *
	 * @param Info $info Post.
	 */
	public function change_post_type_info( $info ) {
		if ( ! empty( $info['publicly_queryable'] ) ) {
			$info['publicly_queryable'] = false;
		}
		return $info;
	}

	/**
	 * Offline payment response filter
	 *
	 * @param Offline response $offline_response Offline payment data.
	 *
	 * @param Orders $orders Order data.
	 */
	public function add_offline_payment_response( $offline_response, $orders ) {
		if ( isset( $orders[0]->fixed_event_price ) && ! empty( $orders[0]->fixed_event_price ) ) {
			$offline_response['fixed_event_price'] = $orders[0]->fixed_event_price;
		}
		if(isset($orders[0]->multi_price_option_data) && !empty($orders[0]->multi_price_option_data)){
            $offline_response['multi_price_option_data'] = $orders[0]->multi_price_option_data[0];
        }
		return $offline_response;
	}

	/**
	 * Offline payment response filter
	 *
	 * @param Stripe response $stripe_response Offline payment data.
	 *
	 * @param Orders $orders Order data.
	 */
	public function add_stripe_payment_response( $stripe_response, $orders ) {
		if ( isset( $orders[0]->fixed_event_price ) && ! empty( $orders[0]->fixed_event_price ) ) {
			$stripe_response['fixed_event_price'] = $orders[0]->fixed_event_price;
		}
		if(isset($orders[0]->multi_price_option_data) && !empty($orders[0]->multi_price_option_data)){
            $stripe_response['multi_price_option_data'] = $orders[0]->multi_price_option_data[0];
        }
		return $stripe_response;
	}

	/**
	 * Zero payment response filter
	 *
	 * @param Data   $data Zero payment data.
	 *
	 * @param Orders $orders Order data.
	 */
	public function add_without_payment_response( $data, $orders ) {
		if ( isset( $orders[0]->fixed_event_price ) && ! empty( $orders[0]->fixed_event_price ) ) {
			$data['fixed_event_price'] = $orders[0]->fixed_event_price;
		}
		if(isset($orders[0]->multi_price_option_data) && !empty($orders[0]->multi_price_option_data)){
            $data['multi_price_option_data'] = $orders[0]->multi_price_option_data[0];
        }
		return $data;
	}

	/**
	 * Paypal verification data filter
	 *
	 * @param Booking $booking Paypal booking data.
	 *
	 * @param All Order data $all_order_data Order data.
	 */
	public function paypal_data_verify_booking( $booking, $all_order_data ) {
		if ( isset( $all_order_data->fixed_event_price ) && ! empty( $all_order_data->fixed_event_price ) ) {
			$booking->order_info['fixed_event_price'] = $all_order_data->fixed_event_price;
		}
		if(isset($orders[0]->multi_price_option_data) && !empty($orders[0]->multi_price_option_data)){
            $booking->order_info['multi_price_option_data'] = $orders[0]->multi_price_option_data[0];
        }
		return $booking;
	}

	/**
	 * Add data in order info
	 *
	 * @param Orderinfo $order_info Order data.
	 *
	 * @param Data $data Booking data.
	 */
	public function add_booking_order_info( $order_info, $data ) {
		if ( isset( $data['fixed_event_price'] ) && ! empty( $data['fixed_event_price'] ) ) {
			$order_info['fixed_event_price'] = $data['fixed_event_price'];
		}
		return $order_info;
	}

	/**
	 * Settings for inactive extension. Used on Global Settings page.
	 */
	public function inactive_extensions_gs_settings() {
		$em = event_magic_instance();
		if ( ! in_array( 'analytics', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-events-analytics-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-analytics-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Events Analytics', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Analyze bookings data.', 'eventprime-event-calendar-management' ); ?></span><span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'file_import_export_events', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-file-ix-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-file-import-export-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Events Import Export', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Allow Events Import / Export.', 'eventprime-event-calendar-management' ); ?></span>
					<span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'em_mailpoet', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-mailpoet-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/event-mailpoet-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Mailpoet', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Integration with MailPoet Plugin.', 'eventprime-event-calendar-management' ); ?></span>
					<span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'guest-booking', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-guest-booking-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/event-guest-booking-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Guest Booking', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Configure guest bookings.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'recurring_events', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-recurring-events-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-recurring-events-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Recurring Events', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Create recurring events.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'attendees-list', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-attendees-lists-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-attendees-list-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Attendees List', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Publish frontend attendee lists.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'coupons', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-coupon-codes-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/coupon-code-extension-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Coupon Codes', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Create custom coupon codes.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'sponser', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-sponsors-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-sponser-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Event Sponsors', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Add sponsors to events.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'em_automatic_discounts', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-automatic-discounts-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/event-early-bird-discount-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Automatic Discounts', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Auto-apply conditional discounts.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'seating', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-live-seating-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/seating-integration-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Live Seating', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Add seat plan and seat selection.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'offline_payments', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-offline-payments-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-offline-payment.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Offline Payments', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Allow Offline Payments.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'stripe', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-stripe-payments-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-stripe-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Stripe Payments', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Accept payments via Stripe.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'wishlist', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension  ep-extension-modal' data-popup='ep-event-wishlist-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-save-events-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Event Wishlist', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Allow users to wish-list events.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'more_widgets', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-list-widget-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/event-more-widget-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Event List Widgets', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Display event data on frontend. ', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'event-comments', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-event-comments-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-event-comment-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Event Comments', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Allow users to post comments on event page.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'attendees_booking', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-event-attendee-booking-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-manually-attendees-booking.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Admin Attendee Bookings', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Create bookings from dashboard.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'google_import_export_events', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-google-ix-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-google-ie.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Google Import Export Events', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Integration with Google Calendar.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'woocommerce_integration', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-woo-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-woo-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'WooCommerce Integration', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Sell store products with your events!.', 'eventprime-event-calendar-management' ); ?></span>
					<span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'zoom-meetings', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-zoom-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-zoom-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'EventPrime Zoom Integration', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Organize and conduct virtual events seamlessly.', 'eventprime-event-calendar-management' ); ?></span>
					<span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'zapier-integration', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-event-zapier-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-zapier-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Zapier Integration', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Automate EventPrime Workflows using Zapier.', 'eventprime-event-calendar-management' ); ?></span>
					<span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'event_invoices', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-invoice-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-invoice-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'Invoices', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Generate and send PDF invoices.', 'eventprime-event-calendar-management' ); ?></span>
					<span class="ep-ext-label">Free </span>
				</div>
			</a>
			<?php
		}
		if ( ! in_array( 'sms_integration', $em->extensions, true ) ) {
			?>
			<a href='javascript:void(0)'>
				<div class='em-settings-box ep-inactive-extension ep-extension-modal' data-popup='ep-twilio-integration-ext' onclick='CallEPExtensionModal(this)'>
					<img class='em-settings-icon' ng-src="<?php echo esc_url( EM_BASE_URL . 'includes/admin/template/images/ep-sms-integration-icon.png' ); ?>">
					<div class='em-settings-description'></div>
					<div class='em-settings-subtitle'><?php esc_html_e( 'EventPrime Twilio Text Notifications', 'eventprime-event-calendar-management' ); ?></div>
					<span><?php esc_html_e( 'Use mobile text messaging to send notifications to users and admins.', 'eventprime-event-calendar-management' ); ?></span>
				</div>
			</a>
			<?php
		}
	}

	/**
     * event ticket price filter for multi price option
     */
    public function em_event_default_ticket_price_filter($ticket_price, $event) {
        if(is_numeric($event)){
            $eventId = $event;
        }
        else{
            $eventId = $event->id;
        }
        if(!empty($eventId) && is_numeric($eventId)) {
            global $wpdb;
            $price_table_name = get_ep_table_name('em_price_options');
            $get_price_data = $wpdb->get_row( "SELECT price, special_price, start_date, end_date FROM $price_table_name WHERE event_id = $eventId AND is_default = 1 AND status = 1" );
            $expire = 0;
            if(!empty($get_price_data)){
                if(!empty($get_price_data->start_date)){
                    if(em_time($get_price_data->start_date) > em_current_time_by_timezone()){
                        $expire = 1;
                    }
                }
                if(!empty($get_price_data->end_date)){
                    if(em_time($get_price_data->end_date) < em_current_time_by_timezone()){
                        $expire = 1;
                    }
                }
                if(empty($expire)){
                    $ticket_price = $get_price_data->price;
                    if(!empty($get_price_data->special_price) && $get_price_data->special_price > 0) {
                        $ticket_price = $get_price_data->special_price;
                    }
                }
            }
        }

        return $ticket_price;
    }
    /**
     * Action to include the templaete on SEO URLs
     */
    public static function em_event_template($template) {
        // We're in an embed post
        if(is_embed()) return $template;

        if(is_single()){
        	if(get_post_type() == 'em_event') {
	        	$template = locate_template('event.php');
	        	if($template == '') {
	            	$template = EM_BASE_DIR.'includes/templates/event.php';
	            }
	        } elseif(get_post_type() == 'em_performer'){
	        	$template = locate_template('single-performer.php');
	        	if($template == '') {
	            	$template = EM_BASE_DIR.'includes/templates/single-performer.php';
	            }
	        }
        } elseif(is_tax('em_event_organizer')) {
        	$template = locate_template('single-event-organizer.php');
        	if($template == '') {
            	$template = EM_BASE_DIR.'includes/templates/single-event-organizer.php';
            }
        }
        elseif(is_tax('em_venue')) {
        	$template = locate_template('single-venue.php');
        	if($template == '') {
            	$template = EM_BASE_DIR.'includes/templates/single-venue.php';
            }
        }
        elseif(is_tax('em_event_type')) {
        	$template = locate_template('single-event-type.php');
        	if($template == '') {
            	$template = EM_BASE_DIR.'includes/templates/single-event-type.php';
            }
        }
        
        return $template;
    }

    public function em_add_body_class( $classes ) {
    	$class = 'theme-' . get_template();

    	if( is_array( $classes ) ) {
    		$classes[] = $class;
    	} else{
    		$classes .= ' ' . $class . ' ';
    	}
    	return $classes;
    }
}

new EventM_Do_Actions();
