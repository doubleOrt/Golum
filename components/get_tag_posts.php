<?php
//we make a call to this page wants to view all of a tag's posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [[],""];

if(isset($_GET["tag"]) && isset($_GET["row_offset"]) && is_integer(intval($_GET["row_offset"])) && isset($_GET["sort_posts_by"])) {

if($_GET["row_offset"] == 0) {
$current_tag_follow_state = $con->query("select id from following_tags where id_of_user = ". $_SESSION["user_id"] ." and tag = '". htmlspecialchars($_GET["tag"]) ."'")->fetch();
// the follow tag button.
$echo_arr[1] .= "<a href='#' class='waves-effect wavesCustom btn commonButtonWhite navRightItemsMobileCommonButton addTagFromTagPostsModal scaleVerticallyCenteredItem' data-tag='". htmlspecialchars($_GET["tag"]) ."' data-current-state='". ($current_tag_follow_state == "" ? "0" : "1") ."'>". ($current_tag_follow_state == "" ? "Follow" : "Unfollow") ."</a>";
}
	

if($_GET["sort_posts_by"] == 0) {
$prepared = $con->prepare("select * from posts where title like concat('%',:tag ,'%') or title like concat('%', :tag) or title like concat(:tag,'%') order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":tag", $_GET["tag"]);
$prepared->execute();
}
else if($_GET["sort_posts_by"] == 1) {
$prepared = $con->prepare("select * from posts where title like concat('%',:tag ,'%') or title like concat('%', :tag) or title like concat(:tag,'%') order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":tag", $_GET["tag"]);
$prepared->execute();
}

$posts_arr = $prepared->fetchAll();

if(count($posts_arr) > 0) {
for($i = 0;$i<count($posts_arr);$i++) {
array_push($echo_arr[0], get_post_markup($posts_arr[$i]));
}	
}

	
}

echo json_encode($echo_arr);	


unset($con);

?>