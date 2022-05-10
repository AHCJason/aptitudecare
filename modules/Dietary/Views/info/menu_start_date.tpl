{assign disabled ""}
{if $authStatus eq false}
	{assign disabled 'disabled="disabled"'}
{/if}

{literal}
	<style>
		tr:nth-child(even){background: #CCC}
		.date-picker{width:100%}
	</style>
{/literal}


{if $authStatus}

<style>
	div.editable{ldelim}
		background: #eee;
		background-image: url("{$FRAMEWORK_IMAGES}/edit.png");
		background-repeat: no-repeat;
		background-position: right center;
	{rdelim}
</style>
{literal}
<script type="text/javascript">
var snackOn = "";
$(document).ready(function() {
    //Loop through all Labels with class 'editable'.
    $(".editable").each(function () {
        //Reference the Label.
        var label = $(this);

		if($(this).html() == "")
		{
			$(this).html("&nbsp");
		}
		
        //Add a TextBox next to the Label.
        label.after("<input type = 'text' class = 'date-picker' style = 'display:block;display:none' />");
        //Reference the TextBox.
        var textbox = $(this).next();
 
        //Set the name attribute of the TextBox.
        //textbox[0].name = this.id.replace("lbl", "txt");
        //console.log(textbox);
 
        //Assign the value of Label to TextBox.
        textbox.val(label.text());
 
        //When Label is clicked, hide Label and show TextBox.
        label.click(function () {
            $(this).hide();
            $(this).next().show();
			//$(this).next().attr("style", "display: block; margin: 0 auto; text-align: center; font-family: inherit; font-weight: 500; line-height: 1.1; color: inherit;");
            $(this).next().datepicker('show');
        });
 
        //When focus is lost from TextBox, hide TextBox and show Label.
        textbox.focusout(function () {
            $(this).hide();
			$(this).prev().html($(this).val());
            $(this).prev().show();
        });
    });
	$('.date-picker').datepicker({
		dateFormat: "M d, yy",
		changeMonth: true,    
    	changeYear: true,
		altFormat : "mm/dd/yy",
		altField: "#date_start",
		onSelect: function(dateText, inst) {
			//$(this).prev()[0].childNodes[0].nodeValue = dateText;
			$(this).prev().html(dateText);
			//console.log(dateText);
			//snackOn = dateText;

			//console.log(dateText);

			//$(this).prev().attr("value") has the value of the menu we are changing.
			$.ajax({
				type:"POST",
				url: $( "#start-date" ).attr("action"),
				data: $( "#start-date" ).serialize() + "&menu=" + $(this).prev().attr("value") + "&isAjax=true",
				success: function(data) {
					location.reload();
				},
				error: function(data) {
					alert(data.responseText);
					location.reload();
				},
			})
			
		},
		onClose : function(dateText, inst) {
			if(dateText == "") {
				$(this).prev().html("&nbsp;");
			}
			//console.log(inst.prev().value());
			//Serialized data
			//$( "#start-date" ).serialize() + "&menu=" + $(this).prev().attr("value"));
			//where to send it
			//$( "#start-date" ).attr("action");
		},
	});
});
</script>
{/literal}
{/if}

<div id="action-left">
	&nbsp;
</div>
<div id="center-title">
	{$this->loadElement("selectLocation")}
</div>
<div id="action-right">
	&nbsp;
</div>

<div class="clear"></div>
<h1>Set Menu Start Date</h1>

<div class="current-menu-info">
	<p><strong>Current Menu</strong>: {$currentMenu->name}</p>
	<p><strong>Date Started</strong>: {$currentMenu->date_start|date_format}</p>
</div>

<form id="start-date" name="start_date" method="post" action="{$SITE_URL}">
	<input type="hidden" name="page" value="info">
	<input type="hidden" name="action" value="submitStartDate">
	<input type="hidden" name="location" value="{$location->public_id}">
	<input type="hidden" name="path" value="{$current_url}">
	<br><br>
	<table class="form text-center">
		<tr>
		{if $authStatus eq false}
			<td colspan="2"><strong>Menu Start Dates:</strong></td>
		{else}
			<td colspan="2"><strong>Choose the menu:</strong></td>
		{/if}
		</tr>
		{foreach from=$availableMenus item="menu"}
			<tr>
				<td class="text-center">
				{*{if $authStatus eq false}*}
					<p>{$menu->name}{if $currentMenu->menu_id eq $menu->menu_id}✱{/if}</p>
{*				{else}
					<input type="radio" name="menu" value="{$menu->public_id}">{$menu->name}{if $currentMenu->menu_id eq $menu->menu_id}✱{/if}
				{/if}*}
				</td>
				<td><div class="editable" style="width: 100%;" value="{$menu->public_id}">{$menu->date_start|date_format}</div></td>
			</tr>
		{/foreach}

		<tfoot>
			<td>✱ denotes current menu.</td>
		</tfoot>
	{if $authStatus}
		<tr>
			<td>&nbsp;</td>
		</tr>
		<div style="display:none">
		{*<tr>
			<td><strong>Select the start date</strong>:</td>
		</tr>
		<tr>
			<td>*}<input type="input" id="date_start" name="date_start" class="datepicker"></td>{*
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="text-right"><input type="submit" value="Save"></td>
		</tr>*}
		</div>
	{/if}
	</table>
</form>

<br>
<br>
<div id="page-info">
	<p>HOW TO: Click the date or the blank to the right of the menu to set the date. </p>
	<p>NOTE: You only need to change the menu twice per year when you are ready to change to a new menu. For example, if you are currently on the Fall/Winter menu you will not need to use this page until just prior to changing to the Spring/Summer menu.</p>
	<p>PLEASE REMEMBER: Once the menu is set to start it will continue to rotate through the menu until it reaches the start date for the new menu season. You can see the start dates for each menu above.</p>


</div>
