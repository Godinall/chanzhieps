<?php
/**
 * The admin browse view file of book module of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Tingtin Dai<daitingting@xirangit.com>
 * @package     book
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
?>
<?php include '../../common/view/header.admin.html.php';?>
<?php 
$path = array_keys($book->pathNames);
js::set('path', json_encode($path));
?>
<div class='box radius'>  
  <h4 class='title'><?php echo $book->title;?></h4>
  <dl><?php echo $this->book->getBookCatalogue($book->id);?></dl>
</div>
<?php include '../../common/view/footer.admin.html.php';?>