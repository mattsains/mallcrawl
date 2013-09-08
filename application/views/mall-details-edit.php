<section id="main_content">
    <?php echo form_open_multipart($submit_to);?>
    <div class="map_container">
        <p>Click and drag the red marker to your mall's location:</p>
        <div id="map_canvas"></div>
    </div>
    <input type="hidden" name="mallid" value="<?php echo $mallid;?>" />
    <input type="hidden" name="x_coord" id="x_coord" value="<?php echo $x_coord;?>" />
    <input type="hidden" name="y_coord" id="y_coord" value="<?php echo $y_coord;?>" />
    <span class="fields">
        <?php echo form_error('name','<span class="error">','</span>');?>
        <div><label for="name">Name:</label> <input type="text" class="h2-like" name="name" value="<?php echo set_value('name',$name); ?>"/></div>
        <?php echo form_error('province','<span class="error">','</span>');?>
        <div><label for="province">Province:</label> 
        <?php $province_sel=isset($province)?$province:"";?>
            <select name="province">
                <option value="" disabled <?php if ($province_sel=="") echo "selected";?>></option>
                <option value="Eastern Cape" <?php if ($province_sel=="Eastern Cape") echo "selected";?>>Eastern Cape</option>
                <option value="Free State" <?php if ($province_sel=="Free State") echo "selected";?>>Free State</option>
                <option value="Gauteng" <?php if ($province_sel=="Gauteng") echo "selected";?>>Gauteng</option>
                <option value="KwaZulu-Natal" <?php if ($province_sel=="KwaZulu-Natal") echo "selected";?>>KwaZulu-Natal</option>
                <option value="Limpopo" <?php if ($province_sel=="Limpopo") echo "selected";?>>Limpopo</option>
                <option value="Mpumalanga" <?php if ($province_sel=="Mpumalanga") echo "selected";?>>Mpumalanga</option>
                <option value="North West" <?php if ($province_sel=="North West") echo "selected";?>>North West</option>
                <option value="Northern Cape" <?php if ($province_sel=="Northern Cape") echo "selected";?>>Northern Cape</option>
                <option value="Western Cape" <?php if ($province_sel=="Western Cape") echo "selected";?>>Western Cape</option>
            </select></div>
        <?php echo form_error('city','<span class="error">','</span>');?>
        <div><label for="city">City:</label> <input type="text" name="city" value="<?php echo set_value('city',$city); ?>"/></div>
        <?php echo form_error('manager_name','<span class="error">','</span>');?>
        <div><label for="manager_name">Manager:</label> <input type="text" name="manager_name" value="<?php echo set_value('manager_name',$manager_name); ?>"/></div>
        <?php if (isset($map_err)) echo $map_err;?>
        <div><label for="map">Map:</label> <input type="file" name="map" /></div>
        <?php if (isset($logo_err)) echo $logo_err;?>
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