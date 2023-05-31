<?php
/**
 * @var $model
 */

if(empty($model)) $model = "field.value";
?>


<div v-if="field.type === 'select'">
	<div v-if="field.choices.length > 0">
		<select class="form-control disable-select"
          v-init="<?php echo stm_lms_filtered_output($model) ?>"
          v-model="<?php echo stm_lms_filtered_output($model) ?>"
          @change="selectChange($event, field)">
      <option v-if="field.placeholder" v-html="field.placeholder" v-bind:value="''"></option>
      <option v-for="choice in field.choices" v-html="choice" v-if="choice !== ''"></option>
  	</select>
	</div>
	<div v-else>
	  <input type="text"
			 class="form-control"
			 placeholder="Options Loading"
			 readonly>
	</div>
</div>