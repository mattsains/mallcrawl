<section id="main_content">
    
    <?php echo form_open(current_url().'?edit=1');?>
    <div id="map_canvas"></div>
    <input type="hidden" name="mallid" value="<?php echo $mallid;?>" />
    <input type="hidden" name="x_coord" id="x_coord" value="<?php echo $x_coord;?>" />
    <input type="hidden" name="y_coord" id="y_coord" value="<?php echo $y_coord;?>" />
    <span class="fields">
        <?php echo form_error('name','<span class="error">','</span>');?>
        <div><label for="name">Name:</label> <input type="text" class="h2-like" name="name" value="<?php echo set_value('name',$name); ?>"/></div>
        <?php echo form_error('manager_name','<span class="error">','</span>');?>
        <div><label for="manager_name">Manager:</label> <input type="text" name="manager_name" value="<?php echo set_value('manager_name',$manager_name); ?>"/></div>
        <?php echo form_error('map','<span class="error">','</span>');?>
        <div><label for="map">Map:</label> <input type="file" name="map" /></div>
        <?php echo form_error('logo','<span class="error">','</span>');?>
        <div><label for="logo">Logo:</label> <input type="file" name="logo"/></div>
        <?php echo form_error('website','<span class="error">','</span>');?>
        <div><label for="website">Website:</label> <input type="text" name="website" value="<?php echo set_value('website',$website);?>" /></div>
        <?php echo form_error('twitter','<span class="error">','</span>');?>
        <div><label for="twitter">Twitter:</label> <input type="text" name="twitter" value="<?php echo set_value('twitter',$twitter);?>" /></div>
        <?php echo form_error('facebook','<span class="error">','</span>');?>
        <div><label for="facebook">Facebook:</label> <input type="text" name="facebook" value="<?php echo set_value('facebook',$facebook);?>" /></div>
        <?php echo form_error('phone','<span class="error">','</span>');?>
        <div><label for="phone">Phone:</label> <input type="text" name="phone" value="<?php echo set_value('phone',$phone);?>" /></div>
        <?php echo form_error('email','<span class="error">','</span>');?>
        <div><label for="email">Email:</label> <input type="text" name="email" value="<?php echo set_value('email',$email);?>" /></div>
        <?php echo form_error('bio','<span class="error">','</span>');?>
        <div><label for="bio">Bio:</label> <textarea name="bio" rows=5 ><?php echo $bio;?></textarea></div>
        
    
        <input type="submit"/>
    </span>
    </form>
</section>