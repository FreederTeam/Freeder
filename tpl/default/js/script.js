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
	function tag_entry(caller, entry_id, tag_value) {
		var callback;
		switch (tag_value) {
			case "_read":
				callback = function(c, d) {
					c.innerHTML = "Unread";
					var article = c.parentNode.parentNode;
					article.parentNode.removeChild(article);
				}
				break;

			case "_sticky":
				callback = function(c, d) {
					c.innerHTML = "Unstick";
				}
				break;

			default:
				callback = function (c, d) { };
				break;
		}
		ajax(caller, 'api/tags.php?entry='+entry_id+'&tag='+tag_value, callback);
	}


	/**
	 * Remove a tag on an entry
	 */
	function untag_entry(caller, entry_id, tag_value) {
		var callback;
		switch (tag_value) {
			case "_read":
				callback = function(c, d) {
					c.innerHTML = "Read";
				}
				break;

			case "_sticky":
				callback = function(c, d) {
					c.innerHTML = "Stick";
				}
				break;

			default:
				callback = function (c, d) { };
				break;
		}
		ajax(caller, 'api/tags.php?entry='+entry_id+'&tag='+tag_value+'&remove=1', callback);
	}


	/**
	 * Mark all entries as read.
	 */
	function tag_all(caller, tag_value) {
		ajax(caller, 'api/tags.php?all=1&tag='+tag_value, function(c, d) {
			$('main article').remove();
		});
	}


// == Display Tags
	$(".DisplayTagsButton").click(function(){
		$(this).siblings(".ArticleTagsList").slideToggle(200);
	});

// == Modal Box
	function fillModalbox(title, content, className) {
		if (className != undefined) {
			$("#JsModalbox").addClass(className);
		}
		$("#JsModalbox-h1").html(title);
		$("#JsModalbox-p").html(content);

		$("#JsOverlay").toggle();
		$("#JsModalbox").toggle();
	}
