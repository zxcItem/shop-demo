<?php


namespace app\data\controller\account;

use app\data\model\account\DataAccountMsm;
use app\data\service\Message as MessageService;
use app\data\service\message\Alisms;
use think\admin\Controller;
use think\admin\helper\QueryHelper;

/**
 * 手机短信管理
 * @class Message
 * @package app\data\controller
 */
class Message extends Controller
{

    /**
     * 缓存配置名称
     * @var string
     */
    protected $smskey;

    /**
     * 初始化控制器
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        $this->smskey = 'account.smscfg';
    }

    /**
     * 手机短信管理
     * @auth true
     * @menu true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        DataAccountMsm::mQuery()->layTable(function () {
            $this->title = '手机短信管理';
            $this->scenes = MessageService::$scenes;
        }, static function (QueryHelper $query) {
            $query->equal('status')->like('smsid,scene,phone')->dateBetween('create_time');
        });
    }

    /**
     * 修改短信配置
     * @auth true
     * @return void
     * @throws \think\admin\Exception
     */
    public function config()
    {
        if ($this->request->isGet()) {
            $this->vo = sysdata($this->smskey);
            $this->scenes = MessageService::$scenes;
            $this->regions = Alisms::regions();
            $this->fetch();
        } else {
            sysdata($this->smskey, $this->request->post());
            $this->success('修改配置成功！');
        }
    }
}