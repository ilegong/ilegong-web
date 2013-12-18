<div class="container clearfix"><div class="row"><div class="col-md-12">
	<div class="alert alert-error">
		<h1 style="padding:20px 0;"><?php echo __('Error.'); ?></h1>
		<p>
			<?php echo __('An Internal Error Has Occurred.'); ?>
		</p>				
		<?php
		echo '<hr/>';
		if (Configure::read('debug') > 0 ):
			echo $this->element('exception_stack_trace');
		endif;
		?>
	</div>
</div></div></div>
