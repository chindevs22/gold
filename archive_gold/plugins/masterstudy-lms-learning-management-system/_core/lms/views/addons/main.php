<div class="stm-lms-addons">
	<div class="stm-lms-addon-search">
		<input id="addons-search" type="text" placeholder="<?php esc_attr_e( 'Search addons', 'masterstudy-lms-learning-management-system' ); ?>"
			value="<?php echo esc_attr( $_GET['search'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>">
		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
			<defs></defs>
			<path id="Forma_1" data-name="Forma 1" d="M15.8,14.855L11.25,10.31a6.338,6.338,0,1,0-.942.942L14.854,15.8A0.666,0.666,0,1,0,15.8,14.855ZM6.335,11.33a5,5,0,1,1,4.994-5A5,5,0,0,1,6.335,11.33Z" class="cls-1"></path>
		</svg>
	</div>
	<?php
	if ( ! STM_LMS_Helpers::is_pro() ) {
		?>
	<div class="stm-lms-addon-banner">
		<div class="stm-lms-addon-banner-text">
			<h2>
				<strong><?php echo esc_html__( 'Get MasterStudy Pro', 'masterstudy-lms-learning-management-system' ); ?></strong>
				<?php echo esc_html__( 'with all Addons for a Single Price', 'masterstudy-lms-learning-management-system' ); ?>
			</h2>
			<ul>
				<li>
					<img src="<?php echo esc_url( STM_LMS_URL . '/assets/addons/addons.svg' ); ?>" alt="">
					<?php echo esc_html__( '20+ Premium addons', 'masterstudy-lms-learning-management-system' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( STM_LMS_URL . '/assets/addons/updates.svg' ); ?>" alt="">
					<?php echo esc_html__( 'Frequent updates', 'masterstudy-lms-learning-management-system' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( STM_LMS_URL . '/assets/addons/support.svg' ); ?>" alt="">
					<?php echo esc_html__( 'Priority ticket support', 'masterstudy-lms-learning-management-system' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( STM_LMS_URL . '/assets/addons/starter_theme.svg' ); ?>" alt="">
					<?php echo esc_html__( 'Starter theme', 'masterstudy-lms-learning-management-system' ); ?>
				</li>
			</ul>
			<a href="https://stylemixthemes.com/wordpress-lms-plugin/pricing/?utm_source=wpadmin-ms&utm_medium=addons&utm_campaign=get-now-addons" class="stm-lms-addon-banner-button" target="_blank">
				<i class="fas fa-arrow-right"></i>
				<?php echo esc_html__( 'Get Now', 'masterstudy-lms-learning-management-system' ); ?>
			</a>
		</div>
			<img src="<?php echo esc_url( STM_LMS_URL . '/assets/addons/addon_banner_bg.png' ); ?>" class="bg">
	</div>
		<?php
	}

	foreach ( $addons as $key => $addon ) {
		$addon_enabled = ! empty( $enabled_addons[ $key ] );
		?>
		<div class="stm-lms-addon <?php echo $addon_enabled ? 'active' : ''; ?>">
			<div class="addon-image">
				<img src="<?php echo esc_url( $addon['url'] ); ?>"/>
			</div>
			<div class="addon-install">
				<div class="addon-title">
					<h4 class="addon-name"><?php echo wp_kses( $addon['name'], array() ); ?></h4>
					<?php if ( STM_LMS_Helpers::is_pro() && ! empty( $addon['documentation'] ) ) { ?>
						<div class="addon-documentation">
							<a href="https://docs.stylemixthemes.com/masterstudy-lms/lms-pro-addons/<?php echo esc_attr( $addon['documentation'] ); ?>" target="_blank">
								<?php esc_html_e( 'How it works', 'masterstudy-lms-learning-management-system' ); ?>
							</a>
							<i class="stmlms-question"></i>
						</div>
					<?php } ?>
					<?php if ( ! STM_LMS_Helpers::is_pro() ) { ?>
						<span class="addon-badge"><?php esc_html_e( 'Pro', 'masterstudy-lms-learning-management-system' ); ?></span>
					<?php } ?>
				</div>
				<div class="addon-description"><?php echo wp_kses( $addon['description'], array() ); ?></div>
				<div class="addon-settings-wrapper">
				<?php if ( ! STM_LMS_Helpers::is_pro() && ! empty( $addon['documentation'] ) ) { ?>
					<div class="addon-documentation">
						<a href="https://docs.stylemixthemes.com/masterstudy-lms/lms-pro-addons/<?php echo esc_attr( $addon['documentation'] ); ?>" target="_blank">
							<?php esc_html_e( 'How it works', 'masterstudy-lms-learning-management-system' ); ?>
						</a>
						<i class="stmlms-question"></i>
					</div>
				<?php } ?>
				<?php if ( STM_LMS_Helpers::is_pro() ) { ?>
						<div class="wpcfto-admin-checkbox section_2-enable_courses_filter">
							<label class="toggle-addon" data-key="<?php echo esc_attr( $key ); ?>">
								<div class="wpcfto-admin-checkbox-wrapper is_toggle <?php echo $addon_enabled ? 'active' : ''; ?>">
									<div class="wpcfto-checkbox-switcher"></div>
									<input type="checkbox" name="enable_courses_filter" id="section_2-enable_courses_filter">
								</div>
							</label>
						</div>
						<?php if ( ! empty( $addon['settings'] ) ) { ?>
							<a href="<?php echo esc_url( $addon['settings'] ); ?>" class="addon-settings <?php echo $addon_enabled ? 'active' : ''; ?>" target="_blank">
								<i class="fa fa-cog"></i>
								<?php esc_html_e( 'Settings', 'masterstudy-lms-learning-management-system' ); ?>
							</a>
						<?php } ?>
				<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
