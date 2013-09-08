<section id="main_content">
    <div id="map_canvas"></div>
    
    <img class="logo" src="<?php echo $logo;?>" alt="<?php echo $name;?>"/><br/>
    <h2 class="title"><?php echo $name; ?></h3>
    <div style="text-align:center;"><a href="?edit=1">[edit]</a></div>
    <span class="fields">
        <div><span class="label">Province:</span> <?php echo $province; ?></div>
        <div><span class="label">City:</span> <?php echo $city; ?></div>
        <div><span class="label">Manager:</span> <?php echo $manager_name; ?></div>
        <div><span class="label">Map:</span> <a href="<?php echo $map;?>">Click here to view map</a></div>
        <div><span class="label">Secret:</span> <?php echo $secret;?></div>
        <div><span class="label">Website:</span> <a href="<?php echo $website;?>"><?php echo $website;?></a></div>
        <div><span class="label">Twitter:</span> <a href="<?php echo $twitter;?>"><?php echo $twitter;?></a></div>
        <div><span class="label">Facebook:</span> <a href="<?php echo $facebook;?>"><?php echo $facebook;?></a></div>
        <div><span class="label">Phone:</span> <?php echo $phone;?></div>
        <div><span class="label">Email:</span> <a href="mailto:<?php echo $email;?>"><?php echo $email;?></a></div>
        <div><span class="label">Bio:</span> <?php echo $bio;?></div>
    <h3>Associated stores:</h3>
    <table class="list">
    <tr><th>Name</th><th>Type</th><?php if (ISSET($is_admin)){?><th>Creator</th><?php }?><th>Manager</th><th>Phone</th><th>Starred</th></tr>
    <?php 
    foreach ($stores as $store)
    {
        echo "<tr><td><a href='".base_url().'stores/'.$store['storeid']."'>".$store['name']."<a/></td><td>".$store['typename']."</td>";
        if (ISSET($is_admin))
            echo "<td><a href='".base_url().'owners/'.$store['ownerid']."'>".$store['uname']."</td>";
        echo "<td>".$store['manager_name']."</td><td>".$store['phone']."</td><td>Starred</td></td>";
    }
    ?>
    </table>
    <p>Give stores your secret (<?php echo $secret; ?>) so that they can attach themselves to your mall</p>
    </span>
</section>