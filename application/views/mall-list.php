<section id="main_content">
    <table align="center">
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
    <div style="text-align:center">
        <?php echo $pagination;?>
        <a href="<?php echo site_url('malls/new');?>">Add a mall</a>
    </div>
</section>
