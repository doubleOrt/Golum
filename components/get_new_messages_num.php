<?php
//we make a call to this page whenever the user opens the sidebar to tell them the number of new messages they got.

require_once "common_requires.php";
require_once "logged_in_importants.php";


$user_chats = $con->query("select id from chats where chatter_ids like '%-". $_SESSION["user_id"] ."' or chatter_ids like '". $_SESSION["user_id"] ."-%'")->fetchAll();

$chat_ids_string = "";
for($i = 0;$i<count($user_chats);$i++) {
if($i != 0) {
$chat_ids_string .= " or ";	
}	
$chat_ids_string .= " chat_id = ". $user_chats[$i]["id"];	
}


echo $con->query("select count(id) from messages where (". $chat_ids_string .") and message_from != ". $_SESSION["user_id"] ." and read_yet = false")->fetch()[0];



?>