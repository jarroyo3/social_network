$(document).ready(function () {
    NotificationService.setNotifications();
    setInterval(function(){
        NotificationService.setNotifications();
        PrivateMessageService.setPMNotification();
    }, 60000); // cada minuto
});

var NotificationService = (function () {
    return {
        displayNotificationLabel: function () {
            if ($('.label-notifications').text() == 0) {
                $('.label-notifications').hide();
            } else {
                $('.label-notifications').show();
            }
        },
        setNotifications: function () {
            $.ajax({
                url: URL + '/notifications/get',
                type: 'GET',
                success: function (response) {
                    $('.label-notifications').html(response);
                    NotificationService.displayNotificationLabel();
                }
            });
        }
    };
})();

var PrivateMessageService = (function () {
    return {
        displayPMLabel: function () {
            if ($('.label-notifications-msg').text() == 0) {
                $('.label-notifications-msg').hide();
            } else {
                $('.label-notifications-msg').show();
            }
        },
        setPMNotification: function () {
            $.ajax({
                url: URL + '/private-message/notification/get',
                type: 'GET',
                success: function (response) {
                    $('.label-notifications-msg').html(response);
                    PrivateMessageService.displayPMLabel();
                }
            });
        }
    };
})();