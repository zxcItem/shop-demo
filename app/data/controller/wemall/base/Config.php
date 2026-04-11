<?php

declare(strict_types=1);


namespace app\data\controller\wemall\base;

use app\data\service\account\Account;
use app\data\service\wemall\ConfigService;
use think\admin\Controller;
use think\admin\Exception;

/**
 * 应用参数配置.
 * @class Config
 */
class Config extends Controller
{
    /**
     * 商城参数配置.
     * @auth true
     * @menu true
     * @throws Exception
     */
    public function index()
    {
        $this->title = '商城参数配置';
        $this->data = ConfigService::get();
        $this->pages = ConfigService::$pageTypes;
        $this->fetch();
    }

    /**
     * 修改参数配置.
     * @auth true
     * @throws Exception
     */
    public function params()
    {
        $this->vo = ConfigService::get();
        if ($this->request->isGet()) {
            $this->enableAndroid = (bool)Account::field(Account::ANDROID);
            $this->fetch();
        } else {
            ConfigService::set(array_merge($this->vo, $this->request->post()));
            $this->success('配置更新成功！');
        }
    }

    /**
     * 修改订单配置.
     * @auth true
     * @throws Exception
     */
    public function order()
    {
        $this->params();
    }

    /**
     * 修改协议内容.
     * @auth true
     * @throws Exception
     */
    public function content()
    {
        $input = $this->_vali(['code.require' => '编号不能为空！']);
        $title = ConfigService::$pageTypes[$input['code']] ?? null;
        if (empty($title)) {
            $this->error('无效的内容编号！');
        }
        if ($this->request->isGet()) {
            $this->title = "编辑{$title}";
            $this->data = ConfigService::getPage($input['code']);
            $this->fetch('index_content');
        } elseif ($this->request->isPost()) {
            ConfigService::setPage($input['code'], $this->request->post());
            $this->success('内容保存成功！', 'javascript:history.back()');
        }
    }
}
