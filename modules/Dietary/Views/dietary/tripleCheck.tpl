<div class="row"">
	<div class="col-lg-4">
		{$this->loadElement("module")}
	</div>
	<div class="col-lg-4 text-center">
		{$this->loadElement("selectLocation")}
	</div>

	<div class="col-lg-4">
		<a id="tray-card-select-date" href="{$SITE_URL}/?module=Dietary&amp;page=patient_info&amp;action=meal_tray_card&amp;location={$location->public_id}&amp;patient=all&amp;pdf2=true" class="btn btn-primary pull-right">Tray Cards</a>

		<a id="meal-order-form-select-date" href="{$SITE_URL}/?module=Dietary&amp;page=menu&amp;action=meal_order_form&amp;location={$location->public_id}&amp;pdf2=true" class="btn btn-primary pull-right" target="_blank">Meal Order Forms</a>
	</div>
</div>

<h1>Triple Check</h1>
<input type="hidden" id="location" value="{$location->public_id}">
<input type="hidden" name="currentUrl" value="{$current_url}">
{if $output}
<pre>
{$output}
</pre>
{/if}

{if isset($pageContent)}
<pre>
{$pageContent}
</pre>
{/if}