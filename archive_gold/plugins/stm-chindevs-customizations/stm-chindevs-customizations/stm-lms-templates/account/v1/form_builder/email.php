<?php
/**
 * @var $model
 * @var $value
 */

if(!empty($value)) {
    $value = "v-bind:value=\"{$value}\"";
} else {
    $value = '';
}

if(empty($model)) $model = "field.value";

$empty_fields = '';
if(isset($_GET['empty_fields']) && !empty($_GET['empty_fields'])) {
    $fields = explode(',', $_GET['empty_fields']);
    foreach ($fields as $key => $field_id) {
        $empty_fields .= "'$field_id'";
        if($key + 1 < count($fields) ) {
            $empty_fields .= ',';
        }
    }
}
?>

<input class="form-control"
       v-if="field.type === 'text' || field.type === 'tel' || field.type === 'email'"
       :class="[
          field.id,
          [<?php echo $empty_fields; ?>].includes(field.id) ? 'error' : '',
        ]"
       :placeholder="field.placeholder ? field.placeholder : ''"
       :type="field.type"
       <?php echo stm_lms_filtered_output($value); ?>
       v-model="<?php echo stm_lms_filtered_output($model); ?>"/>