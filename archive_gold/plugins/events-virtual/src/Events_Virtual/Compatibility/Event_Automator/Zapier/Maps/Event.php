<?php
/**
 * The Virtual Event Integration with Zapier service provider.
 *
 * @since 1.13.5
 * @package TEC\Events_Virtual\Compatibility\Event_Automator\Zapier\Maps
 */

namespace TEC\Events_Virtual\Compatibility\Event_Automator\Zapier\Maps;

use WP_Post;

/**
 * Class Event
 *
 * @since 1.13.5
 *
 * @package TEC\Events_Virtual\Compatibility\Event_Automator\Zapier\Maps
 */
class Event {

	/**
	 * Filters the event details sent to a 3rd party.
	 *
	 * @since 1.13.5
	 *
	 * @param array<string|mixed> An array of event details.
	 * @param WP_Post An instance of the event WP_Post object.
	 *
	 * @return array<string|mixed> An array of event details.
	 */
	public function add_virtual_fields( array $next_event, WP_Post $event ) {
		if ( ! $event->virtual ) {
			return $next_event;
		}

		// VE Fields.
		$ve_fields = [
			'virtual'                    => true,
			'virtual_video_source'       => $event->virtual_video_source,
			'virtual_event_type'         => $event->virtual_event_type,
			'virtual_autodetect_source'  => $event->virtual_autodetect_source,
			'virtual_url'                => $event->virtual_url,
			'virtual_meeting_provider'   => $event->virtual_meeting_provider,
			'virtual_provider_details'   => [],
			'virtual_embed_video'        => $event->virtual_embed_video,
			'virtual_linked_button'      => $event->virtual_linked_button,
			'virtual_linked_button_text' => $event->virtual_linked_button_text,
			'virtual_show_embed_at'      => $event->virtual_show_embed_at,
			'virtual_show_embed_to'      => $event->virtual_show_embed_to,
			'virtual_show_on_event'      => $event->virtual_show_on_event,
			'virtual_show_on_views'      => $event->virtual_show_on_views,
			'virtual_show_lead_up'       => $event->virtual_show_lead_up,
			'virtual_rsvp_email_link'    => $event->virtual_rsvp_email_link,
			'virtual_ticket_email_link'  => $event->virtual_ticket_email_link,
			'virtual_is_immediate'       => $event->virtual_is_immediate,
			'virtual_is_linkable'        => $event->virtual_is_linkable,
			'virtual_should_show_embed'  => $event->virtual_should_show_embed,
			'virtual_should_show_link'   => $event->virtual_should_show_link,
		];

		$next_event = array_merge( $next_event, $ve_fields );

		/**
		 * Filters the event details map sent to a 3rd party.
		 *
		 * @since 1.13.5
		 *
		 * @param array<string|mixed> $next_event An array of event details.
		 * @param WP_Post             $event      An instance of the event WP_Post object.
		 */
		$next_event = apply_filters( 'tec_virtual_automator_map_event_details', $next_event, $event );

		return $next_event;
	}
}
