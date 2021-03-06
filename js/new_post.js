
$(document).ready(function(){

$(document).on("change","#options_num",function(){

if($(this).val() == 1) {
$("#shareNewImagesContainer").html("<div class='col l12 m12 s12' data-index='0'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div>");
}
if($(this).val() == 2) {
$("#shareNewImagesContainer").html("<div class='col l6 m6 s6' data-index='0'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div><div class='col l6 m6 s6' data-index='1'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div>");	
}
if($(this).val() == 3) {
$("#shareNewImagesContainer").html("<div class='col l6 m6 s6' style='height:50%;' data-index='0'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div><div class='col l6 m6 s6' style='height:50%;' data-index='1'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div><div class='col l12 m12 s12' style='height:50%' data-index='2'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div>");	
}
if($(this).val() == 4) {
$("#shareNewImagesContainer").html("<div class='col l6 m6 s6' style='height:50%;' data-index='0'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div><div class='col l6 m6 s6' style='height:50%;' data-index='1'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div><div class='col l6 m6 s6' style='height:50%;' data-index='2'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div><div class='col l6 m6 s6' style='height:50%;' data-index='3'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div>");	
}

$("#shareNewPostButton").addClass("disabledButton");	
resetNewPostInputs();

});


$(document).on("click","#shareNewImagesContainer div",function(){
$("#new_post_file_" + $(this).attr("data-index")).click();
});

$(document).on("change","#newPostInputs input",function(){

var image_file_check = checkImageFile($(this),5000000);
if(image_file_check != true) {
Materialize.toast(image_file_check,6000,"red");	
return false;	
}

imagePreview($(this)[0].files[0],$("#shareNewImagesContainer div[data-index=" + $(this).attr("data-index") + "] img"),"src");
$("#shareNewImagesContainer div[data-index=" + $(this).attr("data-index") + "]").attr("data-uploaded","true");
shouldActivateShareButton();
});


$(document).on("change","#post_title",function(){
shouldActivateShareButton();
});


// when the user presses the button to create a new post, we need to make a call to new_post.php with that post's data.
$(document).on("click","#shareNewPostButton",function(){

if(shouldActivateShareButton() != false) {

var data = new FormData();

for(var i = 0;i<$("#options_num").val();i++) {
data.append("files" + i,$("#new_post_file_" + i)[0].files[0]);
}


data.append("title",$("#post_title").val());
data.append("type",$("#options_num").val());

$(this).addClass("disabledButton");

$.post({
url:"components/new_post.php",
data:data,
cache: false,
contentType: false,
processData: false,
success:function(data){
Materialize.toast("Post Successful!",2000,"green");

//if the post was successfully posted, then open the singlePostModal and populate its innerHTML with the new post's markup.
if(!isNaN(data)) {
openModalCustom("singlePostModal",currentZindexStack,modalsOpened);
getPosts("components/get_single_post.php",{"post_id":data},markUpProcessor,$("#singlePostsContainer"));		
}

// reset the #shareNewModal 
$("#shareNewPostButton").addClass("disabledButton");	
$("#shareNewImagesContainer").html("<div class='col l12 m12 s12' data-index='0'><img class='shareNewImagesImage' src='icons/upload.png' alt='Photo'/></div>");
$("#post_title").val("");
$("#options_num").val("1");
$("#options_num").material_select();
resetNewPostInputs();

$('#shareNewModal').find('.modalCloseButton').click();


$("#shareNewPostButton").removeClass("disabledButton");
}	
});

}

});





// don't want to make the code look messy, so we reuse this function instead.
function resetNewPostInputs() {
$("#newPostInputs").html("<form action='#' method='post' enctype='multipart/form-data'><input id='new_post_file_0' type='file' accept='image/*' name='new_post_file_0' data-index='0'/> <input id='new_post_file_1' type='file' accept='image/*' name='new_post_file_1' data-index='1'/> <input id='new_post_file_2' type='file' accept='image/*' name='new_post_file_2' data-index='2'/> <input id='new_post_file_3' type='file' accept='image/*' name='new_post_file_3' data-index='3'/></form>");
}

// checks if the share post button should be activated
function shouldActivateShareButton() {	

if($("#post_title").val() == "") {
$("#shareNewPostButton").addClass("disabledButton");	
return false;		
}	

if($("#post_title").val().length < 15 || $("#post_title").val().length > 49) {
Materialize.toast("Title Must Be Longer Than 14 And Shorter Than 50 Characters",6000,"red");	
$("#shareNewPostButton").addClass("disabledButton");	
return false;
}

$("#shareNewPostButton").removeClass("disabledButton");
$("#shareNewImagesContainer div").each(function(){	
if(typeof $(this).attr("data-uploaded") == "undefined") {
$("#shareNewPostButton").addClass("disabledButton");	
return false;
}	
});

}



});
