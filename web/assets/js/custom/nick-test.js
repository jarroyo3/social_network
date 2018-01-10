$(document).ready(function(){
    $(document).on('blur', '.nick-input', function(){
        var nick = $(this).val();
        $.ajax({
            url: URL + '/nick-test',
            data: {nick: nick},
            type: 'POST',
            success: function(response){
                console.log(response)
                if (response === 'used') {
                    $('.nick-input').css('border', "1px solid red");
                } else {
                    $('.nick-input').css('border', "1px solid green");
                }
            }
        })
    })
});