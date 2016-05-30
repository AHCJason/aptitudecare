<!-- /modules/Dietary/Views/photos/manage_photos.tpl -->
<script>
	$(document).ready(function() {
		var table = null;
		var key = null;
		var photoId = null;
		var form = null;
		var data = null;
		var formCount = $("table.form").length;

		$(".fancybox").fancybox();

				{literal}
	    $(".photo-tag").tagit({
	    	// the photo-key is not being loaded because the field name is being set when the dom
	    	// loads, not when a tag is entered.
	    	//fieldName: "photo[" + $(this).next("input.photo-key").val() + "][photo_tag][]",
	    	beforeTagAdded: function() {
	    	},
	    	afterTagAdded: function() {
	    	},
	    	fieldName: "photo_tag[]",
	    	availableTags: fetchOptions("PhotoTag"),
	    	autocomplete: {delay:0, minLength: 2},
	    	showAutocompleteOnFocus: false,
	    	caseSensitive: false,
	    	allowSpaces: true,
		    beforeTagRemoved: function(event, ui) {
		        // if tag is removed
		        var photoId = $(this).parent().parent().parent().parent().parent().children("input:hidden:first").val();
		        var tagName = ui["tagLabel"];
		        $.post(SITE_URL, {
		        	page: "photos",
		        	action: "delete_tag",
		        	photo_id: photoId,
		        	tag_name: tagName
		        	}, function (e) {
		        		
		        	}, "json"
		        );
		    }

	    }); 


	    function fetchOptions(type){
        	var choices = "";
        	var array = [];
        	var runLog = function() {
        		array.push(choices);
        	};

        	var options = $.get(SITE_URL, {
        		page: "Photos",
        		action: "fetchTags",
        		type: type
        		}, function(data) {
        			$.each(data, function(key, value) {
        				choices = value.name;
        				runLog();
        			});
        		}, "json"
        	);

        	return array;
        }
      
		{/literal}


		$("input#save-photo").on("click", function(e) {
			e.preventDefault();
			table = $(this).parent().parent().parent();
			key = table.parent().parent().children("input:hidden:first").val()
			photoId = table.parent().children("input:hidden:first").val();
			form = $("#photo-info-" + key);
			data = $("#photo-info-" + key).serialize();

			$.ajax({
				type: 'post',
				url: SITE_URL + "/?page=photos&action=save_photo_info&photo_id=" + photoId,
				data: data,
				success: function() {

				}
			});
		
		});


		var timeoutID = null;

		function findPhotos(str) {
			$.ajax({
				type: 'post',
				url: SITE_URL,
				data: {
					page: "photos",
					action: "search_photos",
					facility: $("#selected-facility").val(),
					term: str
				},
				success: function(data) {
					var $container = $("#image-container");
					$container.empty();
					$("#page-links").empty();
					$.each(data, function(key, value) {
						$container.append('<a class="fancybox image-item" rel="fancybox-thumb" href="' + SITE_URL + '/files/dietary_photos/' + value.filename + '" title="' + value.name + '": "' + value.description + '"> <img src="' + SITE_URL + '/files/dietary_photos/thumbnails/' + value.filename + '" class="photo-image" alt=""></a>');
					});
				},
				dataType: "json"
			});
		}

		$("#search-pictures").keyup(function() {
			clearTimeout(timeoutID);
			var $target = $(this);
			console.log($target.val());
			timeoutID = setTimeout(function() { findPhotos($target.val()); }, 500);
		});



	});
</script>
<div id="page-header">
	<div id="action-left">&nbsp;</div>
	<div id="center-title">
		{$this->loadElement("selectLocation")}
	</div>
	<div id="action-right">{* Search: <input type="text" id="search-pictures" size="30"> *}</div>
</div>

<h1>Manage Photos</h1>

{if !empty ($photos)}
	{foreach from=$photos item=photo name=count key=key}
		<form id="photo-info-{$key}" method="post" action="{$SITE_URL}">
			<input type="hidden" id="form-key-{$key}" value="{$key}">
			<table class="form">
				<input type="hidden" class="photo-id" value="{$photo->public_id}">
				<tr>
					<td rowspan="2">
						<a class="fancybox" rel="fancybox-thumb" href="{$SITE_URL}/files/dietary_photos/{$photo->filename}" title="{$photo->name}: {$photo->description}">
							<img src="{$SITE_URL}/files/dietary_photos/thumbnails/{$photo->filename}" style="width:100px" alt=""></td>
						</a>
					<td>
						<strong>Name:</strong><br>
						<input type="text" name="name" value="{$photo->name}" size="69">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<strong>Description:</strong><br>
						<textarea name="description" class="description" placeholder="Photo description" cols="80" rows="4" >{$photo->description}</textarea>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2"><strong>Tags:</strong><br>
						<ul class="photo-tag">
							{foreach from=$photo->tag item=tag}
							<li>{$tag->name}</li>
							{/foreach}
						</ul>
					</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td>
						<strong>User Created:</strong><br>
						{$photo->username}
					</td>
					<td>
						<strong>Facilty:</strong><br>
						{$photo->location_name}
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						<input type="radio" name="approved" value="1">Approve<br>
						<input type="radio" name="approved" value="0">Reject
					</td>
					<td></td>
				</tr>
				<tr>
					<td colspan="3" class="text-right"><input type="submit" id="save-photo" value="Save"></td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
			</table>
		</form>
	{/foreach}

	<div class="clear"></div>
	<div id="page-links">
		{$var = "{$SITE_URL}?module=Dietary&page=photos&action=manage_photos&facility={$facility->public_id}"}
		{include file="elements/pagination.tpl"}	
	</div>


{else}
	<h2>The selected location has not yet uploaded any photos.</h2>
{/if}

<div id="dialog">Are you sure you want to reject these photos? The photos will be deleted and will not be recoverable.</div>