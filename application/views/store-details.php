<section id="main_content">
    
    <img class="logo" src="<?php echo $logo;?>" alt="<?php echo $name;?>"/>
    <h2 class="title"><?php echo $name; ?></h3>
    <div style="text-align:center;"><a href="?edit=1">[edit]</a></div>
     <span class="fields">   
        <div><span class="label">Mall:</span> <a href="<?php echo base_url();?>malls/<?php echo $mallid; ?>"><?php echo $mall; ?></a></div>
        <div><span class="label">Manager:</span> <?php echo $manager_name; ?></div>
        <div><span class="label">Website:</span> <a href="<?php echo $website;?>"><?php echo $website;?></a></div>
        <div><span class="label">Twitter:</span> <a href="<?php echo $twitter;?>"><?php echo $twitter;?></a></div>
        <div><span class="label">Facebook:</span> <a href="<?php echo $facebook;?>"><?php echo $facebook;?></a></div>
        <div><span class="label">Phone:</span> <?php echo $phone;?></div>
        <div><span class="label">Email:</span> <a href="mailto:<?php echo $email;?>"><?php echo $email;?></a></div>
        <div><span class="label">Bio:</span> <?php echo $bio;?></div>
        <!-- categories and types -->
        <br />
        <div><span class="label">Type:</span> <?php echo $type_name;?></div>
        <h3>Categories</h3>
        <?php 
        if (count($categories)==0)
            echo "<div>The store is uncategorized</div>";
        else { 
        echo '<table class="list" style="width:70%;table-layout:fixed;">';
        
        for($i=0; $i<count($categories)/5; $i+=5)
        {
            echo "<tr>";
            for ($j=0; $j<5; $j++)
            {
                echo "<td>";
                if (5*$i +$j<count($categories))
                    echo $categories[5*$i+$j]['categoryname'];
                echo "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        } ?>
    </span>
</section>