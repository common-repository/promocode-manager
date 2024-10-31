<?php if($message): ?>
    <div id="message" class="<?php echo $message_level?$message_level:"updated";?> below-h2"><p><?php echo $message;?></p></div>
<?php else: ?>
    <div id="message" class="below-h2">
    </div>
<?php endif; ?>