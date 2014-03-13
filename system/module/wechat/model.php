<?php
/**
 * The model file of wechat module of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Tingting Dai <daitingting@cxirangit.com>
 * @package     wechat
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
class wechatModel extends model
{
    /**
     * Get a public account by id.
     * 
     * @param  int    $id 
     * @access public
     * @return object
     */
    public function getByID($id)
    {
        return $this->dao->findByID($id)->from(TABLE_WX_PUBLIC)->fetch();
    }

    /** 
     * Get public list.
     * 
     * @access public
     * @return array
     */
    public function getList()
    {
        $publics = $this->dao->select('*')->from(TABLE_WX_PUBLIC)->orderBy('addedDate_desc')->fetchAll('id');
        if(!$publics) return array();
        foreach($publics as $public) $public->url = getWebRoot(true) . commonModel::createFrontLink('wechat', 'response', "id=$public->id");
        return $publics;
    }

    /**
     * Create a public.
     * 
     * @access public
     * @return int|bool
     */
    public function create()
    {
        $public = fixer::input('post')->add('addedDate', helper::now())->get();
        $this->dao->insert(TABLE_WX_PUBLIC)->data($public)->autoCheck()->exec();
        return !dao::isError();
    }

    /**
     * Get response for a message.
     * 
     * @param  object    $message 
     * @access public
     * @return void
     */
    public function getResponse($message)
    {
        $response = new stdclass();
        $response->msgType = 'text';
        $response->content = '你好' . $message->event . $message->eventKey;
        return $response;
    }

    /**
     * Set response for a public.
     * 
     * @param  int     $publicID
     * @access public
     * @return void
     */
    public function setResponse($publicID)
    {
        $response = fixer::input('post')
            ->add('public', $publicID)
            ->setIF($this->post->group == 'subscribe', 'key', 'subscribe')
            ->get();

        $this->dao->insert(TABLE_WX_RESPONSE)->data($response)->autoCheck()->exec();
        return !dao::isError();
    }
}
