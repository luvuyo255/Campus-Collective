<?php

    $serverName = "CS3-DEV.ICT.RU.AC.ZA";
    $user = "Yakuza";
    $Password = "W3bD3vCs3!";
    $datebase = "yakuza";

    $conn = new Mysqli($serverName, $user , $Password ,$datebase);


    if($conn ->connect_error){
        die("connection to server and datebase failed" .$conn->connect_error);
   }else{
        //echo "Connection successfully established";
    }


?>