<script>
	$(document).ready(function() {
		$(".order").click(function(e) {
			e.preventDefault();
			var id = $(this).attr("id");
			var url = SITE_URL + "/?module=Dietary&page=reports&action=diet_census&location=" + $("#location").val() + "&orderby=" + id;
			window.location = url;
		});
	});
</script>