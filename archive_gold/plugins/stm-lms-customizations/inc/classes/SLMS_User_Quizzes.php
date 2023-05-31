<?php

class SLMS_User_Quizzes {

//    public static function get_user_answers(int $course_id, int $quiz_id, int $user_id){
//        global $wpdb;
//        $table = stm_lms_user_answers_name( $wpdb );
//
//        $fields = ( empty( $fields ) ) ? '*' : implode( ',', $fields );
//
//        $request = "SELECT {$fields} FROM {$table}
//                    WHERE
//                    course_id = {$course_id} AND
//                    user_id = {$user_id} AND
//                    quiz_id = {$quiz_id} AND
//                    ORDER BY attempt_number DESC";
//
//        $r = $wpdb->get_results( $request, ARRAY_A );
//
//        return $r;
//    }
//
//    public static function get_user_quiz_attempts(int $course_id, int $quiz_id, int $user_id){
//        $result = [];
//
//        $rows = self::get_user_answers($course_id, $quiz_id, $user_id);
//
//    }

    public static function get_user_quiz_attempts(int $course_id, int $quiz_id, int $student_id){
        $quizzes = stm_lms_get_user_all_course_quizzes( $student_id, $course_id, $quiz_id );

        $quizzes = array_map(
            function ( $quiz ) {
                $quiz['title'] = get_the_title( $quiz['quiz_id'] );

                return $quiz;
            },
            $quizzes
        );

        return $quizzes;
    }

}
