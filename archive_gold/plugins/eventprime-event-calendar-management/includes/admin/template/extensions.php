<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$buy_link = event_m_get_buy_link();
$em = event_magic_instance();
?>
<div class="eventprime ep-full-width ep-extensions">
    <div class="ep-exts-bundle-banner ep-box-wrap">
        <a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="_blank">
            <img  class="ep-extension-bundle" alt="EventPrime Extension Bundle" src="<?php echo plugin_dir_url(__DIR__) . 'template/images/'; ?>ep-extension-banner.png" >
        </a>
    </div>
    
    
    <div class="ep-ext-list-title">Official Extensions
        <span class="ep-box-sep"></span>
        <span class="ep-scblock-mg-logo">
            <img src="<?php echo plugin_dir_url(__DIR__) . 'template/images/'; ?>mg-logo.png" >
        </span>
    </div>
    
    <div class="ep-extensions-filters ep-box-wrap">
        <div class="ep-box-row">
            <div class="ep-box-col-12 ep-d-flex ep-box-center">
                <span class="ep-filter-lable">Filter</span>
                <ul id="em-ext-controls">
                    <li><a href="#" id="all-extensions">All</a></li>
                    <li><a href="#" id="paid-extensions">Paid</a></li>
                    <li><a href="#" id="free-extensions">Free</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="ep-extensions-box-wrap ep-box-wrap">
        <div class="ep-box-row">
            <?php $ext_list = ep_all_exts();
            foreach ($ext_list as $ext) {
                $ext_details = em_get_more_extension_data($ext);?>
                <div class="ep-box-col-4 ep-card-mb-16 ep-ext-card <?php echo $ext_details['is_free'] == 0 ? 'paid-extensions' : 'free-extensions';?>">
                    <div class="ep-box-border ep-box-p-18 ep-box-white-bg ep-box-h-100">
                        <div class="ep-box-row ep-box-h-100">
                            <div class="ep-box-col-8">
                                <div class="ep-ext-box-title"> <?php echo $ext;?></div>
                                <div class="ep-ext-installation-status"> 
                                    <div class="ep-ext-installation-status">
                                        <?php 
                                        if($ext_details['button'] == 'Activate'){?>
                                            <span class="ep-ext-not-installed">Not Activated</span><?php
                                        }
                                        else if($ext_details['is_activate']) {?>
                                            <span class="ep-ext-installed"> Installed</span><?php
                                        } else{?>
                                            <span class="ep-ext-not-installed">Not Installed</span><?php
                                        }?>
                                    </div>
                                </div>
                                <div class="ep-ext-box-description">
                                    <p class="ep-col-desc"><?php echo $ext_details['desc'];?></p>    
                                </div>
                                <div class="ep-ext-box-button">
                                    <a class="ep-install-now-btn ep-more-info <?php echo $ext_details['class_name'];?>" href="<?php echo $ext_details['url'];?>" target="_blank">
                                        <?php echo $ext_details['button'];?>
                                    </a>
                                </div>
                            </div>
                            <div class="ep-box-col-4 ep-d-flex ep-d-flex-v-center ep-flex-direction-col">
                                <?php if($ext_details['is_free'] == 1){?>
                                <div class="ep-ext-box-price">
                                    <a class="ep-free-extension" href="<?php echo $ext_details['url'];?>" target="_blank">Free</a>
                                </div><?php
                                }?>
                                <div class="ep-ext-box-icon">
                                    <img  class="ep-ext-icon" alt="" src="<?php echo plugin_dir_url(__DIR__) . 'template/images/'.$ext_details['image']; ?>" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div><?php
            }?>
        </div>
    </div>

    <div class="ep-exts-bundle-banner dbfl"> <a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="_blank"><img  class="ep-extension-bundle" alt="EventPrime Extension Bundle" src="<?php echo plugin_dir_url(__DIR__) . 'template/images/'; ?>ep-extension-banner.png" ></a> </div>
</div>



<!----Old Code---->



<!-- <div class="ep-ext-list">
        <?php $ext_list = ep_all_exts();
        foreach ($ext_list as $ext) {
            $ext_details = em_get_more_extension_data($ext);?>
        
            <div class="plugin-card ep-ext-card <?php echo $ext_details['is_free'] == 0 ? 'paid-extensions' : 'free-extensions';?>">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a href="<?php echo $ext_details['url'];?>" class=" open-plugin-details-modal" target="_blank">
                                <?php echo $ext;?>
                                <img  class="plugin-icon" alt="" src="<?php echo plugin_dir_url(__DIR__) . 'template/images/'.$ext_details['image']; ?>" > 
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php if($ext_details['is_free'] == 1){?>
                                <li class="ep-free-extension-wrap">
                                    <a class="ep-free-extension" href="<?php echo $ext_details['url'];?>" target="_blank">Free</a>
                                </li><?php
                            }?>
                            <li>
                                <a class="install-now button <?php echo $ext_details['class_name'];?>" href="<?php echo $ext_details['url'];?>"><?php echo $ext_details['button'];?></a>
                            </li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p class="ep-col-desc"><?php echo $ext_details['desc'];?></p>
                        <p class="authors"> <cite>By <a target="_blank" href="https://eventprime.net/">eventprime</a></cite></p>
                    </div>
                </div>
            </div><?php
        }?>
    </div>-->

<!---End: Old Code---->