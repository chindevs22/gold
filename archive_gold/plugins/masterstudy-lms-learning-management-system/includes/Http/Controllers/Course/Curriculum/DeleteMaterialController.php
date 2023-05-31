<?php

namespace MasterStudy\Lms\Http\Controllers\Course\Curriculum;

use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Repositories\CourseRepository;
use MasterStudy\Lms\Repositories\CurriculumMaterialRepository;
use MasterStudy\Lms\Validation\Validator;
use WP_REST_Request;

class DeleteMaterialController {
	public function __invoke( $course_id, WP_REST_Request $request ): \WP_REST_Response {
		if ( ! ( new CourseRepository() )->exists( $course_id ) ) {
			return WpResponseFactory::not_found();
		}

		$validator = new Validator(
			$request->get_json_params(),
			array(
				'id' => 'required|integer',
			)
		);

		if ( $validator->fails() ) {
			return new \WP_REST_Response(
				array(
					'error_code' => 'course_curriculum_validation_error',
					'errors'     => $validator->get_errors_array(),
				),
				422
			);
		}

		$data = $validator->get_validated();

		( new CurriculumMaterialRepository() )->delete( $data['id'] );

		return WpResponseFactory::ok();

	}
}
