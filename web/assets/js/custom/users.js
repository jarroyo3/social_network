$(document).ready(function () {
    var ias = jQuery.ias({
        container: '.box-users',
        item: '.user-item',
        pagination: '.pagination',
        next: '.pagination .next_link',
        triggerPageThreshold: 4,

    });

    ias.extension(new IASTriggerExtension({
        text: 'Ver más personas',
        offset: 3
    }));

    ias.extension(new IASSpinnerExtension({
        src: URL + '../assets/images/ajax/loader.gif'
    }));

    ias.extension(new IASNoneLeftExtension({
        text: 'No has más personas'
    }));

    ias.on('ready', function(event){
        FollowService.followUser();
        FollowService.unfollowUser();
    });

    ias.on('rendered', function (event) {
        FollowService.followUser();
        FollowService.unfollowUser();
    });

});

var FollowService = (function(){
    return {
        followUser: function() {
            $('.btn-follow').unbind('click').click(function(){
                $(this).addClass('hidden');
                $(this).parent().find('.btn-unfollow').removeClass('hidden');
                $.ajax({
                    url: URL + '/follow',
                    type: 'post',
                    data: {followed: $(this).data('followed')},
                    success: function(response) {
                        console.log(response);
                    }
                })
            });
        },
        unfollowUser: function() {
            $('.btn-unfollow').unbind('click').click(function () {
                $(this).addClass('hidden');
                $(this).parent().find('.btn-follow').removeClass('hidden');
                $.ajax({
                    url: URL + '/unfollow',
                    type: 'post',
                    data: { followed: $(this).data('followed') },
                    success: function (response) {
                        console.log(response);
                    }
                })
            });
        }
    }
})();