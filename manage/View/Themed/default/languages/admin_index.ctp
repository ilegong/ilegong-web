<div class="languages index">
    <div class="actions">
        <ul>
            <li><?php echo $this->Html->link(__('New Language', true), array('action'=>'add')); ?></li>
        </ul>
    </div>

    <table cellpadding="0" cellspacing="0">
    <?php
        $tableHeaders =  $this->Html->tableHeaders(array(
            $this->Paginator->sort('id'),
            $this->Paginator->sort('title'),
            $this->Paginator->sort('native'),
            $this->Paginator->sort('alias'),
            $this->Paginator->sort('status'),
            __('Actions', true),
        ));
        echo $tableHeaders;

        $rows = array();
        foreach ($languages AS $language) {
            $actions  = $this->Html->link(__('Move up', true), array('action' => 'moveup', $language['Language']['id']));
            $actions .= ' ' . $this->Html->link(__('Move down', true), array('action' => 'movedown', $language['Language']['id']));
            $actions .= ' ' . $this->Html->link(__('Edit', true), array('action' => 'edit', $language['Language']['id']));
            $actions .= ' ' . $this->Html->link(__('Delete', true), array('action' => 'delete', $language['Language']['id']), null, __('Are you sure?', true));

            $rows[] = array(
                $language['Language']['id'],
                $language['Language']['title'],
                $language['Language']['native'],
                $language['Language']['alias'],
                $language['Language']['status'],
                $actions,
            );
        }

        echo $this->Html->tableCells($rows);
        echo $tableHeaders;
    ?>
    </table>
</div>

<div class="paging"><?php echo $this->Paginator->numbers(); ?></div>
<div class="counter"><?php echo $this->Paginator->counter(array('format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true))); ?></div>
