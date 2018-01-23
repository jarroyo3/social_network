$(document).ready(function () {
    var ias = jQuery.ias({
        container: '#timeline .block-content',
        item: '.publication-item',
        pagination: '#timeline .pagination',
        next: '#timeline .pagination .next_link',
        triggerPageThreshold: 5,

    });

    ias.extension(new IASTriggerExtension({
        text: 'Ver más publicaciones',
        offset: 3
    }));

    ias.extension(new IASSpinnerExtension({
        src: URL + '../../images/ajax/loader.gif'
    }));

    ias.extension(new IASNoneLeftExtension({
        text: 'No has más publicaciones'
    }));

    ias.on('ready', function (event) {
        PublicationService.initBootstrap();
        PublicationService.showPublicationImage();
        PublicationService.deletePublication();
        PublicationService.likeUnlike();
    });

    ias.on('rendered', function (event) {
        PublicationService.initBootstrap();
        PublicationService.showPublicationImage();
        PublicationService.deletePublication();
        PublicationService.likeUnlike();
    });

});

var PublicationService = (function () {
    return {
        showPublicationImage: function () {
            $('.btn-img').unbind('click').click(function () {
                $(this).parent().find('.pub-image').fadeToggle();
            })
        },
        deletePublication: function () {
            $('.btn-delete-pub').unbind('click').click(function () {
                if (confirm('¿Estás seguro que desea eliminar la publicación?')) {
                    $publicationDelete = $(this).parent().parent();
                    var idPublication = parseInt($(this).data('publicationid'));
                    $.ajax({
                        url: URL + '/publication/remove/' + idPublication,
                        type: 'POST',
                        success: function (msg) {
                            if (msg.indexOf('correctamente')) {
                                $publicationDelete.hide('slow', function () { $publicationDelete.remove(); });
                            }
                        }
                    })
                }
            });
        },
        initBootstrap: function () {
            $('[data-toggle="tooltip"]').tooltip();
        },
        likeUnlike: function () {
            $('.btn-like, .btn-unlike').unbind('click').click(function () {
                var action = '';
                $(this).addClass('hidden');
                if ($(this).hasClass('btn-like')) {
                    $(this).parent().find('.btn-unlike').removeClass('hidden');
                    action = 'like';
                } else if ($(this).hasClass('btn-unlike')) {
                    $(this).parent('.like').find('.btn-like').removeClass('hidden');
                    action = 'unlike';
                }
                $likeButton = $(this);
                var idPublication = parseInt($(this).data('publicationid'));
                $.ajax({
                    url: URL + '/' + action + '/' + idPublication,
                    type: 'POST',
                    success: function (msg) {
                        if (msg.indexOf('te gusta')) {
                            $likeButton.addClass('active');
                        } else if (msg.indexOf('Ya no te gusta')) {
                            $likeButton.removeClass('active');
                        }
                    }
                })

            })
        }
    }
})();