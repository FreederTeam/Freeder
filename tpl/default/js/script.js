// == Init
	$(document).ready(function() {
		// svg fallback
		svgeezy.init(false, 'png');
		// modalbox autolaunch
		ModalboxAutoLaunch();

		// init touch actions
		init_touch();

		// init tab in settings
		tabs_init();

		$(".Toggle").addClass("closed");
		$(".Toggle-btn").click(function(ev) {
			$(this).parent().toggleClass("closed");
		});

		$(".Share-button").click(function(ev) {
			$(this).parents().filter('.Article').find('.Article-share').toggle();
		});
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
		$.get(target, { "token": "{$token}" }, function(data) {
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
		ajax(caller, '{base_url}api/tags.php?entry='+entry_id+'&tag='+tag_value, function(c, d) {
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
	function tag_entry(caller, entry_id, tag_value, overload_callback=undefined) {
		var callback;
		switch (tag_value) {
			case "_read":
				callback = function(c, d) {
					c.innerHTML = "Unread";
					c.onclick = function () { untag_entry(c, entry_id, '_read') };

					// If on homepage and not feed view
					if ($(".UnreadNumber").length > 0) {
						var article = c.parentNode.parentNode;
						article.parentNode.removeChild(article);

						var unread_items = parseInt($('#ItemsNumberCounter').html());
						if (unread_items >= 1) {
							$('#ItemsNumberCounter').html(unread_items - 1);
						}
						if (unread_items <= 2) {
							$('#ItemsNumberPlural').html('');
						}
					}

					if (typeof(overload_callback) !== 'undefined') {
						overload_callback();
					}
				}
				break;

			case "_sticky":
				callback = function(c, d) {
					c.innerHTML = "Unstick";
					c.onclick = function() { untag_entry(c, entry_id, '_sticky') };

					if (typeof(overload_callback) !== 'undefined') {
						overload_callback();
					}
				}
				break;

			default:
				callback = function (c, d) {
					if (typeof(overload_callback) !== 'undefined') {
						overload_callback();
					}
				};
				break;
		}
		ajax(caller, '{$base_url}api/tags.php?entry='+entry_id+'&tag='+tag_value, callback);
	}


	/**
	 * Remove a tag on an entry
	 *
	 * @param: caller is the caller element
	 * @param: entry_id is the id of the entry to tag
	 * @param: tag_value is the tag to remove
	 */
	function untag_entry(caller, entry_id, tag_value, overload_callback=undefined) {
		var callback;
		switch (tag_value) {
			case "_read":
				callback = function(c, d) {
					c.innerHTML = "Read";
					c.onclick = function () { tag_entry(c, entry_id, '_read') };

					if ($(".UnreadNumber").length > 0) {
						var unread_items = parseInt($('#ItemsNumberCounter').html());
						$('#ItemsNumberCounter').html(unread_items + 1);

						if (unread_items <= 1) {
							$('#ItemsNumberPlural').html('s');
						}
					}

					if (typeof(overload_callback) !== 'undefined') {
						overload_callback();
					}
				}
				break;

			case "_sticky":
				callback = function(c, d) {
					c.innerHTML = "Stick";
					c.onclick = function () { tag_entry(c, entry_id, '_sticky') };

					if (typeof(overload_callback) !== 'undefined') {
						overload_callback();
					}
				}
				break;

			default:
				callback = function (c, d) {
					if (typeof(overload_callback) !== 'undefined') {
						overload_callback();
					}
				};
				break;
		}
		ajax(caller, '{$base_url}api/tags.php?entry='+entry_id+'&tag='+tag_value+'&remove=1', callback);
	}


	/**
	 * Mark all entries as read.
	 *
	 * @param: caller is the caller element
	 * @param: tag_value is the tag to add
	 */
	function tag_all(caller, tag_value) {
		ajax(caller, '{$base_url}api/tags.php?all=1&tag='+tag_value, function(c, d) {
			$('main article').remove();
			$('.ItemsNumber').hide();
		});
	}


// == Display Tags in the articles boxes

	/**
	 * Display the tag list and form in the article boxes
	 */
	$(".DisplayTagsButton").click(function(){
		$(this).siblings(".ArticleTagsList").slideToggle(200);
	});

// == Settings tabs
	/**
	 * Change tab on the settings page
	 */

	/**
	 * Set the view to the current tab
	 */
	function tabs_init() {
		if($(".TabContent").length > 0) {
			$(".currentTab").removeClass("currentTab");

			var toHide = $(".currentTabContent");
			toHide.toggle();
			toHide.removeClass("currentTabContent");

			var idToShow = window.location.hash;
			$(idToShow).parent().toggle();
			$(idToShow).parent().addClass("currentTabContent");

			$("a[data-targetid="+idToShow.substring(1)+"]").parent().addClass("currentTab");
		}
	}

	/**
	 * Handle actions on tab click
	 */
	function tabs_click() {
		//if current tab is clicked, don't do the hole process
		if ($(this).parent().hasClass("currentTab") == false) {
			$(".currentTab").removeClass("currentTab");
			$(this).parent().addClass("currentTab");

			var toHide = $(".currentTabContent");
			toHide.toggle();
			toHide.removeClass("currentTabContent");

			var idToShow = $(this).data("targetid");
			idToShow = "#"+idToShow; //who said it's ugly ?
			$(idToShow).parent().toggle();
			$(idToShow).parent().addClass("currentTabContent");
		}
	}
	$(".OneTab-a").click(tabs_click);

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

// == Touch moves
	/**
	 * Check wether the device has touch capabilities or not
	 */
	function is_touch_device() {
		return !!('ontouchstart' in window) // works on most browsers
			|| !!('onmsgesturechange' in window); // works on ie10
	};

	/**
	 * Init touch moves
	 */
	function init_touch() {
		if(is_touch_device() && $('.Article').length > 0) {
			var mc = new Hammer(document.querySelector('.Article'));
			mc.on("panleft panright", slide_effect);
			mc.on('hammer.input', function(ev) {
				if(ev.isFinal) {
					slide_to_read(ev);
				}
			});
		}
	}

	/**
	 * Handle slide effect
	 */
	function slide_effect(e) {
		e.preventDefault();
		target = $(e.target);
		if (!target.hasClass('.Article')) {
			target = target.parents('.Article');
		}

		if(Math.abs(e.deltaX) >= 2*Math.abs(e.deltaY)) {
			$(target).css('transform', 'translate('+e.deltaX+'px,0)');
			$(target).css('left', e.deltaX);
			$(target).css('opacity', 1-Math.abs(e.deltaX)/$(target).width())
		}
		return false;
	}

	/**
	 * "Slide to mark read / unread" event handler
	 */
	function slide_to_read(e) {
		target = $(e.target);
		if (!target.hasClass('.Article')) {
			target = target.parents('.Article');
		}

		if(Math.abs(e.deltaX) > $(target).width() / 2) {
			console.log($('Controls .Controls-button:first-child', target));
			tag_entry($('Controls .Controls-button:first-child', target), target.attr('id'), '_read');
		}
		$(target).css('transform', 'translate(0,0)');
		$(target).css('opacity', '1');
		return false;
	}


// == Options
if ({$config->open_items_new_tab} > 0) {
	$(".ArticleContent h1:first-child a").click(function () {
		console.log($('.Controls .Read-button', $(this).parents(".Article")).trigger("click"));
	});
}
