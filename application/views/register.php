<section id="main_section">
        <h3>Register</h3>
        <?php if (ISSET($msg)){?><span class="error"><?php echo $msg;?></span><?php } ?>
        <?php if (ISSET($redir) && $redir) echo form_open(base_url().'auth/register?redir='.$redir);
              else echo form_open(base_url().'auth/register');?>
            
            <div class="form_field"><label for="username">Username</label><input name="username" id="username" type="text" value="<?php echo $username; ?>" /></div>
            <div class="form_field"><label for="password">Password</label><input name="password" id="password" type="password" value="" /></div>
            <div class="form_field"><label for="password">Password Confirmation</label><input name="password2" id="password2" type="password" value="" /></div>
            
            <div class="form_field"><input type="submit" value="Log in"/></div>
        </form>
    </section>