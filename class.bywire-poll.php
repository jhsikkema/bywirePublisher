<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/


if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
   $response = array("msg"=> "called", "basename"=> basename(__FILE__), "script"=> basename($_SERVER["SCRIPT_FILENAME"]));
}
$response = array("msg"=> "called", "basename"=> basename(__FILE__), "script"=> basename($_SERVER["SCRIPT_FILENAME"]));
echo json_encode($response);


?>