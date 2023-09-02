<?php
/**
 * @var $post_id
 * @var $whatsapp_number
 * @var $text
 * @var $button
 */

$url = "https://api.whatsapp.com/send?phone=$whatsapp_number&text=$text";
?>

<a class="button button-whatsapp" href="<?php echo esc_url($url); ?>" target="_blank">
    <i class="fab fa-whatsapp"></i> <?php echo $button; ?>
</a>
