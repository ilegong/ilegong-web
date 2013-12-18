<div class="acl_actions form">
    <h2><?php echo $this->pageTitle; ?></h2>
    <?php echo $form->create('Aco', array('url' => array('controller' => 'acl_actions', 'action' => 'add'))); ?>
        <fieldset>
        <?php
            echo $form->input('parent_id', array(
                'options' => $acos,
                'empty' => true,
                'rel' => __('Choose none if the Aco is a controller.', true),
            ));
            echo $form->input('alias', array());
        ?>
        </fieldset>
    <?php echo $form->end('Submit'); ?>
</div>