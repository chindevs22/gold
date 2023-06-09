<?php
/***
 * @var $post_id
 * @var $item_id
 * @var $last_answers
 * @var $q
 */

$user = STM_LMS_User::get_current_user();

$attempts = SLMS_User_Quizzes::get_user_quiz_attempts($post_id, $item_id, $user['id']);

?>
<?php if(count($attempts)): ?>
<span class="stm-lms-single_quiz__label"></span>

<h3 class="stm-lms-single_question__text"><?php _e('Previous Results', 'slms'); ?></h3>

<table style="text-align:center;">
    <thead>
    <th><?php _e('Attempt', 'slms'); ?></th>
    <th><?php _e('Points', 'slms'); ?></th>
    <th><?php _e('Progress', 'slms'); ?></th>
    <th><?php _e('Status', 'slms'); ?></th>
    <th><?php _e('Answers', 'slms'); ?></th>
    </thead>
    <tbody>
    <?php foreach($attempts as $key => $attempt): ?>
        <?php
        $last_answers = stm_lms_get_quiz_attempt_answers(
            $user['id'],
            $attempt['quiz_id'],
            array(
                'question_id',
                'user_answer',
                'correct_answer',
            ),
            $key+1
        );

        $slms_points = 0;
        if(count($last_answers)) {
            foreach ($last_answers as $answer) {
                $question_id = intval($answer['question_id']);
                if(!empty(intval($answer['correct_answer']))) {
                    $slms_points += (int)get_post_meta($question_id, 'slms_points', true);
                }
            }
        }
        ?>
        <tr>
            <td><?php echo $key+1; ?></td>
            <td><?php echo $slms_points; ?></td>
            <td><?php echo $attempt['progress']; ?>%</td>
            <td><?php echo ($attempt['status'] == 'passed') ? __('Passed', 'slms') : __('Failed', 'slms'); ?></td>
            <td>
                <a href="#attempt-details-<?php echo $key+1; ?>"
                   onclick="return false;"
                   data-toggle="collapse"
                   data-target="#attempt-details-<?php echo $key+1; ?>"
                    class="slms-attempt-details-btn collapsed">
                    <?php _e('Show', 'slms'); ?>
                    <i class="fa fa-chevron-down"></i>
                </a>
            </td>
        </tr>
        <tr id="attempt-details-<?php echo $key+1; ?>" class="collapse">
            <td colspan="5">
                <?php
                $answers = [];
                if(count($last_answers)) {
                    foreach($last_answers as $answer) {
                        $question_id = intval($answer['question_id']);
                        $answers[] = array(
                            'id' => $question_id,
                            'title' => get_the_title($question_id),
                            'answer' => $answer['user_answer'],
                            'correct_answer' => $answer['correct_answer'],
                            'correct' => (!empty(intval($answer['correct_answer']))) ? __('Correct','slms') : __('Incorrect','slms'),
                            'correct_class' => (!empty(intval($answer['correct_answer']))) ? 'text-success' : 'text-danger',
                        );
                    }
                }
                ?>

                <table style="margin:0;">
                    <thead>
                    <tr>
                        <th><?php _e('Question', 'slms'); ?></th>
                        <th><?php _e('Status', 'slms'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($answers as $answer): ?>
                    <tr>
                        <td><?php echo $answer['title']; ?></td>
                        <td><span class="<?php echo $answer['correct_class']; ?>"><?php echo $answer['correct']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>