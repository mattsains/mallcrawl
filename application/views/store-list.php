<section id="main_content">
    <table align="center">
    <tr><th>Logo</th><th>Store</th><?php if ($is_admin) echo "<th>Creator</th>";?><th>Manager</th><th>Mall</th><th>Phone</th><th>Starred</th></tr>
    <?php
        foreach($stores as $store)
        {
            echo "<tr><td>";
            if ($store['logo'])
                echo "<img class='thumb' src='".$store['logo']."' alt='".$store['name']." logo'/>";
            echo "</td><td><a href='".base_url()."stores/".$store['storeid']."'>".$store['name']."</a></td>";
            if ($is_admin)
                echo "<td>".$store['uname']."</td>";
            echo "<td>".$store['manager_name']."</td><td><a href=\"".base_url()."malls/".$store['mallid']."\">".$store['mall']."</a></td><td>".$store['phone']."</td><td>".$store['starred']."</td></tr>\n";
        }
    ?>
    </table>
    <div style="text-align:center">
        <?php echo $pagination;?>
        <a href="<?php echo site_url('stores/new');?>">Add a store</a>
    </div>
</section>
