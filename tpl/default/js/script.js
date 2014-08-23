// == Init
	$(document).ready(function() {
		svgeezy.init(false, 'png');
	});

// == Functions
	function ajax(caller, target) {
		$.get(target, function(data) {
			if (data == 'OK') {
				var article = caller.parentNode.parentNode;
				article.parentNode.removeChild(article);
			}
		});
		return false;
	}


	function tag(caller, entry_id, tag_baselink) {
		var tag_value = $('#newTag').val();
		$.get('api/tags.php?entry='+entry_id+'&tag='+tag_value, function(data) {
			if (data == 'OK') {
				$('#newTag').val("");
				var article = caller.parentNode.parentNode;
				$('ul', $(article)).append('<li><a href="'+tag_baselink+tag_value+'">'+tag_value+'</a></li>');
			}
		});
	}

// == Display Tags
	$(".DisplayTagsButton").click(function(){
		$(this).siblings(".ArticleTagsList").slideToggle(200);
	});
