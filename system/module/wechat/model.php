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
        $publics = $this->dao->select('*')->from(TABLE_WX_PUBLIC)->orderBy('addedDate')->fetchAll('id');
        if(!$publics) return array();
        foreach($publics as $public) $public->url = rtrim(getWebRoot(true), '/') . commonModel::createFrontLink('wechat', 'response', "id=$public->id");
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
     * Update a public.
     * 
     * @param  int $publicID 
     * @access public
     * @return void
     */
    public function update($publicID)
    {
        $public = fixer::input('post')->get();
        $this->dao->update(TABLE_WX_PUBLIC)->data($public)->autoCheck()->exec();
        return !dao::isError();
    }

    /**
     * Delete a public.
     * 
     * @param  int      $publicID 
     * @access public
     * @return void
     */
    public function delete($publicID, $null = null)
    {
        $this->dao->delete()->from(TABLE_WX_PUBLIC)->where('id')->eq($publicID)->exec();
        return !dao::isError();
    }

    /** 
     * Get response list.
     * 
     * @param  int    $publicID 
     * @access public
     * @return array
     */
    public function getResponseList($publicID)
    {
        $responses = $this->dao->select('*')->from(TABLE_WX_RESPONSE)->where('public')->eq($publicID)->andWhere('`group`')->eq('')->fetchAll('id');

        foreach($responses as $response) $this->processResponse($response);

        return $responses;
    }

    /**
     * Get response for a message.
     * 
     * @param  int       $public 
     * @param  object    $message 
     * @access public
     * @return object
     */
    public function getResponseForMessage($public, $message)
    {
        if($message->msgType == 'text')
        {
            $response = $this->getResponseByKey($public, $message->content);    
        }
        elseif($message->msgType == 'event')
        {
            if($message->event == 'subscribe') $message->eventKey = 'subscribe';
            $response = $this->getResponseByKey($public, $message->eventKey);    
        }

        if(empty($response))
        {
            $response = $this->getResponseByKey($public, 'default');    
        }

        if(!empty($response))
        {
            $message->status   = $response->key == 'default' ? 'wait' : 'replied';
            $message->response = $response->key;

            if($response->type == 'text' or $response->type == 'link')
            {
                $reply = new stdclass();
                $reply->msgType = 'text';
                $reply->content = $response->content;
            } 
            elseif($response->type == 'news')
            {
                $reply = $response->content;
            }
        }
        $this->saveMessage($public, $message);

        return $reply;
    }

    /**
     * Get response by key.
     * 
     * @param  int    $public 
     * @param  int    $key 
     * @access public
     * @return object
     */
    public function getResponseByKey($public, $key)
    {
        $response = $this->dao->select('*')->from(TABLE_WX_RESPONSE)
            ->where('public')->eq($public)
            ->andWhere('`key`')->eq($key)
            ->fetch();
        return $this->processResponse($response);
    }

    /**
     * Process a response. 
     * 
     * @param  object    $response 
     * @access public
     * @return void
     */
    public function processResponse($response)
    {
        if(empty($response)) return $response;
        
        if($response->type == 'text')
        {
            if($response->source != 'manual')
            {
                $response->content = $this->parseResponseContent($response->source);
            }
        }

        if($response->type == 'news')
        {
            $response->params  = json_decode($response->content);
            $response->content = $this->parseResponseContent($response->params);
        }

        if($response->type == 'link')
        {
            if($response->source != 'manual')
            {
               $response->content = $response->source;
            }
        }

        return $response;
    }

    /**
     * Create response for a public.
     * 
     * @param  int     $publicID
     * @access public
     * @return void
     */
    public function setResponse($publicID)
    {
        $response = fixer::input('post')->add('public', $publicID)->get();

        if($response->type == 'news')
        { 
            $response->source = 'system';
            $content = array();
            $content['block']    = $response->block;
            $content['category'] = $response->category;
            if(isset($response->limit)) $content['limit'] = $response->limit;
            $response->content = json_encode($content);
        }

        $this->dao->replace(TABLE_WX_RESPONSE)
            ->data($response, $skip = 'linkModule, textBlock, block, category, limit')
            ->autoCheck()
            ->exec();

        return !dao::isError();
    }

    /**
     * Get menu to commit.
     * 
     * @param  int    $public 
     * @access public
     * @return void
     */
    public function getMenu($public)
    {
        $menus = $this->dao->select('*')->from(TABLE_CATEGORY)->where('type')->like('wechat_%')->orderBy('`order`')->fetchGroup('parent');
        $responseList = $this->dao->select('*')->from(TABLE_WX_RESPONSE)->where('public')->eq($public)->andWhere('`group`')->eq('menu')->fetchAll('key');
        foreach($responseList as $response) $response = $this->processResponse($response);

        $buttons = array();
        foreach($menus[0] as $menu)
        {
            if(!empty($menus[$menu->id]))
            {
                $submenus = new stdclass();
                $submenus->name = $menu->name;
                foreach($menus[$menu->id] as $submenu)
                {
                    if(!isset($responseList['m_' . $submenu->id])) continue;
                    $response = $this->convertResponse2Menu($responseList['m_' . $submenu->id]);
                    $response->name = $submenu->name;
                    $submenus->sub_button[] = $response;
                }
                $buttons[] = $submenus;
            }
            else
            {
                if(!isset($responseList['m_' . $menu->id])) continue;
                $response = $this->convertResponse2Menu($responseList['m_' . $menu->id]);
                $response->name = $menu->name;
                $buttons[] = $response;
            }
        }
        return array('button' => $buttons);
    }

    /**
     * Convert response.
     * 
     * @param  int    $response 
     * @access public
     * @return void
     */
    public function convertResponse2Menu($response)
    {
        $result = new stdclass();
        if($response->type == 'link')
        {
            $result->type = 'view';
            $result->url  = $response->source == 'manual' ? $response->content : $response->source;
        }
        else
        {
            $result->type = 'click';
            $result->key  = $response->key;
        }
        return $result;
    }

    /**
     * Delete a response.
     * 
     * @param  int     $response 
     * @access public
     * @return void
     */
    public function deleteResponse($response, $null = null)
    {
        $this->dao->delete()->from(TABLE_WX_RESPONSE)->where('id')->eq($response)->exec();
        return !dao::isError();
    }

    /**
     * Parse response content. 
     * 
     * @param  string|object    $content 
     * @access public
     * @return void
     */
    public function parseResponseContent($content)
    {
        if(!is_object($content))
        {
            if($content == 'company') return strip_tags($this->config->company->desc);
            if($content == 'contact')
            {
                $contact = json_decode($this->config->company->contact);
                $text = '';
                foreach($contact as $item => $value)
                {
                    if(empty($value)) continue;
                    $text .= $this->lang->company->{$item} . $this->lang->colon . $value . "\n";
                }
                return $text;
            }
            return $content;
        } 
        else
        {
            $userFunc = array('wechatModel', 'parse' . ucfirst($content->block));
            return call_user_func($userFunc, $content);
        }
    }

    /**
     * Parse article tree. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseArticleTree($content)
    {
        return $this->parseTree($content, 'article');
    }

    /**
     * Parse product tree. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseProductTree($content)
    {
        return $this->parseTree($content, 'product');
    }

    /**
     * Parse tree. 
     * 
     * @param  object    $content 
     * @param  string    $type 
     * @access public
     * @return object
     */
    public function parseTree($content, $type)
    {
        $categories = $this->dao->select('*')->from(TABLE_CATEGORY)->where('id')->in($content->category)->fetchAll('id');

        $response = new stdclass();
        $response->msgType = 'news';

        foreach($content->category as $categoryID)
        {
            if(empty($categories[$categoryID])) continue;
            $category = $categories[$categoryID];

            $article = new stdclass();
            $article->title       = $category->name;
            $article->url         = rtrim(getWebRoot(true), '/') . commonModel::createFrontLink($type, 'browse', "categoryID={$category->id}", "category={$category->alias}");
            $article->description =  $category->desc;
            $response->articles[] = $article;
        }
        return $response;
    }

    /**
     * Parse latest article. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseLatestArticle($content)
    {
        return $this->parseArticles($content);
    }

    /**
     * Parse hot article. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseHotArticle($content)
    {
        return $this->parseArticles($content);
    }

    /**
     * Parse articles. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseArticles($content)
    {
        $orderByList = array('latestArticle' => 'id_desc', 'hotArticle' => 'views_desc');

        $this->app->loadClass('pager', true);
        $pager = new pager($recTotal = 0, $recPerPage = $content->limit, 1);

        $articles = $this->loadModel('article')->getList('article', $content->category, $orderByList[$content->block], $pager);

        $response = new stdclass();
        $response->msgType = 'news';

        foreach($articles as $article)
        {
            $item = new stdclass();
            $item->title       = $article->title;
            $item->url         = rtrim(getWebRoot(true), '/') . $this->article->createPreviewLink($article->id);
            $item->description = $article->summary;
            if(!empty($article->image)) $item->picUrl = rtrim(getWebRoot(true), '/') . $article->image->primary->smallURL;
            $response->articles[] = $item;
        }
        return $response;
    }

    /**
     * Parse latest product. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseLatestProduct($content)
    {
        return $this->parseProducts($content);
    }

    /**
     * Parse hot product. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseHotProduct($content)
    {
        return $this->parseProducts($content);
    }

    /**
     * Parse products. 
     * 
     * @param  object    $content 
     * @access public
     * @return object
     */
    public function parseProducts($content)
    {
        $orderByList = array('latestProduct' => 'id_desc', 'hotProduct' => 'views_desc');

        $this->app->loadClass('pager', true);
        $pager = new pager($recTotal = 0, $recPerPage = $content->limit, 1);

        $products = $this->loadModel('product')->getList($content->category, $orderByList[$content->block], $pager);

        $response = new stdclass();
        $response->msgType = 'news';

        foreach($products as $product)
        {
            $categories    = $product->categories;
            $categoryAlias = current($categories)->alias;

            $article = new stdclass();
            $article->title       = $product->name;
            $article->url         = rtrim(getWebRoot(true), '/') . commonModel::createFrontLink('product', 'view',  "productID=$product->id", "name=$product->alias&category=$categoryAlias");
            $article->description = $product->summary;
            if(!empty($product->image)) $article->picUrl = rtrim(getWebRoot(true), '/') . $product->image->primary->smallURL;
            $response->articles[] = $article;
        }
        return $response;
    }

    /**
     * Save message. 
     * 
     * @param  int      $public 
     * @param  object   $data 
     * @access public
     * @return void
     */
    public function saveMessage($public, $data)
    {
        $message = new stdclass();
        $message->public   = $public;
        $message->wid      = isset($data->msgId) ? $data->msgId : '';
        $message->from     = $data->fromUserName;
        $message->to       = $data->toUserName;
        $message->response = $data->response;
        $message->type     = $data->msgType;
        $message->content  = isset($data->content) ? $data->content : '';
        $message->event    = isset($data->event) ? $data->event : '';
        $message->eventKey = isset($data->eventKey) ? $data->eventKey : '';
        $message->status   = isset($data->status) ? $data->status : 'wait';
        $message->time     = helper::now();

        $this->dao->insert(TABLE_WX_MESSAGE)->data($message)->autoCheck()->exec();
        return !dao::isError();
    }

    /**
     * Get message. 
     * 
     * @param  string   $status 
     * @param  string   $orderBy 
     * @param  object   $pager 
     * @access public
     * @return array 
     */
    public function getMessage($status, $orderBy, $pager = null)
    {
        $messages = $this->dao->select('*')->from(TABLE_WX_MESSAGE)
            ->where('status')->eq($status)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        $menus = $this->dao->select('*')->from(TABLE_CATEGORY)->where('type')->like('wechat%')->fetchAll('id');

        foreach($messages as $message)
        {
            if(strpos($message->response, 'm_') !== false)
            {
                $menuId = str_replace('m_', '', $message->response);
                $message->content = $menus[$menuId]->name;
                $message->content = $this->lang->wechat->message->menu . $this->lang->colon . $message->content;
            }

            if($message->response == 'default')
            {
                $message->response = $this->lang->wechat->response->default;
            }
            elseif($message->response == 'subscribe')
            {
                $message->response = $this->lang->wechat->response->subscribe;
            }
            elseif(strpos($message->response, 'm_') !== false)
            {
                $message->response = $message->content;
            }
            else
            {
                $message->response = $this->lang->wechat->response->keywords . $this->lang->colon . $message->response;
            }
        }
        return $messages;
    }

    /**
     * Pull fans.
     * 
     * @access public
     * @return void
     */
    public function pullFans()
    {
        $publicList = $this->dao->select('*')->from(TABLE_WX_PUBLIC)->fetchAll();
        foreach($publicList as $public)
        {
            $this->app->loadClass('wechatapi', true);
            $api  = new wechatapi($public->token, $public->appID, $public->appSecret, $this->config->debug);
            $fans = $api->getFans();
            foreach($fans->data->openid as $openID)
            {
                $pulledFan = $this->dao->select("count(*) as count")->from(TABLE_OAUTH)
                    ->where('provider')->eq('wechat_' . $public->id)
                    ->andWhere('openID')->eq($openID)
                    ->fetch('count');

                if($pulledFan) continue;

                $user = array();
                $user['openID']   = $openID;
                $user['provider'] = 'wechat_' . $public->id;
                $user['account']  = uniqid('wx_');
                $user['join']     = helper::now();

                $this->dao->insert(TABLE_OAUTH)->data($user, $skip = 'join')->exec();
                $this->dao->insert(TABLE_USER)->data($user, $skip = 'openID,provider')->exec();
            }
        }
        return true;
    }

    /**
     * Get fans info. 
     * 
     * @param  array    $users 
     * @access public
     * @return void
     */
    public function getFansInfo($users)
    {
        foreach($users as $user)
        {
            if(!$user->provider) continue;
            $public = $this->getByID(str_replace('wechat_', '', $user->provider));
            $this->app->loadClass('wechatapi', true);
            $api = new wechatapi($public->token, $public->appID, $public->appSecret, $this->config->debug);
            $fan = $api->getUserInfo($user->openID);

            if($fan->subscribe != 1) continue;

            $user->nickname = $fan->nickname;
            $user->realname = $fan->nickname;
            $user->address  = $fan->country . ' ' . $fan->province . ' ' . $fan->city;
            $user->join     = date('Y-m-d H:i:s', $fan->subscribe_time);

            if($fan->sex == 0) $user->gender = 'u';
            if($fan->sex == 1) $user->gender = 'm';
            if($fan->sex == 2) $user->gender = 'f';

            $this->dao->update(TABLE_USER)->data($user, $skip = 'openID,provider')->where('id')->eq($user->id)->exec();
        }
        return true;
    }
}
