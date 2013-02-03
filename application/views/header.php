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
          function map_initialize() {
            var mapOptions = {
              center: new google.maps.LatLng(<?php echo $map['x_coord'].', '.$map['y_coord'];?>),
               zoom: 14,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("map_canvas"),
                mapOptions);
            var marker = new google.maps.Marker({
                  position: mapOptions.center,
                  map: map
                });
            }

        </script>
    <?php } ?>
</head>

<body <?php if (ISSET($map)) echo 'onload="map_initialize();"';?>>
    <h1>Mall Crawl Administration</h1>
    <nav>
        <ul>
            <li><a href="<?php echo base_url();?>">Home</a></li>
            <li><a href="<?php echo base_url();?>malls">Malls</a></li>
            <li><a href="#">Potentially other things</a></li>
        </ul>
    </nav>