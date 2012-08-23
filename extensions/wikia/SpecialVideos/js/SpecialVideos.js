var SpecialVideos = {
	init: function() {
		$('.WikiaDropdown').wikiaDropdown({
			onChange: function(e, $target) {
				var currSort = this.$selectedItemsList.text(),
					newSort = $target.text();

				if(currSort != newSort) {
					var sort = $target.data('sort');
					window.location.search = "?sort="+sort;
				}
			}
		});
		
		$('.addVideo').addVideoButton({
			gaCat: 'testing',
			callback: function() {
				alert('added');
			}
		});
	}
};

$(function() {
	SpecialVideos.init();
});