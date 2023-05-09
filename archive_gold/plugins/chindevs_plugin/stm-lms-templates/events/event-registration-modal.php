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
		esc_html__( '%s', 'masterstudy-lms-learning-management-system-pro' ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
		esc_html( get_the_title( $course_id ) )
	);
	?>
</div>

<div class="stm_lms_select_price">

	  <div class="row">
		  <div class="col-sm-12">
			  <?php foreach ( $event_price_options as $key => $value ) : ?>
			  <div class="radio">
				  <label><b><?php echo esc_html( $key ); ?></b> - <?php echo STM_LMS_Helpers::display_price( $value ); ?>
                  	<input type="radio" name="price" value="<?php echo esc_attr( $value ); ?>" data-price="<?php echo esc_attr( $value ); ?>">
				  </label>
			  </div>
			  <?php endforeach; ?>
		  </div>
		</div>

<!--   	If we end up using a form. <?php echo do_shortcode('[contact-form-7 id="216194" title="Registration Form" class="wpcf7-form ajax-form"]'); ?> -->

	<a href="#"
	   data-course-id="<?php echo intval( $course_id ); ?>"
	   class="btn btn-default event-add-to-cart disabled"
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


