// == Init
	$(document).ready(function() {
		// svg fallback
		svgeezy.init(false, 'png');
		// modalbox autolaunch
		ModalboxAutoLaunch();

	});

// == Functions
	/**
	 * Sends an ajax query to the target URL and run callback upon success.
	 *
	 * @param: caller is the caller element.
	 * @param: target is the URL to call
	 * @param: callback is a function to call upon successful AJAX call
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
	 *
	 * @param: caller is the caller element
	 * @param: entry_id is the id of the entry to tag
	 * @param tag_baselink is the baselink for the tags (as in template)
	 */
	function tag_form(caller, entry_id, tag_baselink) {
		var tag_input = $('input[name="newTag"]', caller);
		var tag_value = tag_input.val();
		ajax(caller, 'api/tags.php?entry='+entry_id+'&tag='+tag_value, function(c, d) {
			tag_input.val("");
			var article = c.parentNode.parentNode;
			$('ul', $(article)).append('<li class="TagList-completeTag CompleteTag"><a class="TagList-tagName TagName" href="'+tag_baselink+tag_value+'">'+tag_value+'</a></li>');
			$('.Side-tagList').append('<li class="TagList-completeTag CompleteTag"><a class="TagList-tagName TagName" href="'+tag_baselink+tag_value+'">'+tag_value+'</a></li>');
		});
		return false;
	}


	/**
	 * Add a tag to an entry
	 *
	 * @param: caller is the caller element
	 * @param: entry_id is the id of the entry to tag
	 * @param: tag_value is the tag to add
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
	 *
	 * @param: caller is the caller element
	 * @param: entry_id is the id of the entry to tag
	 * @param: tag_value is the tag to remove
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
	 *
	 * @param: caller is the caller element
	 * @param: tag_value is the tag to add
	 */
	function tag_all(caller, tag_value) {
		ajax(caller, 'api/tags.php?all=1&tag='+tag_value, function(c, d) {
			$('main article').remove();
		});
	}


// == Display Tags
	/**
	 * Display the tag list and form
	 */
	$(".DisplayTagsButton").click(function(){
		$(this).siblings(".ArticleTagsList").slideToggle(200);
	});

// == Modal Box
	/**
	 * Autolaunch of the modalbox at the page load if modalbox is not empty
	 */
	function ModalboxAutoLaunch() {
		if ($("#JsModalbox-p").html().length > 0) {
			ModalboxDisplay();
		}
	}

	/**
	 * Fill the modal box
	 * @param title The modalbox title
	 * @param content The modalbox content
	 */
	function ModalboxFill(title, content) {
		$("#JsModalbox-h1").html(title);
		$("#JsModalbox-p").html(content);
		}

	/**
	 * Display the modal box
	 */
	function ModalboxDisplay() {
		$("#JsOverlay").toggle();
		$("#JsModalbox").toggle();

	}

	/**
	 * Close the modal box
	 */
	function ModalboxClose() {
		$("#JsOverlay").toggle();
		$("#JsModalbox").toggle();

	}

// == Touch movements
	/**
	 * Check wether the device has touch capabilities or not
	 */
	function is_touch_device() {
		return !!('ontouchstart' in window) // works on most browsers
			|| !!('onmsgesturechange' in window); // works on ie10
	};

	/**
	 * Handle slide effect
	 */
	function slide_effect(e) {
		e = e || window.event;
		e.preventDefault();
		e.gesture.preventDefault();
		var target = e.target || e.srcElement;

		if(Math.abs(e.gesture.deltaX) > 10 && Math.abs(e.gesture.deltaX) >= 2*Math.abs(e.gesture.deltaY)) {
			$(target).css('transform', 'translate('+e.gesture.deltaX+'px,0)');
			$(target).css('left', e.gesture.deltaX);
			$(target).css('opacity', 1-Math.abs(e.gesture.deltaX)/$(target).width())
		}
		return false;
	}

	/**
	 * "Slide to mark read / unread" event handler
	 */
	function slide_to_read(e) {
		e = e || window.event;
		var target = e.target || e.srcElement;

		if(Math.abs(e.gesture.deltaX) >= 2*Math.abs(e.gesture.deltaY) && Math.abs(e.gesture.deltaX) > $('.articles-button-bar', $(target)).width()) {
			var id = $('.anchor', $(target)).attr('id');
			readThis($('.read_button', $(target)), id, true, function() {
				if(!$('body').hasClass('no-animations')) {
					$('.feedArticle').css('transform', 'translate(0,0)');
					$('.feedArticle').css('opacity', '1');
				}
			});
		}
		else {
			$('').css('transform', 'translate(0,0)');
			$('').css('opacity', '1');
		}
		return false;
	}
