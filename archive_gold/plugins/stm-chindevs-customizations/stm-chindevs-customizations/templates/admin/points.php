<?php
/**
 * @var $post_id
 * @var $questions_points
 */

//    pre_var($questions_points);
?>

<style>
    .slms-questions-points {
        padding-top: 15px;
    }
    .slms-questions-points--section {
        background-color: #fff;
        border: 1px solid #ccc;
        display: block;
        box-shadow: 0 0 2px 0px rgba(0,0,0,0.3);
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        position: relative;
    }
    .slms-questions-points--section__btn {
        color: inherit;
        text-decoration: none;
        display: block;
    }
    .slms-questions-points--section__btn,
    .slms-questions-points--section__btn:focus {
        box-shadow: 0 0 0 transparent !important;
        outline: none !important;
    }
    .slms-questions-points--section__btn i {
        color: inherit;
        position: absolute;
        right: 8px;
        top: 8px;
        font-size: 18px;
        width: 22px;
        height: 22px;
        text-align: center;
        line-height: 22px;
    }
    .slms-questions-points--section__btn.opened i {
        transform: rotate(180deg);
    }
</style>

<div class="slms-questions-points">

    <?php if(count($questions_points)): ?>
        <?php foreach ($questions_points as $quiz_id => $items): ?>
            <div class="slms-questions-points--section">

                <a href="#quiz-<?php echo $quiz_id; ?>" data-id="<?php echo $quiz_id; ?>" class="slms-questions-points--section__btn">
                    <strong><?php _e('Quiz:', 'slms'); ?> <?php echo get_the_title($quiz_id); ?></strong>
                    <i class="fa fa-angle-down"></i>
                </a>

                <div class="slms-questions-points--section__collapse" id="quiz-<?php echo $quiz_id; ?>" style="display:none;">
                    <table>
                        <tbody>
                            <?php if(count($items)): ?>
                                <?php foreach($items as $question_id => $value): ?>
                                    <tr>
                                        <td><?php echo get_the_title($question_id); ?></td>
                                        <td><input type="number" name="slms_points[<?php echo $quiz_id; ?>][<?php echo $question_id; ?>]" value="<?php echo $value; ?>"></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
    (function ($){
        $(document).on('click', '.slms-questions-points--section__btn', function (e){
            e.preventDefault();
            let item_id = $(this).data('id');
            $('#quiz-' + item_id).toggle();
            $(this).toggleClass('opened');
        });
    })(jQuery);
</script>
