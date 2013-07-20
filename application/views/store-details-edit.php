<section id="main_content">
    <?php echo form_open_multipart($submit_to);?>
    <?php if (isset($secret)){?><input type="hidden" name="secret" value="<?php echo $secret;?>" /><?php } ?>
    <?php if (isset($storeid)){?><input type="hidden" name="storeid" value="<?php echo $storeid;?>" /><?php } ?>
    <span class="fields">
        <?php echo form_error('name','<span class="error">','</span>');?>
        <div><label for="name">Name:</label> <input type="text" class="h2-like" name="name" value="<?php if (isset($name)) echo $name; ?>"/></div>
        
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
        <div><label for="manager_name">Manager:</label> <input type="text" name="manager_name" value="<?php if (isset($manager_name)) echo $manager_name; ?>"/></div>
        
        <?php if (isset($logo_err)) echo $logo_err;?>
        <div><label for="logo">Logo:</label> <input type="file" name="logo"/></div>
        
        <?php echo form_error('website','<span class="error">','</span>');?>
        <div><label for="website">Website:</label> <input type="text" name="website" value="<?php if (isset($website)) echo $website; ?>" /></div>
        
        <?php echo form_error('twitter','<span class="error">','</span>');?>
        <div><label for="twitter">Twitter:</label> <input type="text" name="twitter" value="<?php if (isset($twitter)) echo $twitter; ?>" /></div>
        
        <?php echo form_error('facebook','<span class="error">','</span>');?>
        <div><label for="facebook">Facebook:</label> <input type="text" name="facebook" value="<?php if (isset($facebook)) echo $facebook; ?>" /></div>
        
        <?php echo form_error('phone','<span class="error">','</span>');?>
        <div><label for="phone">Phone:</label> <input type="text" name="phone" value="<?php if (isset($phone)) echo $phone; ?>" /></div>
        
        <?php echo form_error('email','<span class="error">','</span>');?>
        <div><label for="email">Email:</label> <input type="text" name="email" value="<?php if (isset($email)) echo $email; ?>" /></div>
        
        <?php echo form_error('bio','<span class="error">','</span>');?>
        <div><label for="bio">Bio:</label> <textarea name="bio" rows=5 ><?php if (isset($bio)) echo $bio; ?></textarea></div>
        
        <!-- TODO: add a javascript category selector -->
        <input type="submit"/>
    </span>
    </form>
</section>