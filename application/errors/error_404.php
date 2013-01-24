<?php header('Content-Type: application/json');?>
{"error":{"text":"<?php echo strip_tags($message);?>","ref":"404:<?php echo substr($_SERVER['REQUEST_URI'],1);?>"}}