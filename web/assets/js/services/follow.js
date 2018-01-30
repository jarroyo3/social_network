var FollowService = (function () {
    return {
        followUser: function () {
            $('.btn-follow').unbind('click').click(function () {
                $(this).addClass('hidden');
                $(this).parent().find('.btn-unfollow').removeClass('hidden');
                $.ajax({
                    url: URL + '/follow',
                    type: 'post',
                    data: { followed: $(this).data('followed') },
                    success: function (response) {
                        console.log(response);
                    }
                })
            });
        },
        unfollowUser: function () {
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