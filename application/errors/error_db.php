<?php header('Content-Type: application/json');?>{"server_error":{"text":"<?php echo $heading;?>","ref":"c<?php echo time();?>","can_retry":true}}<?php die();//prevents output mixed in error messages?>