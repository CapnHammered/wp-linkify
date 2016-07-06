jQuery(document).ready(function() {
	jQuery('.linkify.unloaded').each(function() {
		var linkify = this;
		jQuery(linkify).find('img').attr('src', LINKIFY_URL + 'img/ajax-loader.gif')
		jQuery.ajax({
			url: LINKIFY_URL + 'ajax/helper.php', 
			method: 'GET',
			data: { 
				url: encodeURIComponent(jQuery(this).find('a').attr('href')) 
			},
			success: function(data) {
				data = JSON.parse(data);
				jQuery(linkify).find('span:first-child').html(data.title);
				jQuery(linkify).find('p').html(data.description);
				jQuery(linkify).find('img').attr('src', data.image).error(function() {
					jQuery(this).attr('src', LINKIFY_URL + 'img/no-thumbnail-available.png');
				});
			}
		});
	});
});