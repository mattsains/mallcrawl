<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="<?php echo base_url();?>assets/main.css" type="text/css" />
    <title>Mall Crawl - <?php echo $title;?></title>
    <?php if (ISSET($map)){?>
        <script type="text/javascript"
          src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAc5xip37Y645co4JFWAcCCQHGFdrAgZmM&sensor=false">
        </script>
        <script type="text/javascript">
        var map;
        var marker;
          function map_initialize() {
            var mapOptions = {
              center: new google.maps.LatLng(<?php echo $map['x_coord'].', '.$map['y_coord'];?>),
               zoom: 14,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("map_canvas"),
                mapOptions);
            marker = new google.maps.Marker({
                  position: mapOptions.center,
                  <?php if (ISSET($map['edit'])){?> draggable: true, <?php } else {?>
                                                    draggable: false, <?php } ?>
                  map: map
                });
            <?php if (ISSET($map['edit'])){?>
            google.maps.event.addListener(marker, 'dragend', function() {
              document.getElementById('x_coord').value=marker.getPosition().lat();
              document.getElementById('y_coord').value=marker.getPosition().lng();
            });
            <?php } ?>
            }
        </script>
    <?php } ?>
    <?php if(ISSET($catjs)){ ?><script type="text/javascript" src="<?php echo base_url();?>assets/categoryselect.js">
    </script><?php } ?>
    
</head>

<body <?php if(ISSET($catjs)) echo 'onload="onlload();"'; ?> <?php if (ISSET($map)) echo 'onload="map_initialize();"';?>>
    <h1>Mall Crawl Administration</h1>
    <nav>
        <ul>
            <li><a href="<?php echo base_url();?>">Home</a></li>
            <li><a href="<?php echo base_url();?>malls">Malls</a></li>
            <li><a href="<?php echo base_url();?>stores">Stores</a></li>
            <?php if (logged_in()){ ?>
                <li><a href="<?php echo base_url();?>auth/logout">Logout</a></li>
            <?php } else { ?>
                <li><a href="<?php echo base_url();?>auth/login">Login</a></li>
                <li><a href="<?php echo base_url();?>auth/register">Register</a></li>
            <?php } ?>
        </ul>
    </nav>