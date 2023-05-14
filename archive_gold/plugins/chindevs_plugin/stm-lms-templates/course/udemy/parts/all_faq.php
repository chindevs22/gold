<?php
stm_lms_register_style( 'faq' );

$faq = get_post_meta( get_the_ID(), 'faq', true );

$payment_faq = get_post_meta( get_the_ID(), 'payment_faq', true );

if ( ! empty( $faq ) || ! empty ( $payment_faq ) ) :
	?>
	<div class="panel-group" id="stm_lms_faq" role="tablist" aria-multiselectable="true">
	<?php
	$payment_faq = json_decode( $payment_faq, true );
	if ( ! empty( $payment_faq ) ) :
		?>
		<h3><b> Payment-Related FAQs </b></h3>
		<?php
		foreach ( $payment_faq as $pfaq_id => $pfaq_doc ) :
			if ( empty( $pfaq_doc['answer'] ) || empty( $pfaq_doc['question'] ) ) {
				continue;
			}
			error_log("Payment FAQ ID" . $pfaq_id);
			error_log(print_r($pfaq_doc, true));
			?>
			<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="heading<?php echo esc_attr( $pfaq_id ); ?>">
					<h4 class="panel-title">
						<a role="button"
						   data-toggle="collapse"
						   class="heading_font collapsed"
						   data-parent="#accordion"
						   href="#collapse<?php echo esc_attr( $pfaq_id ); ?>"
						   aria-expanded="true"
						   aria-controls="collapse<?php echo esc_attr( $pfaq_id ); ?>">
							<i class="fa fa-angle-down"></i>
							<?php echo sanitize_text_field( $pfaq_doc['question'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</h4>
				</div>
				<div id="collapse<?php echo esc_attr( $pfaq_id ); ?>"
					 class="panel-collapse collapse"
					 role="tabpanel"
					 aria-labelledby="heading<?php echo esc_attr( $pfaq_id ); ?>">
					<div class="panel-body">
						<?php echo html_entity_decode( $pfaq_doc['answer'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
			</div>
		<?php endforeach;
	endif;
	if ( ! empty( $faq ) ) :
		$faq = json_decode( $faq, true );
		if ( ! empty( $faq ) ) :
			?>
				<h3><b>Course FAQs</b></h3>
			<?php
			foreach ( $faq as $faq_id => $faq_doc ) :
				if ( empty( $faq_doc['answer'] ) || empty( $faq_doc['question'] ) ) {
					continue;
				}
				$faq_id = $faq_id + count($payment_faq);
				error_log("FAQ ID" . $faq_id);
				error_log(print_r($faq_doc, true));
				?>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="heading<?php echo esc_attr( $faq_id ); ?>">
						<h4 class="panel-title">
							<a role="button"
									data-toggle="collapse"
									class="heading_font collapsed"
									data-parent="#accordion"
									href="#collapse<?php echo esc_attr( $faq_id ); ?>"
									aria-expanded="true"
									aria-controls="collapse<?php echo esc_attr( $faq_id ); ?>">
								<i class="fa fa-angle-down"></i>
								<?php echo sanitize_text_field( $faq_doc['question'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</h4>
					</div>
					<div id="collapse<?php echo esc_attr( $faq_id ); ?>"
							class="panel-collapse collapse"
							role="tabpanel"
							aria-labelledby="heading<?php echo esc_attr( $faq_id ); ?>">
						<div class="panel-body">
							<?php echo html_entity_decode( $faq_doc['answer'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				</div>
			<?php endforeach;
			endif;
		endif;
		?>
	</div>
	<?php
endif;
?>
