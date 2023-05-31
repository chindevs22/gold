<?php

namespace MasterStudy\Lms\Repositories;

use JsonException;
use RuntimeException;

final class FaqRepository {
	private const META_KEY = 'faq';

	/**
	 * @return array<array{question: string, answer: string}>
	 * @throws RuntimeException
	 */
	public function find_for_course( int $course_id ): array {
		$data = get_post_meta( $course_id, self::META_KEY, true );
		if ( ! $data ) {
			return array();
		}

		return $this->unserialize( $data );
	}

	/**
	 * @param array<array{question: string, answer: string}> $faq
	 *
	 * @throws RuntimeException
	 */
	public function save( int $course_id, array $faq ): void {
		$data = $this->serialize( $faq );

		update_post_meta( $course_id, self::META_KEY, $data );
	}

	/**
	 * @throws RuntimeException
	 */
	private function unserialize( string $data ): array {
		try {
			return json_decode( $data, true, 512, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
		} catch ( JsonException $e ) {
			throw new RuntimeException( 'Failed to unserialize faq data: ' . $e->getMessage() );
		}
	}

	/**
	 * @throws RuntimeException
	 */
	private function serialize( array $faq ): string {
		try {
			return wp_json_encode( $faq, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
		} catch ( JsonException $e ) {
			throw new RuntimeException( 'Failed to serialize data before save: ' . $e->getMessage() );
		}
	}
}
