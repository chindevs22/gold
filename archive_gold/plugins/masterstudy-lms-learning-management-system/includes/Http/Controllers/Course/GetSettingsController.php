<?php

namespace MasterStudy\Lms\Http\Controllers\Course;

use MasterStudy\Lms\Http\Serializers\CertificateSerializer;
use MasterStudy\Lms\Http\Serializers\CourseCategorySerializer;
use MasterStudy\Lms\Http\Serializers\CourseLevelSerializer;
use MasterStudy\Lms\Http\Serializers\CourseSerializer;
use MasterStudy\Lms\Plugin\Taxonomy;
use MasterStudy\Lms\Repositories\CertificateRepository;
use MasterStudy\Lms\Repositories\CourseRepository;
use WP_REST_Request;

class GetSettingsController {
	private CourseRepository $course_repository;
	private CertificateRepository $certificate_repository;

	public function __construct() {
		$this->course_repository      = new CourseRepository();
		$this->certificate_repository = new CertificateRepository();
	}

	public function __invoke( $course_id, WP_REST_Request $request ): \WP_REST_Response {
		$course = $this->course_repository->find( $course_id );

		return new \WP_REST_Response(
			array(
				'categories'     => ( new CourseCategorySerializer() )->collectionToArray( Taxonomy::all_categories() ),
				'certificates'   => ( new CertificateSerializer() )->collectionToArray( $this->certificate_repository->get_all() ),
				'course'         => ( new CourseSerializer() )->toArray( $course ),
				'levels'         => ( new CourseLevelSerializer() )->collectionToArray( \STM_LMS_Helpers::get_course_levels() ),
				'featured_quota' => \STM_LMS_Subscriptions::get_featured_quota(),
			)
		);
	}
}
