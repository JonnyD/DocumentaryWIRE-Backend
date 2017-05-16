
console.log("Hello1");


$(document).ready(function(){
    $(document).on('click', '.like-documentary .like, .like-documentary .liked', function (e) {
        console.log("Hello2");
        var el = $(this);
        el.prop('disabled', true);

        var actionType = el.attr("data-action");

        var data = {
            actionType: actionType,
            documentaryId: el.attr("data-did")
        };

        $.ajax({
            type: 'POST',
            url: 'http://localhost/DocumentaryWIRE3/web/suarez_app_dev.php/ajax/like/documentary',
            data: data,
            dataType: 'json',
            error: function () {
                alert('Error. please log in.');
                el.removeClass('liking');
                el.prop('disabled', false);
            },
            beforeSend: function () {
                el.addClass('liking');
            },
            success: function (r) {
                if (r.error != '') {
                    alert(r.error);
                    return false;
                }
                if (actionType == 'like') {
                    $('.like-documentary').each(function () {
                        var like = $(this).find('.like');
                        if (like.attr('data-did') == el.attr('data-did')) {
                            $(like).text("Remove from Favorites");
                            $(like).attr('data-action', 'unlike');
                            $(like).removeClass('like');
                            $(like).addClass('liked');
                        }
                    });

                    $('.dp-post-likes').each(function () {
                        var like = $(this);
                        if (like.attr('data-did') == el.attr('data-did')) {
                            $(like).addClass('liked');
                            var count = $(this).find('.count');
                            var amount = parseInt(count.text()) + 1;
                            $(count).text(amount);
                        }
                    });
                }
                else if (actionType == 'unlike') {
                    $('.like-documentary').each(function () {
                        var like = $(this).find('.liked');
                        if (like.attr('data-did') == el.attr('data-did')) {
                            $(like).text("Add to Favorites");
                            $(like).attr('data-action', 'like');
                            $(like).removeClass('liked');
                            $(like).addClass('like');
                        }
                    });

                    $('.dp-post-likes').each(function () {
                        var like = $(this);
                        if (like.attr('data-did') == el.attr('data-did')) {
                            $(like).removeClass('liked');
                            var count = $(this).find('.count');
                            var amount = parseInt(count.text()) - 1;
                            $(count).text(amount);
                        }
                    });
                }

                el.prop('disabled', false);
            }
        });

        e.preventDefault();
    });
});