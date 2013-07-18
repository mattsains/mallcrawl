<section id="main_content">
    <?php echo form_open_multipart($submit_to);?>
    <input type="hidden" name="storeid" value="<?php echo $storeid;?>" />
    <?php if (isset($secret)){?><input type="hidden" name="secret" value="<?php echo $secret;?>" /><?php } ?>
    <span class="fields">
        <?php echo form_error('name','<span class="error">','</span>');?>
        <div><label for="name">Name:</label> <input type="text" class="h2-like" name="name" value="<?php echo set_value('name',$name); ?>"/></div>
        
        <?php echo form_error('type','<span class="error">','</span>');?>
        <div><label for="type">Type:</label>
            <select name="type">
            <?php
                foreach ($types as $type)
                {
                    echo "<option value=\"".$type['typeid']."\">".$type['text']."</option>";
                }
            ?>
            </select>
        </div>
        
        <?php echo form_error('manager_name','<span class="error">','</span>');?>
        <div><label for="manager_name">Manager:</label> <input type="text" name="manager_name" value="<?php echo set_value('manager_name',$manager_name); ?>"/></div>
        
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