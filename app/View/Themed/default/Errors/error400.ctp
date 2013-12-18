<div class="container clearfix"><div class="row"><div class="col-md-12">
	<div class="alert alert-error">
		<h1 style="padding:20px 0;"><?php echo __('404 page not found.'); ?></h1>
		<p>
			<?php echo __('The requested address was not found on this server.'); ?>
		</p>				
		<?php
		echo '<hr/>';
		if (Configure::read('debug') > 0 ):
			echo $this->element('exception_stack_trace');
		endif;
		?>
	</div>
</div></div></div>