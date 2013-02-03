    <section id="main_section">
        <h3>Log in</h3>
        <?php if (ISSET($msg)){?><span class="error"><?php echo $msg;?></span><?php } ?>
        <?php if (ISSET($redir) && $redir) echo form_open(base_url().'auth/login?redir='.$redir);
              else echo form_open(base_url().'auth/login');?>
            
            <div class="form_field"><label for="Username">Username</label><input name="username" type="text" value="<?php echo $username; ?>" /></div>
            <div class="form_field"><label for="Password">Password</label><input name="password" type="password" value="" /></div>
            <div class="form_field"><input type="submit" value="Log in"/></div>
        </form>
    </section>