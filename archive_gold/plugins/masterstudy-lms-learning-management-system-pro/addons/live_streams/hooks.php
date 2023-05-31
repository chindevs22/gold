<?php

use MasterStudy\Lms\Validation\ConditionalRules;

add_filter(
	'masterstudy_lms_lesson_types',
	function ( $types ) {
		if ( apply_filters( 'stm_lms_live_stream_allowed', true ) ) {
			$types[] = 'stream';
		}

		return $types;
	}
);

add_filter(
	'masterstudy_lms_lesson_validation_rules',
	function ( $rules ) {
		$condition = function ( $data ) {
			return 'stream' === ( $data['type'] ?? null );
		};
		return array_merge(
			$rules,
			array(
				'stream_url'             => new ConditionalRules( $condition, 'required|url' ),
				'stream_start_date'      => new ConditionalRules( $condition, 'nullable|integer' ),
				'stream_start_time'      => new ConditionalRules( $condition, 'nullable|time' ),
				'stream_end_date'        => new ConditionalRules( $condition, 'nullable|integer' ),
				'stream_end_time'        => new ConditionalRules( $condition, 'nullable|time' ),
				'stream_start_timestamp' => new ConditionalRules( $condition, 'nullable|integer' ),
				'stream_end_timestamp'   => new ConditionalRules( $condition, 'nullable|integer' ),
			)
		);
	}
);

add_filter(
	'masterstudy_lms_lesson_hydrate',
	function ( $lesson, $meta ) {
		if ( 'stream' === $lesson['type'] ) {
			$lesson['stream_url']        = $meta['lesson_stream_url'][0] ?? null;
			$lesson['stream_start_date'] = (int) $meta['stream_start_date'][0] ?? null;
			$lesson['stream_start_time'] = $meta['stream_start_time'][0] ?? null;
			$lesson['stream_end_date']   = (int) $meta['stream_end_date'][0] ?? null;
			$lesson['stream_end_time']   = $meta['stream_end_time'][0] ?? null;

			if ( empty( $lesson['stream_start_date'] ) || empty( $lesson['stream_start_time'] ) ) {
				$lesson['stream_start_timestamp'] = null;
			} else {
				$lesson['stream_start_timestamp'] = strtotime( $lesson['stream_start_date'] . ' ' . $lesson['stream_start_time'] );
			}
			if ( empty( $lesson['stream_end_date'] ) || empty( $lesson['stream_end_time'] ) ) {
				$lesson['stream_end_timestamp'] = null;
			} else {
				$lesson['stream_end_timestamp'] = strtotime( $lesson['stream_end_date'] . ' ' . $lesson['stream_end_time'] );
			}
		}

		return $lesson;
	},
	10,
	2
);

add_action(
	'masterstudy_lms_lesson_save',
	function ( $data ) {
		if ( 'stream' !== ( $data['type'] ?? null ) ) {
			return;
		}

		if ( ! empty( $data['stream_start_timestamp'] ) ) {
			list ( $data['stream_start_date'], $data['stream_start_time'] ) = explode( ' ', gmdate( 'Y-m-d H:i', $data['stream_start_timestamp'] ) );
		}

		if ( ! empty( $data['stream_end_timestamp'] ) ) {
			list ( $data['stream_end_date'], $data['stream_end_time'] ) = explode( ' ', gmdate( 'Y-m-d H:i', $data['stream_end_timestamp'] ) );
		}

		$map = array(
			'stream_start_date' => 'stream_start_date',
			'stream_start_time' => 'stream_start_time',
			'stream_end_date'   => 'stream_end_date',
			'stream_end_time'   => 'stream_end_time',
			'lesson_stream_url' => 'stream_url',
		);

		foreach ( $map as $meta_key => $data_key ) {
			if ( isset( $data[ $data_key ] ) ) {
				update_post_meta( $data['id'], $meta_key, $data[ $data_key ] );
				$data[ $meta_key ] = $data[ $data_key ];
			}
		}

		do_action( 'stm_lms_save_lesson_after_validation', $data['id'], $data );
	}
);
