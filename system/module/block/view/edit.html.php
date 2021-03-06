<?php
/**
 * The edit view file of block module of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     block
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
?>
<?php include '../../common/view/header.admin.html.php';?>
<?php include '../../common/view/chosen.html.php';?>
<?php js::set('id', $block->id); ?>
<div class='panel'>
  <div class='panel-heading'><strong><i class='icon-edit'></i> <?php echo $lang->block->edit;?></strong></div>
  <div class='panel-body'>
    <form method='post' id='ajaxForm'>
      <table align='center' class='table table-form'>
        <tr>
          <th class='w-120px'><?php echo $lang->block->type;?></th>
          <td><?php echo $this->block->createTypeSelector($type, $block->id);?></td>
        </tr>
        <tr>
          <th><?php echo $lang->block->title;?></th>
          <td><?php echo html::input('title', $block->title, "class='form-control'");?></td>
        </tr>
        <?php if(isset($config->block->defaultIcons[$type])):?>
        <?php if(!isset($block->content->icon)) $block->content->icon = $config->block->defaultIcons[$type];?>
        <tr>
          <th><?php echo $lang->block->icon;?></th>
          <td><?php echo html::select('params[icon]', '', '', "class='chosen-icons' data-value='{$block->content->icon}'");?></td>
        </tr>
        <?php endif;?>
        <?php echo $this->fetch('block', 'blockForm', 'type=' . $type . '&id=' . $block->id);?>
        <tr>
          <td></td>
          <td><?php echo html::submitButton() . html::hidden('blockID', $block->id);?></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php include '../../common/view/footer.admin.html.php';?>
