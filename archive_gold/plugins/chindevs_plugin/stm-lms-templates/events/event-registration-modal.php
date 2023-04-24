<?php
/**
 * @var $course_id
 */
//$groups = STM_LMS_Enterprise_Courses::stm_lms_get_enterprise_groups( true );
require_once(dirname(__FILE__) . '/../../../chindevs_plugin/user_events.php');

$event_price_options = get_event_prices($course_id);
error_log("getting options");
error_log(print_r($event_price_options, true));

$price  = STM_LMS_Course::get_course_price( $course_id );
$limit = 1;
$user    = STM_LMS_User::get_current_user();
$user_id = $user['id'];

?>

<h2><?php esc_html_e( 'Register For Event', 'masterstudy-lms-learning-management-system-pro' ); ?></h2>
<div class="course_name">
	<?php
	printf(
		/* translators: %s Bundle price */
		esc_html__( '%s', 'masterstudy-lms-learning-management-system-pro' ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
		esc_html( get_the_title( $course_id ) )
	);
	?>
</div>

<div class="actions has-groups">

	<div class="stm_lms_popup_add_users">

	<div class="stm_lms_popup_add_users__inner">

        <div class="row">
            <div class="col-sm-6">
                <label>
                    <span class="heading_font">
                        <?php esc_html_e( 'Your Name:', 'masterstudy-lms-learning-management-system-pro' ); ?>
                    </span>
                    <input type="text" placeholder="<?php esc_attr_e( 'Enter your name...', 'masterstudy-lms-learning-management-system-pro' ); ?>" class="form-control" name="gc_name" id="gc_name"/>
                </label>
            </div>
            <div class="col-sm-6">
                <label>
                    <span class="heading_font">
                        <?php esc_html_e( 'Your Email:', 'masterstudy-lms-learning-management-system-pro' ); ?>
                    </span>
                    <input type="email" placeholder="<?php esc_attr_e( 'Enter your email...', 'masterstudy-lms-learning-management-system-pro' ); ?>" class="form-control" name="gc_email" id="gc_email"/>
                </label>
            </div>
        </div>

		<a href="#"
            data-course-id="<?php echo intval( $course_id ); ?>"
            class="btn btn-default add-to-cart disabled"
            data-price="<?php echo esc_attr( $price ); ?>">
            <?php
            printf(
                /* translators: %s Price */
                esc_html__( 'Add to cart %s', 'masterstudy-lms-learning-management-system-pro' ),
                '<span>' . STM_LMS_Helpers::display_price( '0' ) . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            );
            ?>
	    </a>
	</div>

</div>
</div>

