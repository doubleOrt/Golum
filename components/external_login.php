<?php

require_once __DIR__ . "/../instantchat_test/vendor/autoload.php";
require_once "initialization.php";

$echo_arr = [0,""];

// the google console api project client ID
$CLIENT_ID = "154556605033-dnrn5oc0hub7gslgl61aa2qpeo0rctmc.apps.googleusercontent.com";

if(isset($_POST["id"])) {

$id_token = $_POST["id"];

$client = new Google_Client(['client_id' => $CLIENT_ID]);

$payload = $client->verifyIdToken($id_token);

if ($payload) {
$user_google_id = $payload["sub"];
$user_google_email = $payload["email"];
$user_google_first_name = $payload["given_name"];
$user_google_last_name = $payload["family_name"];
$user_google_avatar = $payload["picture"];

$prepared = $con->prepare("select id from users where (external_id = :external_id and external_type = 'GOOGLE') or (email_address = :email_address and activated = 'true')");

if($prepared->execute([":external_id" => $user_google_id, ":email_address" => $user_google_email])) {
$account_associated_with_email_id = $prepared->fetch()[0];
/* if the user already has an account, then just set the user_id 
session to the id field of that account's row in the database  */
if(!is_null($account_associated_with_email_id)) {
$_SESSION["user_id"] = $account_associated_with_email_id;
// so that the client redirects the user to the logged_in.html page.
$echo_arr[0] = 1;
}
/* the user does not have an account already, therefore 
we need to create a new one. */
else {
/* make a call to this function to create the user's account with the date Google gave you, 
returns the user's id on success and an error message on failure. */
$register_user = register_external_user($user_google_id, "GOOGLE", $user_google_email, $user_google_first_name, $user_google_last_name, $user_google_avatar);	
// if user account was created successfully
if(filter_var($register_user, FILTER_VALIDATE_INT) !== false) {
$_SESSION["user_id"] = $register_user;
// so that the client redirects the user to the logged_in.html page.
$echo_arr[0] = 1;	
}
else {
$echo_arr[1] = $register_user;	
}
}
}
else {
$echo_arr[1] = "Sorry, something went wrong :(";	
}
} else {
$echo_arr[1] = "Sorry, something went wrong :(";	
}	
	
}
else {
echo "Please send an 'id' field along with the request :("	;
}


echo json_encode($echo_arr);

unset($con);




function register_external_user($external_id, $external_type, $email_address, $first_name, $last_name, $avatar_picture) {
global $con;	
/* we generate a username since Google does not give us 
one, and i don't think Facebook does either. */
$user_name = $first_name . rand(1000,1000000);	
$sign_up_date = date("Y/m/d H:i",time());

// check if the username we randomly created doesn't already exist
if(check_if_username_already_exists($user_name) === false) {
if($con->prepare("update users set email_address = '', activated = '' where email_address = :email_address and activated != 'true'")->execute([":email_address" => $email_address])) { 		
	
// this is the big thing, the row that creates the actual user account.	
$prepared = $con->prepare("INSERT INTO users (external_id, external_type, user_name, email_address, activated, first_name, last_name, avatar_picture, sign_up_date) VALUES (:external_id, :external_type, :user_name, :email_address, :activated, :first_name, :last_name, :avatar_picture, :sign_up_date)");
$prepared->bindParam(":external_id", $external_id);
$prepared->bindParam(":external_type", $external_type);
$prepared->bindParam(":user_name", $user_name);
$prepared->bindParam(":email_address", $email_address);
$prepared->bindValue(":activated", "true");
$prepared->bindParam(":first_name", $first_name);
$prepared->bindParam(":last_name", $last_name);
$prepared->bindParam(":avatar_picture", $avatar_picture);
$prepared->bindParam(":sign_up_date", $sign_up_date);

// if the account was created successfully
if($prepared->execute()) {	
$user_id = $con->lastInsertId();
/* we need to insert a row into the avatars field since Google gives us one and we 
cannot have an avatar image in the users table without a corresponding row in 
the avatars table. */
if($con->prepare("insert into avatars (id_of_user, avatar_path, date_of, positions, rotate_degree) values (:user_id, :avatar_path, :date_of, '0,0', '0')")->execute([":user_id" => $user_id, ":avatar_path" => $avatar_picture, ":date_of" => $sign_up_date])) {

// everything was successful, return the newly created user's user_id.
return $user_id;
}
else {
/* if you could not insert a row for the avatar, then delete the row you 
inserted for the user and return an error message. */
$con->prepare("delete from users where id = :user_id limit 1")->execute([":user_id" => $user_id]);	
return "Something went wrong :(";
}
}
else {
return "Something went wrong :(";	
}

}
else {
return "Something went wrong :(";	
}

}
// if the username already exists, then call yourself again, in hopes that this username will be non-existent.
else {
$register_external_user($external_id, $external_type, $email_address, $first_name, $last_name, $avatar_picture);
}

}

function check_if_username_already_exists($user_name) {
global $con;	
$user_name_already_exists_prepared = $con->prepare("select id from users where user_name = :user_name");
$user_name_already_exists_prepared->execute([":user_name" => $user_name]);
return (!is_null($user_name_already_exists_prepared->fetch()[0]) ? true : false);	
}


?>