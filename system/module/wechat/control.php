<?php
/**
 * The control file of wechat module of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     index
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
class wechat extends control
{
    /**
     * The public account.
     * 
     * @var object   
     * @access public
     */
    public $public;

    /**
     * The wechat api object.
     * 
     * @var object   
     * @access public
     */
    public $api;

    /**
     * Set the wechat api.
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function setAPI($public)
    {
        $this->public = $this->wechat->getByID($public);
        $this->app->loadClass('wechatapi', true);
        $this->api = new wechatapi($this->public->token, $this->public->appID, $this->public->appSecret, $this->config->debug);
    }

    /**
     * The wechat auto response api.
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function response($public)
    {
        $this->setAPI($public);
        $this->api->checkSign();

        $message  = $this->api->getMessage();
        $response = $this->wechat->getResponseForMessage($public, $message);
        $this->api->response($response);
    }

    /**
     * Reply a message.
     * 
     * @param  int    $public 
     * @param  int    $to 
     * @access public
     * @return void
     */
    public function reply($public, $to)
    {
        $this->setAPI($public);
        $message = new stdclass();
        $message->content = '你好';
        $this->api->reply('o-nXYt1LrugCK0oqZcrxyMidiJSg', 'text', $message);
    }

    /**
     * Delete menu of a public.
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function deleteMenu($public)
    {
        $this->setAPI($public);
        if($this->api->deleteMenu()) $this->send(array('result' => 'success', 'message' => $this->lang->deleteSuccess));
        $this->send(array('result' => 'fail', 'message' => $this->lang->fail));
    }

    /**
     * Upload a media file.
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function uploadMedia($public)
    {
        $this->setAPI($public);
        $media = $this->api->uploadMedia('image', '/home/wwccss/chanzhi/www/data/test.jpg');
        echo $this->api->getMedia($media);
    }

    /**
     * Get the qrcode from weixin server.
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function qrcode($public)
    {
        $this->setAPI($public);
        $qrcodeFile = $this->app->getDataRoot() . 'wechat' . DS . $public->appID . '.jpg';
        if(!is_dir(dirname($qrcodeFile))) @mkdir(dirname($qrcodeFile));
        $data = $this->api->getQRCode($qrcodeFile); 
    }

    /**
     * Get fans.
     * 
     * @param  int    $public 
     * @param  string $next 
     * @access public
     * @return void
     */
    public function getFans($public, $next = '')
    {
        $this->setAPI($public);
        $this->api->getFans($next);
    }

    /**
     * Get user info.
     * 
     * @param  int    $public 
     * @param  string $lang 
     * @access public
     * @return void
     */
    public function getUserInfo($public, $lang = 'zh_CN')
    {
        $this->setAPI($public);
        print_r($this->api->getUserInfo('o-nXYt1LrugCK0oqZcrxyMidiJSg', $lang));
    }

    /**
     * Browse public in admin.
     * 
     * @access public
     * @return void
     */
    public function admin()
    {
        $publics = $this->wechat->getList();
        if(empty($publics)) $this->locate(inlink('create'));

        $this->view->title   = $this->lang->wechat->admin;
        $this->view->publics = $publics;
        $this->display();
    }

    /**
     * Create a public.
     * 
     * @access public
     * @return void
     */
    public function create()
    {
        if($_POST) 
        {
            $this->wechat->create();       
            if(dao::isError())  $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate'=>inlink('admin')));
        }

        $this->view->title = $this->lang->wechat->create;
        $this->display();
    }

    /**
     * Edit a public.
     * 
     * @param  int    $publicID
     * @access public
     * @return void
     */
    public function edit($publicID)
    {
        if($_POST) 
        {
            $this->wechat->update($publicID);       
            if(dao::isError())  $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate'=>inlink('admin')));
        }

        $this->view->title  = $this->lang->wechat->edit;
        $this->view->public = $this->wechat->getByID($publicID);
        $this->display();
    }

    /**
     * Delete a public.
     * 
     * @param  int      $publicID 
     * @access public
     * @return void
     */
    public function delete($publicID)
    {
        if($this->wechat->delete($publicID)) $this->send(array('result' => 'success'));
        $this->send(array('result' => 'fail', 'message' => dao::getError()));
    }
    
    /**
     * Admin response for a public.
     * 
     * @param  int    $publicID 
     * @access public
     * @return void
     */
    public function adminResponse($publicID)
    {
        $this->view->title           = $this->lang->wechat->response->keywords;
        $this->view->publicID        = $publicID;
        $this->view->responseList    = $this->wechat->getResponseList($publicID);
        $this->view->articleCategory = $this->loadModel('tree')->getPairs(0, 'article');
        $this->view->productCategory = $this->tree->getPairs(0, 'product');
        $this->display();
    }

    /**
     * Set response for a public.
     * 
     * @param  int     $public 
     * @param  string  $group 
     * @param  string  $key
     * @access public
     * @return void
     */
    public function setResponse($public, $group = '', $key = '')
    {
        if($_POST) 
        {
            $this->wechat->setResponse($public);
            if(dao::isError())  $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            if($group == '') $response['locate'] = inlink('adminresponse', "publicID={$public}");
            if($group == 'default' or $group == 'subscribe') $response['locate'] = inlink('admin');
            $this->send($response);
        }

        if($key)
        {
            $response = $this->wechat->getResponseByKey($public, $key);
            if(!empty($response) and $response->source == 'system' and $response->type != 'news')
            {
                $response->source = $response->content;
            }
            $this->view->response = $response;
        }

        if($group == 'menu') unset($this->lang->wechat->menu);

        $this->view->articleTree = $this->loadModel('tree')->getOptionMenu('article', 0, $removeRoot = true);
        $this->view->productTree = $this->tree->getOptionMenu('product', 0, $removeRoot = true);
        $this->view->title       = $this->lang->wechat->response->set;
        $this->view->public      = $public;
        $this->view->group       = $group;
        $this->view->key         = $key;
        $this->display();
    }

    /**
     * Commit menu. 
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function commitMenu($public)
    {
        $this->setApi($public);
        $menu = $this->wechat->getMenu($public);
        $result = $this->api->commitMenu($menu);
        if($result['result'] == 'success') $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess));
        $this->send($result);
    }

    /**
     * Delete a response.
     * 
     * @param  int    $response 
     * @access public
     * @return void
     */
    public function deleteResponse($response)
    {
        if($this->wechat->deleteResponse($response)) $this->send(array('result' => 'success'));
        $this->send(array('result' => 'fail', 'message' => dao::getError()));
    }

    /**
     * Browse message in admin.
     * 
     * @param string  $status
     * @param string  $orderBy
     * @param int     $recTotal 
     * @param int     $recPerPage 
     * @param int     $pageID 
     * @access public
     * @return void
     */
    public function message($status = 'wait', $orderBy = 'time_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->lang->menuGroups->wechat = 'feedback';
        $this->lang->wechat->menu       = $this->lang->feedback->menu;

        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $messageList = $this->wechat->getMessage($status, $orderBy, $pager);

        $users = $this->loadModel('user')->getList();
        $wechatUsers = array();
        foreach($users as $user)
        {
            if(empty($user->openID)) continue;
            $wechatUsers[$user->openID] = $user->realname;
        }

        foreach($messageList as $message)
        {
            if(isset($wechatUsers[$message->from])) $message->from = $wechatUsers[$message->from];
        }

        $this->view->publicList  = $this->wechat->getList(); 
        $this->view->messageList = $messageList;
        $this->view->status      = $status;
        $this->view->pager       = $pager;
        $this->display();
    }
}
