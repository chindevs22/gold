<?php
/**
 * @var $name
 * @var $model
 */

if(empty($name)) $name = 'field.id';
if(empty($model)) $model = 'field.value';

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

<div v-if="field.type === 'radio'"
     class="field-item"
     :class="[
      field.id,
      [<?php echo $empty_fields; ?>].includes(field.id) ? 'error' : '',
    ]"
>
    <label v-if="field.type === 'radio' && choice !== ''"
           v-for="(choice, index) in field.choices"
           class="radio-label">

        <input type="radio"
               :name="<?php echo stm_lms_filtered_output($name); ?>"
               v-bind:value="choice"
               :checked="index === 0"
               v-model="<?php echo stm_lms_filtered_output($model); ?>"/>

        <span v-html="choice"></span>

    </label>
</div>