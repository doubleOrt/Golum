<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";

$im = new Imagick(); 


if(isset($_POST["title"]) && isset($_POST["type"]) && is_numeric($_POST["type"]) && count($_FILES) > 0) {

//this is the path of the images after upload
$upload_to = "../posts/";

$counter = 0;

$file_types = "";


//what is going to be the id of the new post ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = $con->query("SHOW TABLE STATUS LIKE 'posts'")->fetch();

$what_id = $what_id_query["Auto_increment"];


foreach($_FILES as $file) {
$file_path_info = strtolower(pathinfo($upload_to . basename($file["name"]),PATHINFO_EXTENSION));	

if($counter != 0) {
$file_types .= ",";	
}

$file_types .= $file_path_info;

if($file_path_info != "jpg" && $file_path_info != "jpeg" && $file_path_info != "png" && $file_path_info != "gif") {
echo "Image Type Must Be Either \"JPEG\", \"JPG\", \"PNG\" Or \"GIF\" !";	
die();	
}
if($file["size"] >= 5000000) {
echo "Materialize.toast('Image Size Must Be Smaller Than 5MB',6000,'red');";	
die();	
}	

if(move_uploaded_file($file["tmp_name"],$upload_to . basename($file["name"]))) {

//this is the new path to the avatar.
$new_path = "../posts/" . $what_id . "-" . $counter . "." . $file_path_info;	
	
//rename the file and check if it is successful
if(!rename($upload_to . basename($file["name"]) , $new_path)) {
echo "Materialize.toast('Sorry, There Was An Error',6000,'red');";	
die();
}	

if($file_path_info == "jpg" || $file_path_info == "jpeg") {
$exif = exif_read_data($new_path);
if(isset($exif["orientation"])) {
$ort = $exif['Orientation'];	
$image_width = imagesx(ImageCreateFromJpeg($new_path));
resample($new_path,$image_width,$ort);
}
}
}
else {
echo "Materialize.toast('Sorry, There Was An Error',6000,'red');";	
die();
}

$counter++;
}

$post_time = time();

$prepared = $con->prepare("insert into posts (title,type,file_types,time,posted_by) values(:title,:type,:file_types,:time,:posted_by)");
$prepared->bindParam(":title",$_POST["title"]);
$prepared->bindParam(":type",$_POST["type"]);
$prepared->bindParam(":file_types",$file_types);
$prepared->bindParam(":time",$post_time);
$prepared->bindParam(":posted_by",$_SESSION["user_id"]);

if($prepared->execute()) {
echo $con->lastInsertId();	
}
	
}



function resample($jpgFile, $width, $orientation) {
// Get new dimensions
list($width_orig, $height_orig) = getimagesize($jpgFile);
$height = (int) (($width / $width_orig) * $height_orig);
// Resample
$image_p = imagecreatetruecolor($width, $height);
$image   = imagecreatefromjpeg($jpgFile);
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
// Fix Orientation
switch($orientation) {
case 3:
$image_p = imagerotate($image_p, 180, 0);
break;
case 6:
$image_p = imagerotate($image_p, -90, 0);
break;
case 8:
$image_p = imagerotate($image_p, 90, 0);
break;
}
imagejpeg($image_p,$jpgFile,100);
}

?>