<?php
/**
 * @var string $type
 * @var array $answers
 * @var string $question
 * @var string $question_explanation
 * @var string $question_hint
 */
$question_id = get_the_ID();

stm_lms_register_style('item_match_question');
wp_enqueue_script('jquery-ui-sortable');
stm_lms_register_script('jquery.ui.touch-punch.min');
stm_lms_register_script('item_match_question', array('stm-lms-jquery.ui.touch-punch.min'));

$user_answers = array();
if (!empty($user_answer['user_answer'])) {
    $user_answers = explode('[stm_lms_sep]', str_replace('[stm_lms_item_match]', '', $user_answer['user_answer']));
}

$value = (!empty($user_answer['user_answer'])) ? $user_answer['user_answer'] : '';

?>

<div class="stm_lms_question_item_match">

    <div class="row">

        <div class="col-md-6">
            <div class="stm_lms_question_item_match__questions">
                <?php foreach ($answers as $answer): ?>
                    <div class="stm_lms_question_item_match__single">
                        <?php if ( array_key_exists('question', $answer) ) {
                                echo wp_kses_post($answer['question']);
                            } else {
                                esc_html_e('The question was not set', 'masterstudy-lms-learning-management-system');
                            } ?>
                        <div class="stm_lms_question_item_match__answers">
                            <div class="stm_lms_question_item_match__answer"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="text" class="stm_lms_question_item_match__input" name="<?php echo esc_attr($question_id); ?>" value="<?php echo $value; ?>"/>
        </div>

        <div class="col-md-6">
            <div class="stm_lms_question_item_match__answers stm_lms_question_item_match__answers_origin">
                <?php foreach ($answers as $i => $answer): ?>
                    <div class="stm_lms_question_item_match__answer">
                        <?php if(SLMS_Quiz::show_results()): ?>
                            <?php if (isset($user_answers[$i]) && !empty($user_answers[$i])): ?>
                                <div class="stm_lms_question_item_match__match">
                                    <?php echo esc_html( stripslashes($user_answers[$i]) ); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-12">
            <div class="stm_lms_question_item_match__matches">
                <?php shuffle($answers); foreach ($answers as $i => $answer): ?>
                    <?php if(SLMS_Quiz::show_results()): ?>
                        <?php if ((isset($user_answers[$i]) && empty($user_answers[$i])) || !isset($user_answers[$i])): ?>
                            <div class="stm_lms_question_item_match__match ui-state-highlight" data-answer="<?php echo esc_attr($answer['text']); ?>"><?php echo wp_kses_post($answer['text']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                            <div class="stm_lms_question_item_match__match ui-state-highlight" data-answer="<?php echo esc_attr($answer['text']); ?>"><?php echo wp_kses_post($answer['text']); ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

    </div>


</div>