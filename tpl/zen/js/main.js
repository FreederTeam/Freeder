

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

    // Modal box
    $('.modalbox--close').click(function(ev){
        $('.modalbox').addClass('hidden');
    });

    // Article navigation following view
    $('.main').scroll(function(ev){
        $('.article--nav').each(function(i){
            var article = $(this).parents('.article');

            // If the article is the first one displayed
            if (article.offset().top < 0 && article.offset().top + article.outerHeight() - $(this).outerHeight() > 0) {
                // We fix its navigation menu
                $(this).css('top', -article.offset().top);
            }
            else {
                //$(this).css('top', '');
            }
        });
    });
});

