// == Init
	$(document).ready(function() {
		svgeezy.init(false, 'png');
	});

// == Functions
	/**
	 * Sends an ajax query to the target URL and run callback upon success.
	 */
	function ajax(caller, target, callback) {
		$.get(target, function(data) {
			if (data == 'OK') {
				callback(caller, data);
			}
		});
		return false;
	}


	/**
	 * Add a new tag to an entry via the form.
	 */
	function tag_form(caller, entry_id, tag_baselink) {
		var tag_value = $('#newTag').val();
		ajax(caller, 'api/tags.php?entry='+entry_id+'&tag='+tag_value, function(c, d) {
			$('#newTag').val("");
			var article = c.parentNode.parentNode;
			$('ul', $(article)).append('<li><a href="'+tag_baselink+tag_value+'">'+tag_value+'</a></li>');
		});
	}


	/**
	 * Add a tag to an entry
	 */
	function tag_entry(caller, entry_id, tag) {
		ajax(caller, 'api/tags.php?entry='+entry_id+'&tag='+tag_value, function(c, d) {});
	}


	/**
	 * Mark an entry as read.
	 */
	function read_entry(caller, entry_id) {
		ajax(caller, 'api/mark_as_read.php?entry='+entry_id, function(c, d) {
			var article = c.parentNode.parentNode;
			article.parentNode.removeChild(article);
		});
	}


	/**
	 * Mark all entries as read.
	 */
	function read_all(caller) {
		ajax(caller, 'api/mark_as_read.php?all=1', function(c, d) {
			$('main article').remove();
		});
	}


// == Display Tags
	$(".DisplayTagsButton").click(function(){
		$(this).siblings(".ArticleTagsList").slideToggle(200);
	});
