<?php
    $plain = "MrnimbusC137"; 
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    echo $hash;
?>
