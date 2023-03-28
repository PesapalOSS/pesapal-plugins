/**
 * Created by kraiba on 16/08/2016.
 */
function ajaxPostReact(url, dataPost, successCallback) {

$.ajax({
    type:"POST",
    url:url,
    data:dataPost,
    dataType:"json",
    cache:false,
    success:function (data) {
        successCallback(data);
    }.bind(this),
    error:function (data) {
        successCallback(data)
    }.bind(this)

})
}