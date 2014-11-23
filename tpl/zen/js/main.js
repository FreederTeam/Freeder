

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

    // Submenu
    $('.toggle-submenu').click(function(ev){
        var id = $(this).attr('id').substr(5); // this.id = 'open-submenu-foo'
        var section = $('#'+id);
        var wrapper = $('.submenu--wrapper');
        if (section.is(":visible") && wrapper.hasClass('open')) {
            wrapper.removeClass('open');
        } else {
            $('.submenu section').hide();
            section.show();
            wrapper.addClass('open');
        }
    });
});

