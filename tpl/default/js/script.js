// == Init
	$(document).ready(function() {
		svgeezy.init(false, 'png');
	});

// == Functions
	function ajax(caller, target) {
		var xhr = new XMLHttpRequest();
		xhr.open('GET', target, true);
		xhr.onload = function () {
			if (xhr.status == 200) {
				var article = caller.parentNode.parentNode;
				article.parentNode.removeChild(article);
			}
		};
		xhr.send(null);
		return false;
	}

// == Display Tags
	$(".articleDisplayTagButton").click(function(){
		$(this).siblings("div").slideToggle(200);
	});
