<?php
/**
 * @var $model
 */

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


<div v-if="field.type === 'select'">
	<div v-if="field.choices.length > 0">
		<select class="form-control field-item disable-select"
                :class="[
                  field.id,
                  [<?php echo $empty_fields; ?>].includes(field.id) ? 'error' : '',
                ]"
          v-init="<?php echo stm_lms_filtered_output($model) ?>"
          v-model="<?php echo stm_lms_filtered_output($model) ?>"
          @change="selectChange($event, field)">
      <option v-if="field.placeholder" v-html="field.placeholder" v-bind:value="''"></option>
      <option v-for="choice in field.choices" v-html="choice" v-if="choice !== ''"></option>
  	</select>
	</div>
	<div v-else>
	  <input type="text"
			 class="form-control field-item"
			 placeholder="Options Loading"
             :class="[
                  field.id,
                  [<?php echo $empty_fields; ?>].includes(field.id) ? 'error' : '',
                ]"
			 readonly>
	</div>
</div>