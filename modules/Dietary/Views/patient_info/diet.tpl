<script type="text/javascript" src="{$JS}/diet.js"></script>

<div style="display: flex; justify-content: space-between; flex: 1;">
<a href="/?module=Dietary&amp;page=patient_info&amp;action=switchPatientLookup&amp;patient={$patient->public_id}&amp;location={$selectedLocation->public_id}&amp;direction=-">-Previous Patient</a>
<div class="change-message" style="display: none;">You have unsaved Changes</div>
<a href="/?module=Dietary&amp;page=patient_info&amp;action=switchPatientLookup&amp;patient={$patient->public_id}&amp;location={$selectedLocation->public_id}&amp;direction=+">Next Patient+</a>
</div>

<h1 style="margin-top:0px">Edit Diet <span class="text-24">for</span> {$patient->fullName()}&nbsp;<span class="text-24">[{$patientInfo->number}]</span></h1>

<style>
.noPrefWarn {
	background: #fed8b1;
	margin: 2px auto;
	padding: 5px 15px;
	width: 100%;
	text-align: center;
}
</style>

<form class="form-inline" action="{$SITE_URL}" method="post">
	<input type="hidden" name="page" value="PatientInfo" />
	<input type="hidden" name="action" value="saveDiet" />
	<input type="hidden" id="patient-id" name="patient" value="{$patient->public_id}" />
	<input type="hidden" name="path" value="{$current_url}" />


	<!-- Patient Info Section -->
	<div class="form-header">
		Patient Info
	</div>
	<div class="form-group">
		<label for="first-name" class="col-form-label col-2">First Name:</label>
		<input type="text" id="first-name" class="form-control" name="first_name" value="{$patient->first_name}">
	</div>
{*
	<div class="form-group">
		<label for="middle-name" class="col-form-label col-2">Middle Name:</label>
		<input type="text" id="middle-name" class="form-control" size="10" name="middle_name" value="{$patient->middle_name}">
	</div>
*}
	<div class="form-group">
		<label for="last-name" class="col-form-label col-2">Last Name:</label>
		<input type="text" id="last-name" class="form-control" name="last_name" value="{$patient->last_name}">
	</div>
	<div class="form-group">
		<label for="last-name" class="col-form-label col-2">Birthdate:</label>
		<input type="text" class="form-control datepicker" size="8" name="date_of_birth" value="{display_date($patient->date_of_birth)}" />
	</div>
	{*
	<div class="form-group">
		<label for="patient-room-number" class="col-form-label col-2">Room:</label>
		<input type="text" id="room-number" class="form-control" size="4" name="room_number" value="{$patientInfo->number}" />
	</div>
	*}
	{if ($location->id == 21 or $patientInfo->location_id == 21)}
	<div class="form-group">
		<label for="table_number" class="col-form-label col-2">Table:</label>
		<input type="text" id="table_number" class="form-control" size="6" name="table_number" value="{$patientInfo->table_number}" />
	</div>
	{/if}


	<!-- Diet Info Section -->
	<div class="form-header">
		Diet Info
	</div>
	<div class="form-group">
		<label for="food-allergies">Food Allergies:</label>
		<ul maxlength="128" id="allergies">
			{if $allergies}
				{foreach from=$allergies item=allergy}
				<li>{$allergy->name}</li>
				{/foreach}
			{/if}
		</ul>
	</div>

	<!-- Food Dislikes or Intolerances section -->
	<div class="form-group">
		<label for="food-dislikes">Food dislikes or intolerances:</label>
		<ul maxlength="128" id="dislikes">
			{if $dislikes}
				{foreach from=$dislikes item=dislike}
				<li value="{$dislike->id}">{$dislike->name}</li>
				{/foreach}
			{/if}
		</ul>
	</div>

	<!-- Adaptive Equipment Section -->
	<div class="form-group">
		<label for="adaptive-equipment">Adaptive Equipment:</label>
		<ul maxlength="45" id="adaptEquip">
			{if $adaptEquip}
				{foreach from=$adaptEquip item=equip}
				<li value="{$equip->id}">{$equip->name}</li>
				{/foreach}
			{/if}
		</ul>
	</div>

	<!-- Supplements Section -->
	<div class="form-group">
		<label for="supplements">Supplements:</label>
		<ul maxlength="45" id="supplements">
			{if $supplements}
				{foreach from=$supplements item=supplement}
				<li value="{$supplement->id}">{$supplement->name}</li>
				{/foreach}
			{/if}
		</ul>
	</div>

	<!-- Selective Warning -->
	<div class="noPrefWarn" style="display:none;">
		⚠️ Less than 2-3 preferences entered for this Guest!
	</div>

	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
		<!-- Special Requests Section -->
		<div class="panel panel-default">
	    	<div class="panel-heading" role="tab" id="headingOne">
	    		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="text-decoration:none;color:#000">
	      			<h4 class="panel-title">Special Requests</h4>
	      		</a>
	    	</div>
	    	<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
	      		<div class="panel-body">
	        		<div class="col-md-1">Breakfast:</div>
	        		<div class="col-md-3">
						<ul maxlength="45" id="breakfast_specialrequest">
							{if $breakfast_spec_req}
							{foreach from=$breakfast_spec_req item=req}
							<li value="{$req->id}">{$req->name}</li>
							{/foreach}
							{/if}
						</ul>
	        		</div>
	        		<div class="col-md-1">Lunch:</div>
	        		<div class="col-md-3">
						<ul maxlength="45" id="lunch_specialrequest">
							{if $lunch_spec_req}
							{foreach from=$lunch_spec_req item=req}
							<li value="{$req->id}">{$req->name}</li>
							{/foreach}
							{/if}
						</ul>
	        		</div>
	        		<div class="col-md-1">Dinner:</div>
	        		<div class="col-md-3">
						<ul maxlength="45" id="dinner_specialrequest">
							{if $dinner_spec_req}
							{foreach from=$dinner_spec_req item=req}
							<li value="{$req->id}">{$req->name}</li>
							{/foreach}
							{/if}
						</ul>
	        		</div>
	      		</div>
	    	</div>


	    <!-- Beverages Section -->

		<!-- Beverages Section -->
		<div class="panel panel-default">
		    <div class="panel-heading" role="tab" id="headingTwo">
		    	<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo" style="text-decoration:none;color:#000">
		      		<h4 class="panel-title">
		      			Beverages
		      		</h4>
		      	</a>
		    </div>
		    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
		      <div class="panel-body">
		        <div class="col-md-1">
		        	Breakfast:
		        </div>
		        <div class="col-md-3">
					<ul maxlength="45" id="breakfast_beverages">
						{if $breakfast_beverages}
							{foreach from=$breakfast_beverages item=beverage}
							<li>{$beverage->name}</li>
							{/foreach}
						{/if}
					</ul>
		        </div>
		        <div class="col-md-1">
		        	Lunch:
		        </div>
		        <div class="col-md-3">
					<ul maxlength="45" id="lunch_beverages">
						{if $lunch_beverages}
							{foreach from=$lunch_beverages item=beverage}
							<li>{$beverage->name}</li>
							{/foreach}
						{/if}
					</ul>
		        </div>
		        <div class="col-md-1">
		        	Dinner:
		        </div>
		        <div class="col-md-3">
					<ul maxlength="45" id="dinner_beverages">
						{if $dinner_beverages}
							{foreach from=$dinner_beverages item=beverage}
							<li>{$beverage->name}</li>
							{/foreach}
						{/if}
					</ul>
		        </div>
		    </div>
	  	</div>

		<!-- Snacks drop down section -->
	  	<div class="panel panel-default">
		    <div class="panel-heading" role="tab" id="headingThree">
		    	<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree" style="text-decoration:none;color:#000">
		    	<h4 class="panel-title">Snacks
		    	</h4>
		     	</a>
		    </div>
		    <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
		      <div class="panel-body">
		      	<div class="col-md-1">
		      		AM
	      		</div>
	      		<div class="col-md-3">
					<ul maxlength="128" id="snackAM">
						{if $am_snacks}
							{foreach from=$am_snacks item=snack}
							<li>{$snack->name}</li>
							{/foreach}
						{/if}
					</ul>
	      		</div>
	      		<div class="col-md-1">
	      			PM
	      		</div>
	      		<div class="col-md-3">
					<ul maxlength="128" id="snackPM">
						{if $pm_snacks}
							{foreach from=$pm_snacks item=snack}
							<li>{$snack->name}</li>
							{/foreach}
						{/if}
					</ul>
	      		</div>
	      		<div class="col-md-1">
	      			Bedtime
	      		</div>
	      		<div class="col-md-3">
					<ul maxlength="128" id="snackBedtime">
						{if $bedtime_snacks}
							{foreach from=$bedtime_snacks item=snack}
							<li>{$snack->name}</li>
							{/foreach}
						{/if}
					</ul>
	      		</div>
		      </div>
		    </div>
	  	</div>
	</div>
</div>

	<!-- Diet Order Section-->
	<div class="form-header2">Diet Order</div>
	<div class="checkbox">
		{if true or $selectedLocation->public_id == "ATW500KSj"}
			<label for="regular" class="checkbox-label">
				<input id="regular" class="checkbox" type="checkbox" name="diet_order[]" value="Regular" {if in_array("Regular", $dietOrder['standard'])} checked{/if}>
				Regular
			</label>
			<label for="aha-cardiac" class="checkbox-label">
				<input id="aha-cardiac" type="checkbox" name="diet_order[]" value="AHA/Cardiac" {if in_array("AHA/Cardiac", $dietOrder['standard'])} checked{/if}>
				AHA/Cardiac
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="No Added Salt" {if in_array("No Added Salt", $dietOrder['standard'])} checked{/if}>
				No Added Salt
			</label>
			<label class="checkbox-label">
					<input type="checkbox" name="diet_order[]" value="RCS" {if in_array("RCS", $dietOrder['standard'])} checked{/if}>
					RCS
				</label>
				<label class="checkbox-label">
						<input type="checkbox" name="diet_order[]" value="2 gram Na" {if in_array("2 gram Na", $dietOrder['standard'])} checked{/if}>
					2 gram Na
					</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="Renal" {if in_array("Renal", $dietOrder['standard'])} checked{/if}>
					Renal
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="Gluten Restricted" {if in_array("Gluten Restricted", $dietOrder['standard'])} checked{/if}>
					Gluten Restricted
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="Fortified/High Calorie" {if in_array("Fortified/High Calorie", $dietOrder['standard'])} checked{/if}>
					Fortified/High Calorie
			</label>
		
			<input type="text" name="diet_order_other" class="other-input checkbox-input" maxlength="45" placeholder="Enter other diet orders..." style="width: 350px" value="{$patientInfo->diet_info_other}{$dietOrder['other']}">
			
		{else}
			<label for="regular" class="checkbox-label">
				<input id="regular" class="checkbox" type="checkbox" name="diet_order[]" value="Regular" {if in_array("Regular", $dietOrder['standard'])} checked{/if}>
				Regular
			</label>
			<label for="aha-cardiac" class="checkbox-label">
				<input id="aha-cardiac" type="checkbox" name="diet_order[]" value="AHA/Cardiac" {if in_array("AHA/Cardiac", $dietOrder['standard'])} checked{/if}>
				AHA/Cardiac
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="No Added Salt" {if in_array("No Added Salt", $dietOrder['standard'])} checked{/if}>
				No Added Salt
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="RCS" {if in_array("RCS", $dietOrder['standard'])} checked{/if}>
				RCS
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="2 gram Na" {if in_array("2 gram Na", $dietOrder['standard'])} checked{/if}>
				2 gram Na
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="Renal" {if in_array("Renal", $dietOrder['standard'])} checked{/if}>
				Renal
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="Gluten Free" {if in_array("Gluten Free", $dietOrder['standard'])} checked{/if}>
				Gluten Free
			</label>
			<label class="checkbox-label">
				<input type="checkbox" name="diet_order[]" value="Fortified/High Calorie" {if in_array("Fortified/High Calorie", $dietOrder['standard'])} checked{/if}>
				Fortified/High Calorie
			</label>
			<input type="text" name="diet_order_other" class="other-input checkbox-input" maxlength="45" placeholder="Enter other diet orders..." style="width: 350px" value="{$patientInfo->diet_info_other}{$dietOrder['other']}">
		{/if}
	</div>


	<!-- Texture Section -->
	<div class="form-header2">Texture</div>
	<div class="checkbox">
		<label class="checkbox-label">
			<input type="checkbox" name="texture[]" value="Regular" {if in_array('Regular', $textures['standard'])} checked{/if}>
			Regular
		</label>
		<label class="checkbox-label">
				<input type="checkbox" name="texture[]" value="Easy to Chew" {if in_array('Easy to Chew', $textures['standard'])} checked{/if}>
				Easy to Chew
		</label>
		<label class="checkbox-label">
				<input type="checkbox" name="texture[]" value="Soft & Bite Sized" {if in_array('Soft & Bite Sized', $textures['standard'])} checked{/if}>
				Soft &amp; Bite Sized
		</label>
		<label class="checkbox-label">
				<input type="checkbox" name="texture[]" value="Minced & Moist" {if in_array('Minced & Moist', $textures['standard'])} checked{/if}>
				Minced &amp; Moist
		</label>
		<label class="checkbox-label">
				<input type="checkbox" name="texture[]" value="Puree" {if in_array('Puree', $textures['standard'])} checked{/if}>
				Puree
		</label>
		<label class="checkbox-label">
				<input type="checkbox" name="texture[]" value="Chopped" {if in_array('Chopped', $textures['standard'])} checked{/if}>
				Chopped
		</label>
		<label class="checkbox-label">
				<input type="checkbox" name="texture[]" value="Chopped Meat" {if in_array('Chopped Meat', $textures['standard'])} checked{/if}>
				Chopped Meat
		</label>
		<label class="checkbox-label">
			<input type="checkbox" name="texture[]" value="Mechanical Soft" {if in_array('Mechanical Soft', $textures['standard'])} checked{/if}>
			Mechanical Soft
		</label>
		<label class="checkbox-label">
			<input type="checkbox" name="texture[]" value="Cut Up" {if in_array('Cut Up', $textures['standard'])} checked{/if}>
			Cut Up
		</label>
	{if in_array('Tube Feeding', $textures['standard'])}
		<label class="checkbox-label">
			<input type="checkbox" name="texture[]" value="Tube Feeding" {if in_array('Tube Feeding', $textures['standard'])} checked{/if}>
			Tube Feeding
		</label>
	{/if}
		<br/>
		<label for="liquid" class="checkbox-label">Liquid:
		<select name="texture[]" id="liquid-select">
			<option value="">Select Liquid Type...</option>
			{if true or $selectedLocation->public_id == "ATW500KSj"}
			<option value="Liquidised" {if in_array("Liquidised", $textures['standard'])} selected{/if}>Liquidised</option>
			<option value="Slightly Thick" {if in_array("Slightly Thick", $textures['standard'])} selected{/if}>Slightly Thick</option>
			<option value="Mildly Thick" {if in_array("Mildly Thick", $textures['standard'])} selected{/if}>Mildly Thick</option>
			<option value="Moderately Thick" {if in_array("Moderately Thick", $textures['standard'])} selected{/if}>Moderately Thick</option>
			<option value="Extremely Thick" {if in_array("Extremely Thick", $textures['standard'])} selected{/if}>Extremely Thick</option>
			{/if}
			<option value="Nectar Thick Liquids" {if in_array("Nectar Thick Liquids", $textures['standard'])} selected{/if}>Nectar Liquid</option>
			<option value="Honey Thick Liquids" {if in_array("Honey Thick Liquids", $textures['standard'])} selected{/if}>Honey Liquid</option>
			<option value="Pudding Thick Liquids" {if in_array("Pudding Thick Liquids", $textures['standard'])} selected{/if}>Pudding Liquid</option>
			<option value="Clear Liquid" {if in_array("Clear Liquid", $textures['standard'])} selected{/if}>Clear Liquid</option>
			<option value="Full Liquid" {if in_array("Full Liquid", $textures['standard'])} selected{/if}>Full Liquid</option>
			<option value="Fluid Restriction" {if in_array("Fluid Restriction", $textures['standard'])} selected{/if}>Fluid Restriction</option>
			{* <option value="Other" {if in_array("Other Liquid", $textures['standard'])} selected{/if}>Other</option> *}
		</select>
		</label>
		<input type="text" id="other-texture-input" maxlength="25" name="texture_other" size="45" class="other-input" placeholder="Enter other texture info... (25 character limit)" value="{$patientInfo->texture_other}{$textures['other']}">
	</div>

	<!-- Other Section -->
	<div class="form-header2">Other</div>
	<div class="checkbox">
		<label class="checkbox-label">
			<input type="checkbox" name="other[]" value="Tube Feeding" {if in_array('Tube Feeding', $other['standard']) or in_array('Tube Feeding', $textures['standard'])} checked{/if}>
			Tube Feeding
		</label>
		<label class="checkbox-label">
			<input type="checkbox" name="other[]" value="Isolation" {if in_array("Isolation", $other['standard'])} checked{/if}>
			Isolation
		</label>
		<label class="checkbox-label">
			<input type="checkbox" id="other-fluidRestriction-checkbox" name="other[]" value="Fluid Restriction" {if in_array("Fluid Restriction", $other['standard'])} checked{/if}>
			Fluid Restriction
		</label>
		<label class="checkbox-label">
			<input type="text" id="other-other-input" name="fluid_other" maxlength="25" placeholder="Enter fluid restriction info... (25 character limit)" value="{$patientInfo->fluid_other}{$other['other']}">
		</label>
		{* extra to bypass blank check for the other so isolation can be removed. *}
		{* <input style="display:none;" type="hidden" id="other-other-input" name="other[]" maxlength="25" placeholder="Enter fluid restriction info... (25 character limit)" value=""> *}

	<!-- Portion Size Section -->
		<label class="checkbox-label">
			<input type="radio" name="portion_size" value="Small" {if $patientInfo->portion_size == "Small"} checked{/if}>
			Small
		</label>
		<label class="checkbox-label">
			<input type="radio" name="portion_size" value="Regular" {if $patientInfo->portion_size == "Regular"} checked{elseif $patientInfo->portion_size == "Medium"} checked{elseif !isset($patientInfo->portion_size)} checked{/if}>
			Regular
		</label>
		<label class="checkbox-label">
			<input type="radio" name="portion_size" value="Large" {if $patientInfo->portion_size == "Large"} checked{/if}>
			Large
		</label>
	</div>
	
	<!-- Selective Warning -->
	<div class="noPrefWarn" style="display:none;">
		⚠️ Less than 2-3 preferences entered for this Guest!
	</div>
	<br>
	<br>
	<div class="text-right">
		<input type="submit" class="btn btn-info" value="Save">
	</div>

</form>

