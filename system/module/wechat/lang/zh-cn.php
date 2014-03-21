<?php
/**
 * The wechat module zh-cn file of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Tingting Dai <daitingting@xirangit.com>
 * @package     wechat
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
$lang->wechat->id        = '编号';
$lang->wechat->type      = '类型';
$lang->wechat->name      = '微信名';
$lang->wechat->account   = '微信号';
$lang->wechat->appID     = 'AppID';
$lang->wechat->appSecret = 'AppSecret';
$lang->wechat->token     = 'Token';
$lang->wechat->url       = '接入地址';
$lang->wechat->certified = '认证';
$lang->wechat->users     = '微信会员';
$lang->wechat->content   = '内容';

$lang->wechat->create    = '添加公众号';
$lang->wechat->edit      = '编辑公众号';
$lang->wechat->admin     = '维护公众号';
$lang->wechat->list      = '公众号列表';
$lang->wechat->setMenu   = '菜单';
$lang->wechat->access    = '接入';

$lang->wechat->typeList['subscribe'] = '订阅号';
$lang->wechat->typeList['service']   = '服务号';

$lang->wechat->certifiedList[1] = '是';
$lang->wechat->certifiedList[0] = '否';

$lang->wechat->response = new stdclass();

$lang->wechat->response->keywords  = '关键字';
$lang->wechat->response->set       = '响应设置';
$lang->wechat->response->create    = '添加关键字';
$lang->wechat->response->default   = '默认响应';
$lang->wechat->response->subscribe = '订阅响应';

$lang->wechat->response->type     = '类型';
$lang->wechat->response->source   = '来源';
$lang->wechat->response->module   = '模块';
$lang->wechat->response->block    = '内容';
$lang->wechat->response->link     = '链接';
$lang->wechat->response->category = '类目';
$lang->wechat->response->limit    = '数量';

$lang->wechat->response->list   = '响应列表';

$lang->wechat->response->typeList['link'] = '链接';
$lang->wechat->response->typeList['text'] = '文本消息';
$lang->wechat->response->typeList['news'] = '图文消息';

$lang->wechat->response->sourceList['system'] = '系统';
$lang->wechat->response->sourceList['manual'] = '输入';

global $app;
$webRoot = rtrim(getWebRoot(true), '/');
$home    = $webRoot . commonModel::createFrontLink('index', 'index'); 
$company = $webRoot . commonModel::createFrontLink('company', 'index'); 
$blog    = $webRoot . commonModel::createFrontLink('blog', 'index'); 
$forum   = $webRoot . commonModel::createFrontLink('forum', 'index'); 
$book    = $webRoot . commonModel::createFrontLink('book', 'index'); 

$lang->wechat->response->moduleList[$home]    = '首页';
$lang->wechat->response->moduleList[$company] = '关于我们';
$lang->wechat->response->moduleList[$blog]    = '博客';
$lang->wechat->response->moduleList[$forum]   = '论坛';
$lang->wechat->response->moduleList[$book]    = '手册';
$lang->wechat->response->moduleList['manual'] = '自定义';

$lang->wechat->response->textBlockList['company'] = '公司简介';
$lang->wechat->response->textBlockList['contact'] = '联系我们';
$lang->wechat->response->textBlockList['manual']  = '自定义';

$lang->wechat->response->newsBlockList['articleTree']   = '文章分类';
$lang->wechat->response->newsBlockList['latestArticle'] = '最新文章';
$lang->wechat->response->newsBlockList['hotArticle']    = '热门文章';
$lang->wechat->response->newsBlockList['productTree']   = '产品分类';
$lang->wechat->response->newsBlockList['latestProduct'] = '最新产品';
$lang->wechat->response->newsBlockList['hotProduct']    = '热门产品';

$lang->wechat->message = new stdclass();
$lang->wechat->message->from     = '称呼';
$lang->wechat->message->type     = '类型';
$lang->wechat->message->status   = '状态';
$lang->wechat->message->content  = '消息内容';
$lang->wechat->message->response = '响应';
$lang->wechat->message->menu     = '菜单';
$lang->wechat->message->time     = '时间';
$lang->wechat->message->reply    = '回复';

$lang->wechat->message->list = '消息列表';

$lang->wechat->message->typeList['text']     = '文本消息';
$lang->wechat->message->typeList['image']    = '图片消息';
$lang->wechat->message->typeList['voice']    = '语音消息';
$lang->wechat->message->typeList['location'] = '地理位置消息';
$lang->wechat->message->typeList['link']     = '链接消息';
$lang->wechat->message->typeList['event']    = '事件消息';

$lang->wechat->message->statusList['wait']    = '未回复';
$lang->wechat->message->statusList['replied'] = '已回复';

$lang->wechat->placeholder = new stdclass();
$lang->wechat->placeholder->limit    = '请输条数，最多10条';
$lang->wechat->placeholder->category = '请选择类目，最多10个';

$lang->wechat->needCertified  = "此功能需要公众号认证后使用。";
$lang->wechat->accessInfo = <<<EOT
<ul style="padding-left: 10px">
  <li><strong>AppSecret</strong>： %s </li>
  <li><strong>接入地址</strong>： %s </li>
  <li><strong>Token</strong>： %s </li>
</ul>
EOT;
