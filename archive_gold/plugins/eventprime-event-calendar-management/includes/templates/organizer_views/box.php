<div class="ep-box-wrap">
    <div class="ep-box-row ep-box-top ep-performer-box-wrap ep-organizer-box-wrap ep-box-mx-0">
        <?php 
        $organizer_box_color = em_global_settings('organizer_box_color');
        $b = 1;
        foreach( $organizers as $organizer ){
            $organizer_url = add_query_arg("organizer", $organizer->id, $organizers_page_url);
            $enable_seo_urls = em_global_settings('enable_seo_urls');
            if(!empty($enable_seo_urls)){
                $organizer_url = get_term_link($organizer->id);
            }
            if( $b > 4 ) { 
                $b = 1;
            }
            switch ($b) {
                case 1 :
                    $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[0])) ? '#'.$organizer_box_color[0] : '#A6E7CF';
                    break;
                case 2 :
                    $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[1])) ? '#'.$organizer_box_color[1] : '#DBEEC1';
                    break;
                case 3 :
                    $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[2])) ? '#'.$organizer_box_color[2] : '#FFD3B6';
                    break;
                case 4 :
                    $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[3])) ? '#'.$organizer_box_color[3] : '#FFA9A5';
                    break;
                default:
                    $bg_color = '#A6E7CF';
            }
            $light_bg_color = ep_hex2rgba($bg_color, .5);
            $bg_color = ep_hex2rgba($bg_color, 1);?>
            <div class="ep-box-col-<?php echo $organizer_cols;?> ep-box-column ep-box-px-0" data-id="<?php echo $organizer->id;?>" data-element_type="column">
                <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,<?= $bg_color;?>,<?= $light_bg_color;?>); background-color: transparent;">
                    <div class="ep-box-widget-wrap" data-id="<?php echo $organizer->id;?>">
                        <div class="ep-box-box-item">
                            <div class="ep-box-box-thumb">
                                <?php if ( ! empty( $organizer->image_id ) ){ ?>
                                    <a href="<?php echo $organizer_url; ?>" class="img-fluid"><img src="<?php echo wp_get_attachment_image_src( $organizer->image_id, 'large' )[0]; ?>" alt="<?php _e('Event Organizer Image', 'eventprime-event-calendar-management'); ?>"></a>
                                <?php }else{ ?>
                                    <img src="<?php echo esc_url( plugins_url( '../images/dummy-organizer.png', __FILE__ ) ) ?>" class="img-fluid" alt="<?php _e( 'Dummy Image','eventprime-event-calendar-management' ); ?>"> 
                                <?php } ?>
                            </div>
                            <div class="ep-performer-content">
                                <div class="ep-box-title ep-box-box-title">
                                    <a href="<?php echo $organizer_url; ?>"><?php echo $organizer->name; ?></a>
                                </div>
                                <?php if ( ! empty( $organizer->social_links ) ){     
                                    echo '<div class="ep-organizers-social">';
                                        if( isset( $organizer->social_links->facebook ) )
                                        echo '<a href="'.$organizer->social_links->facebook.'" target="_blank" title="Facebook">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>';
                                        if( isset( $organizer->social_links->instagram ) )
                                        echo '<a href="'.$organizer->social_links->instagram.'" target="_blank" title="Instagram">
                                                <i class="fab fa-instagram"></i>
                                            </a>';
                                        if( isset( $organizer->social_links->linkedin ) )
                                        echo '<a href="'.$organizer->social_links->linkedin.'" target="_blank" title="Linkedin">
                                                <i class="fab fa-linkedin"></i>
                                            </a>';
                                        if(isset($organizer->social_links->twitter ) )
                                        echo '<a href="'.$organizer->social_links->twitter.'" target="_blank" title="Twitter">
                                                <i class="fab fa-twitter"></i>
                                            </a>';
                                    echo '</div>'; 
                                }?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
        <?php $b++; } ?>
    </div>
</div>