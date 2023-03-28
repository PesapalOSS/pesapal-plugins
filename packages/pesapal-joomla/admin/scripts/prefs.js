$(document).on('submit','#prefs-form',function(){
    // code
    var dataPost ={consumer_secret: $('#consumer_secret').val(),consumer_key: $('#consumer_key').val()}
    var url = 'index.php?option=com_pesadon&task=saveParams&format=raw'
    $.ajax({
        type:"POST",
        url:url,
        data:dataPost,
        dataType:"json",
        cache:false,
        success:function (data) {

            $('.message').html('<div class="alert alert-success"><div class="alert-message">Settings Updated.</div></div>')
        },
        error:function (data) {
            $('.message').html('<div class="alert alert-success"><div class="alert-message">Settings Updated.</div></div>')
        }

    });
    return false;
});