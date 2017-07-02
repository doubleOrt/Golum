
var USER_TAGS_CONTAINER;

var get_user_tags_prevent_multiple_calls = false;

function get_user_tags(user_id, row_offset, callback) {
		
if(typeof user_id == "undefined" || typeof row_offset == "undefined") {
return false;	
}	


if(get_user_tags_prevent_multiple_calls == false) {
get_user_tags_prevent_multiple_calls = true;

$.get({
url:"components/get_user_tags.php",
data: {
"user_id": user_id,
"row_offset": row_offset	
},
success: function(data) {
console.log(data);	
var data_arr = JSON.parse(data);
if(typeof callback == "function") {
callback(data_arr);	
}	
get_user_tags_prevent_multiple_calls = false;
}
});

}
	
}



function get_user_tags_callback(data) {

// if the user is not infinite scrolling and there have been no results, add a placeholder div to tell the user there have been no results.
if( data.length < 1 && USER_TAGS_CONTAINER.find(".list_row").length < 1) {
USER_TAGS_CONTAINER.html("<div class='emptyNowPlaceholder'><i class='material-icons'>error</i><br>This loser is not following a single tag :(</div>")	
return false;	
} 

for(var i = 0;i < data.length; i++) {
USER_TAGS_CONTAINER.append(
generate_tag_row_mark_up( 
data[i]["tag"], 
data[i]["total_posts"], 
data[i]["total_followers"],
data[i]["sample_image_path"]
)
);
}
	
}



function addTagsToUserById(tag,callback) {
	
$.post({
data:{"tag":tag},	
url:"components/add_tags.php",
success:function(data) {
callback(data);
}
});

}

function removeTagsFromUserById(tag,callback) {

$.post({
data:{"tag":tag},	
url:"components/remove_tags.php",
success:function(data) {
callback(data);
}
});

}


function generate_tag_row_mark_up(tag, total_posts, total_followers, sample_image_path, current_state) {

return `<div class='row tag_row list_row getTagPosts modal-trigger' data-tag='` + tag + `' data-target='tagPostsModal'>

<div class='col l1 m1 s3 tag_sample_image_container opacityChangeOnActive stopPropagationOnClick'>
<div class='tag_sample_image fadeIn' style='background:url(\"` + sample_image_path + `\"); background-size:cover; background-position:center;'>
</div>
</div>

<div class='col l11 m11 s9 list_row_right_container'>
<div class='col l9 m9 s7 row_infos_container'>
<div class='row_names_container'>
<div class='row_name tag_name flow-text fadeIn opacityChangeOnActive'>` + tag + `</div>
<div class='row_secondary_text tag_row_secondary_text flow-text fadeIn'>` + total_followers + (total_followers != 1 ? " Followers" : " Follower") + `</div>
<div class='row_secondary_text tag_row_secondary_text flow-text fadeIn'>` + total_posts + (total_posts != 1 ? " Posts" : " Post") + `</div>
</div>
</div>

<div class='col l3 m3 s5 skewScaleItem'>
` + (typeof current_state != "undefined" ? (`<a href='#' class='row_button myBackground opacityChangeOnActive addTagFromTagPostsModal stopPropagationOnClick' data-tag='` + tag + `' data-current-state='` + current_state + `'>` + (current_state == 0 ? "Follow +" : "Unfollow") + `</a>`) : "") + `
</div>

</div>

</div><!-- end .list_row -->`;
}



$(document).ready(function(){
	
USER_TAGS_CONTAINER = $("#user_tags_modal_content_child");
	
	
	
/* when users want to see the tags that another user or they are following */	
$(document).on("click", ".get_user_tags", function(){

if(typeof $(this).attr("data-user-id") == "undefined") {
return false;	
}
	
USER_TAGS_CONTAINER.html("");	
USER_TAGS_CONTAINER.attr("data-user-id", $(this).attr("data-user-id"));	
	
get_user_tags($(this).attr("data-user-id"), 0, get_user_tags_callback);	
});
// infinite scrolling the tags followed by a user
USER_TAGS_CONTAINER.scroll(function(){
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) == 0) && $(this).find(".list_row").length > 0) {
get_user_tags(USER_TAGS_CONTAINER.attr("data-user-id"), USER_TAGS_CONTAINER.find(".list_row").length, get_user_tags_callback);	
}
});
	
	
	
});