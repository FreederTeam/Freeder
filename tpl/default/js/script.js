$(document).ready(function() {
	svgeezy.init(false, 'png');
});

function ajax(target) {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', target, false);
	xhr.send(null);
	return false;
}
