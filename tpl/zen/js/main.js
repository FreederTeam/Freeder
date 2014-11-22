

$(document).ready(function() {
    // Toggle sections
    $('.toggle').addClass('closed');
    $('.toggle--btn').click(function(ev) {
        $(this).parent().toggleClass('closed');
    });


    // Close buttons
    $('.close-btn').click(function(ev){
        var article = $(this).parents('.article');
        var entry_id = article.attr('id').substr(6); // article.id = 'entry-345bc6a43b'
        var target = '{$base_url}api/tags.php?entry=' + entry_id + '&tag=_read';
        $.get(target, function(data){
            article.remove();
        }, 'json');
    });
});

