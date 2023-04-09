<?php
$offer_data = event_m_get_offer_data();
?>
<div class="eventprime ep-full-width" style="display:none">
    <div class="" >
    <?php
    if(!empty($offer_data)){
        foreach($offer_data as $offer){?>
            <div class="ep-offer">
                <div class="ep-offer-wrap">
                    <?php if(isset($offer['title'])):?>
                        <span class="ep-offer-title"><strong><?php echo $offer['title']; ?></strong></span>
                    <?php endif; ?>
                    <?php if(isset($offer['offer'])):?>
                        <span class="ep-offer-desc"><?php echo $offer['offer']; ?></span>
                    <?php endif; ?>
                    <?php if(isset($offer['code'])):?>
                        <span class="ep-offer-code"><strong><?php echo $offer['code']; ?></strong></span>
                    <?php endif; ?>
                </div>
                <?php if(isset($offer['link'])):?>
                    <div class="ep-buy-btn"><a href="<?php echo $offer['link']; ?>"><?php echo (isset($offer['link_title']))?$offer['link_title']:__('Buy Now','eventprime-event-calendar-management'); ?></a></div>
                <?php endif; ?>
            </div><?php
        }
    }
    else{?>
        <div class="ep-no-offer"><?php _e('Sorry, no offers available right now.','eventprime-event-calendar-management');?></div><?php
    }?>
</div>
 </div>


<div class="kikfyre ep-offer-page">
    <div class="ep-offer-code-area dbfl">
        <div class="kf-db-title dbfl"><?php _e('Offers', 'eventprime-event-calendar-management'); ?></div>  

        <div class="ep-offers">
            <div class="ep-offer-results"></div>
            <div class="ep-offer-button"> <button><?php _e('Fetch Offers', ''); ?></button>  </div>
        </div>
    </div> 
</div>

<div class="ep-fetch-offers-popup ep-modal-box-main"  style="display: none">
    <div class="ep-modal-box-overlay ep-modal-box-overlay-fade-in"></div>
    <div class="ep-modal-box-wrap ep-modal-box-out">
        <div class="ep-modal-box-header">
            <div class="ep-popup-title"><?php _e('Please Confirm', 'eventprime-event-calendar-management'); ?>   </div>
           <span class="ep-modal-box-close">×</span>
        </div>
        <div class="ep-extension-modal-des">
           <?php _e('To fetch latest offers, we need to connect to the EventPrime server for a moment. No data related to you or your website will be stored or shared. Do you agree to proceed?', 'eventprime-event-calendar-management'); ?>
        </div>
        <div class="ep-modal-box-footer">
            <!--<input type="button" name="confirm" value="" class="pm-popup-close" onclick="pg_fetch_offers()" />-->
            <button onclick="ep_fetch_offers()"><i class="fa fa-refresh fa-spin ep-fetch_offers-spiner " style="display: none;"></i><?php _e("I Agree", 'eventprime-event-calendar-management'); ?></button>
            <a href="javascript:void(0)" class="ep-modal-box-close"><?php _e("I don't Agree", 'eventprime-event-calendar-management'); ?></a>
        </div>
    </div>
</div>