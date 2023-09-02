<?php

class SLMS_User_Quizzes {

    public static function maybe_create_db_column(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'stm_lms_user_quizzes';

        $column_name = 'stored_points';
        $column_type = 'VARCHAR(255)';

        $table_structure = $wpdb->get_results("DESCRIBE $table_name");

        $column_exists = false;

        foreach ($table_structure as $column) {
            if ($column->Field === $column_name) {
                $column_exists = true;
                break;
            }
        }

        if (!$column_exists) {
            $wpdb->query("ALTER TABLE $table_name ADD $column_name $column_type");
        }
    }


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
