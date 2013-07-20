<section id="main_section">
    <?php
        if (isset($mall))
        { ?>
        <span class="fields">
            <h3>You are about to add a store to:</h3>
            <img src="<?php echo $mall['logo']; ?>" alt="<?php echo $mall['name'];?>" />
            <h4><?php echo $mall['name']; ?></h4>
            <?php echo form_open(base_url().'stores/new'); ?>
                <input type="hidden" name="secret" value="<?php echo $mall['secret']; ?>" />
                <input type="hidden" name="verified" value="1" />
                <input type="submit" value="Continue" style="margin-left:0"/>
            </form>
        </span>
        <?php } else { ?>
            <span class="fields">
                <h2>Adding a store to MallCrawl</h2>
                <p>Please enter the secret of the mall you want to add a store to. Ask the mall administration for this information</p>
                <?php echo form_open(base_url().'stores/new'); ?>
                    <?php if (isset($msg)) echo '<span class="error">'.$msg.'</span>'; ?>
                    <div><label for="secret">Secret:</label> <input type="text" size="8" name="secret" value="<?php if (isset($secret)) echo $secret; ?>" /></div>
                    <input type="submit" value="Continue" />
                </form>
            </span>
        <?php } ?>
</section>