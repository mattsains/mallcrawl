<section id="main_content">
    <table>
    <tr><th>Logo</th><th>Mall</th><?php if ($is_admin) echo "<th>Creator</th>";?><th>Manager</th><th>Phone</th><th>Starred</th></tr>
    <?php
        foreach($malls as $mall)
        {
            echo "<tr><td>";
            if ($mall['logo'])
                echo "<img class='thumb' src='".$mall['logo']."' alt='".$mall['name']." logo'/>";
            echo "</td><td><a href='".base_url()."malls/".$mall['mallid']."'>".$mall['name']."</a></td>";
            if ($is_admin)
                echo "<td>".$mall['uname']."</td>";
            echo "<td>".$mall['manager_name']."</td><td>".$mall['phone']."</td><td>".$mall['starred']."</td></tr>\n";
        }
    ?>
    </table>
    <?php echo $pagination;?>
    <div><a href="<?php echo site_url('malls/new');?>">Add a mall</a>
</section>
