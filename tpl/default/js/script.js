$(document).ready(function() {
	svgeezy.init(false, 'png');
});

function ajax(caller, target) {
	caller.innerHTML = "Non Lu";

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
