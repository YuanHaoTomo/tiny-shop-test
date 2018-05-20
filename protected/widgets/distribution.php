<?php
/**
 * 分销插件
 */
class DistributionWidget extends Widget implements EventInterface{
    //后台权限验证
    public $needRightActions = array('index' => true,'user_list'=>true,'update_param'=>true);

    public $actions =  array(
        'back'=>array('index'=>true,'config_set'=>true,'user_list'=>true,'update_param'=>true),
        'front'=>array('users'=>true,'get_levels'=>true,'income'=>true,'goods_list'=>true,'promotion'=>true,'promotion_add'=>true,'promotion_del'=>true,'info'=>true,'apply'=>true,'apply_act'=>true,'promotion_type'=>true,'settlement'=>true,'settlement_act'=>true)
        );

    public function init(){
        $act = Req::args('act');
        if($act==null){
            $act = 'index';
        }
        //$url_index = '/'.Req::args('con').'/'.$act;

        if(isset($this->actions['back'][$act])){
            $this->initBack();
        }else if(isset($this->actions['front'][$act])){
            $this->initPre();
        }

    }


    public function settlement(){
        $this->isReview();
        $this->layout = 'index';
        $model = new Model('widget_distributor_order');
        $obj = $model->fields('sum(commission) as comm')->where('user_id='.$this->user['id'].' and status = 1')->find();
        if(isset($obj['comm'])){
            $this->assign('income',$obj['comm']);
        }else{
            $this->assign('income',0);
        }
        $this->redirect('settlement');
    }


    public function settlement_act(){
        $this->isReview();
        $model = new Model('widget_distributor_order');
        $obj = $model->fields('sum(commission) as comm')->where('user_id='.$this->user['id'].' and status = 1')->find();
        if(isset($obj['comm'])){
            $this->assign('income',$obj['comm']);

            //提现到余额
            $customerModel = new Model("customer");
            $customerModel->data(array('balance'=>"`balance`+".$obj['comm']))->where("user_id=".$this->user['id'])->update();
                Log::balance($obj['comm'],$this->user['id'],'分销佣金提现到个人账户余额：'.$obj['comm'],4,0);
            //记录日志
            $settlementModel = new Model('widget_distributor_settlement_log');
            $log_id = $settlementModel->data(array('user_id'=>$this->user['id'],'amount'=>$obj['comm'],'op_time'=>date('Y-m-d H:i:s')))->insert();
            //更新数据
            $model->where('user_id='.$this->user['id'].' and status = 1')->data(array('status'=>2,'settlement_id'=>$log_id))->update();


        }
        $this->redirect('settlement');

    }


    public function user_list(){
        $this->layout = 'admin';
        $condition = Req::args("condition");
        $condition_str = Common::str2where($condition);
        if($condition_str) $this->assign("where",$condition_str);
        else $this->assign("where","1=1");
        $config = $this->readConfig();
        $this->assign('config',$config);
        $this->assign("condition",$condition);
        $this->redirect();
    }


    public function update_param(){
        $name = Req::args('name');
        $value = intval(Req::args('value'));
        $id = Filter::int(Req::args('id'));
        if($name=='status'){

        }else{
            $name = 'category';
        }
        $model = new Model('widget_distributor');
        $model->where('id='.$id)->data(array("$name"=>$value))->update();
        $this->redirect('/distribution/user_list');
    }


    public function apply(){
        $this->layout = 'index';
        $model = new Model('widget_distributor');
        $obj = $model->where("user_id=".$this->user['id'])->find();
        if($obj&&isset($obj['status']) && $obj['status'] ==1){
            $this->redirect('info');
        }else{
            $config = $this->readConfig();
            $this->assign('config',$config);
            $this->assign('obj',$obj);
            $this->redirect('apply');
        }


    }



    public function apply_act(){

        $model = new Model('widget_distributor');
        $obj = $model->where("user_id=".$this->user['id'])->find();
        if($obj){
            $this->redirect('info');
        }else{
            $config = $this->readConfig();
            if(isset($config['is_condition']) && $config['is_condition'] == 1){
                $orderModel = new Model('order');
                $ammount = $config['ammount'];
                $order = $orderModel->fields('sum(real_amount) as total_amount')->where("user_id=".$this->user['id'].' and pay_status=1')->find();
                if($order && $order['total_amount']>=$ammount){

                }else{
                    $this->assign('msg','申请成为分销商，需要满足在本平台消费'.$ammount.'以上才可申请。');
                    $this->redirect('apply',false);
                    exit;
                }

            }
            $status = 1 ;
            if($config['is_review'] == 1){
                $status = 0;
            }
            $data = array('user_id'=>$this->user['id'],'parent_user_id'=>0,'parent_id_path'=>'','code'=>CHash::random(12,'char'),'status'=>$status);
            $model->data($data)->insert();
            $this->redirect('apply');
        }
    }

    public function promotion_type(){
        $this->isReview();
        $this->layout = 'index';
        $this->redirect('promotion_type');
    }



    public function info()
    {
        $this->isReview();

        $model = new Model('widget_distributor');
        $obj = $model->where('user_id='.$this->user['id'])->find();
        $category = $obj['category'];

        $exts = '(,[0-9]+){0,3}';
        $objs = array();
        $model = new Model("widget_distributor as di");
        if($obj){
            $like = "'{$obj['parent_id_path']},?{$obj['id']}{$exts}$'";
            $objs = $model->fields("count(*) as nums")->join("left join tiny_user cu on di.user_id = cu.id")->where("di.parent_id_path REGEXP {$like}")->find();
            $this->assign('users_num',$objs['nums']);
        }else{
            $this->assign('users_num',0);
        }


        $config = $this->readConfig();
        if($category==0){
            $category_name = '默认等级';
        }else{
            $category_name = $config['category'][($category+1)];
        }
        $this->assign('category_name',$category_name);

        $model = new Model('widget_distributor_order');
        $obj = $model->fields('sum(commission) as comm')->where('user_id='.$this->user['id'].' and status = 1')->find();
        if(isset($obj['comm'])){
            $this->assign('income',$obj['comm']);
        }else{
            $this->assign('income',0);
        }

        $obj = $model->fields('sum(commission) as comm')->where('user_id='.$this->user['id'].' and status = 0')->find();
        if(isset($obj['comm'])){
            $this->assign('pre_income',$obj['comm']);
        }else{
            $this->assign('pre_income',0);
        }

        $this->layout = 'index';
        $this->redirect('info');
    }

    public function checkRight($actionId)
    {
        $act = Req::args('act');
        if(isset($this->actions['back'][$act])){
            return parent::checkRight($actionId);
        }else if(isset($this->actions['front'][$act])){
            if(isset($this->user['name']) && $this->user['name']!=null){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }

    }

    public function noRight()
    {
        $act = Req::args('act');
        if(isset($this->actions['back'][$act])){
            $this->redirect("/admin/login");
        }else{
            $this->redirect("/simple/login");
        }
    }

    public function income()
    {
        $this->isReview();
        //佣金处理
        //在规定时间内夫退款的订单正式转为佣金收
        $config = $this->readConfig();
        $aftertime = isset($config['aftertime'])?Filter::int($config['aftertime']):30;
        $endtime = date('Y-m-d H:m:s',strtotime('-'.$aftertime.'day'));

        //$model = new Model('widget_distributor_order as wdo');
        //$objs = $model->fields('wdo.*,od.status as order_status,od.pay_status')->join('left join tiny_order as od on wdo.order_id = od.id')->where('wdo.user_id = '.$this->user['id'].' and od.pay_status = 1 and wdo.create_time < '."'{$endtime}'")->findAll();
        //
        $model = new Model('');
        //规定时间内退款的定单
        $reback_sql = "update tiny_widget_distributor_order wdo,tiny_order od set wdo.status = -1 where wdo.order_id = od.id and ".'wdo.user_id = '.$this->user['id'].' and od.pay_status = 3 and wdo.create_time > '."'{$endtime}'";
        $model->query($reback_sql);
        //正常结算佣金定单
        $comm_sql = "update tiny_widget_distributor_order wdo,tiny_order od set wdo.status = 1 where wdo.status = 0 and wdo.order_id = od.id and ".'wdo.user_id = '.$this->user['id'].' and od.pay_status = 1 and wdo.create_time < '."'{$endtime}'";

        $model->query($comm_sql);
        $this->layout = 'index';
        $this->redirect('income');
    }

    public function onAction($data){

    }

    //计算佣金
    public function onPayorder($data){
        //计算订单的利润
        //$data['id'] = 188;
        $model = new Model('order_goods as og');
        $objs = $model->join('products as pr on og.product_id = pr.id')->where('og.order_id = '.$data['id'])->findAll();
        $commission  = 0.0;
        foreach ($objs as $obj) {
            $commission += ($obj['real_price'] - $obj['cost_price'])*$obj['goods_nums'];
        }

        if($commission < 0){
            $commission = 0;
        }
        var_dump($commission);

        //查询三级分销
        $parents = $this->getDistributorLevel($data['user_id']);
        $config = $this->readConfig();
        //记录佣金记录表
        $model = new Model('widget_distributor_order');
        $data_log = array('order_id'=>$data['id'],'create_time'=>date('Y-m-d H:m:s'),'status'=>0);
        if(isset($parents[0])){
            $category = $parents[0]['category'];//用户的级别

            $level1 = $config['level1'][$category];
            $comm = $commission*$level1/100;
            $data_log['commission'] = $comm;
            $data_log['user_id'] = $parents[0]['user_id'];

            $model->data($data_log)->insert();
            var_dump($data_log);
        }

        if(isset($parents[1])){
            $category = $parents[1]['category'];//用户的级别
            $level1 = $config['level2'][$category];
            $comm = $commission*$level1/100;
            $data_log['commission'] = $comm;
            $data_log['user_id'] = $parents[1]['user_id'];
            $model->data($data_log)->insert();
        }

        if(isset($parents[2])){
            $category = $parents[2]['category'];//用户的级别
            $level1 = $config['level3'][$category];
            $comm = $commission*$level1/100;
            $data_log['commission'] = $comm;
            $data_log['user_id'] = $parents[2]['user_id'];
            $model->data($data_log)->insert();
        }


        //file_put_contents("payorder.txt",var_export($data,true));
    }

    private function getDistributorLevel($user_id){
        $model = new Model('widget_distributor');
        $obj = $model->where('user_id='.$user_id)->find();
        $parents = array();
        if(isset($obj['parent_id_path']) && $obj['parent_id_path'] != ''){
            $parents = explode(',',$obj['parent_id_path']);
            $parents = array_reverse($parents);
            $parents_ids = implode("','",$parents);
            $objs = $model->where("id in ('$parents_ids')")->findAll();
            $parents_array = array();
            foreach ($objs as $obj) {
                $parents_array["id_".$obj['id']] = $obj;
            }
            $parents_new = array();
            foreach ($parents as $value) {
                $parents_new[] = $parents_array["id_".$value];
            }
            $parents = $parents_new;
        }
        return $parents;
    }

    public function onLogin($data){
        // file_put_contents("events_new.txt",var_export($data,true));
    }

    //用户注册事件,记录分销用户信息
    function onReguser($data){
        $regcode = Session::get('regcode');
        $config = $this->readConfig();
        $status = isset($config['is_review']) && $config['is_review']=='1'?1:0;
        if($regcode != null){
            $model = new Model('widget_distributor');
            $obj = $model->where("code='$regcode'")->find();
            $regdata = array('user_id'=>$data['id'],'parent_user_id'=>0,'parent_id_path'=>'','code'=>CHash::random(12,'char'),'status'=>$status);
            if($obj){
                $regdata['parent_user_id'] = $obj['id'];
                $regdata['parent_id_path'] = trim($obj['parent_id_path'],',').','.$obj['id'];
            }
            $model->data($regdata)->insert();
        }else{
            //非推广注册用户
            $model = new Model('widget_distributor');
            $regdata = array('user_id'=>$data['id'],'parent_user_id'=>0,'parent_id_path'=>'','code'=>CHash::random(12,'char'),'status'=>$status);
            $model->data($regdata)->insert();
        }
    }


    function index(){
        //$this->initBack();
        $config = $this->readConfig();
        $this->assign('config',$config);
        $this->layout = "admin";
        $this->redirect('index');
        exit;
    }


    function users(){
        $this->isReview();
        $this->layout = "index";
        $this->redirect('users');
    }

    public function promotion()
    {
        $this->isReview();
        $config = $this->readConfig();
        $this->assign('config',$config);
        $min3 = min($config['level3']);
        $min2 = min($config['level2']);
        $min1 = min($config['level1']);
        $min = min($min1,$min2,$min3);
        $this->assign('min',$min);
        $this->layout = 'index';
        $this->redirect('promotion');
    }

    public function promotion_add()
    {
        $goods_id = Filter::int(Req::args('goods_id'));
        $model =  new Model('widget_distributor_promotion');
        $obj = $model->where('user_id = '.$this->user['id'].' and goods_id='.$goods_id)->find();
        $info = array('status'=>'fail','msg'=>'已经添加过推广');
        if(!$obj){
            $model->data(array('goods_id'=>$goods_id,'user_id'=>$this->user['id']))->insert();
            $info = array('status'=>'success','msg'=>'此商品成功加入推广');
        }
        echo JSON::encode($info);
    }

    public function promotion_del()
    {
        $goods_id = Filter::int(Req::args('goods_id'));
        $model =  new Model('widget_distributor_promotion');
        $model->where('user_id = '.$this->user['id'].' and goods_id='.$goods_id)->delete();
        $this->redirect('promotion');
    }

    public function goods_list()
    {
        $this->isReview();
        $model = new Model('widget_distributor');
        $obj = $model->where('user_id='.$this->user['id'])->find();
        $this->assign('regcode',$obj['code']);
        $config = $this->readConfig();
        $this->assign('config',$config);
        $min3 = min($config['level3']);
        $min2 = min($config['level2']);
        $min1 = min($config['level1']);
        $min = min($min1,$min2,$min3);
        $this->assign('min',$min);
        $this->layout = 'index';
        $this->redirect('goods_list');
    }



    public function get_levels(){
        $level = Filter::int(Req::args('level'));
        $page = Filter::int(Req::args('page'));
        $page = $page>0?$page:1;
        $model = new Model("widget_distributor");
        $obj = $model->where('user_id='.$this->user['id'])->find();
        $exts = "";

        for($i=0;$i<$level;$i++){
            $exts .= ',[0-9]+';
        }
        $objs = array();
        $model = new Model("widget_distributor as di");
        if($obj){
            $like = "'{$obj['parent_id_path']},?{$obj['id']}{$exts}$'";
            $objs = $model->join("left join tiny_user cu on di.user_id = cu.id")->where("di.parent_id_path REGEXP {$like}")->findPage($page,10,1,true);
        }
        $objs['status'] = "success";
        echo JSON::encode($objs);
    }


    function config_set(){
        $path = str_replace(".php", '/config.php',__file__);
        $datas = Req::args();
        $fields = array('is_review','is_condition','ammount','category','reserved','level1','level2','level3','aftertime','withdraw_condition');
        foreach ($datas as $key => $value) {
            if(!in_array($key,$fields)){
                unset($datas[$key]);
            }
        }
        $file = new File($path,'w');
        $file->write('<?php return '.var_export($datas,true).';');
         $this->assign('message', '信息保存成功！');
        $this->redirect('index',false);
    }

    private function readConfig(){
        $path = str_replace(".php", '/config.php',__file__);
        return include $path;
    }

    /**
     * 判定用户是否为已经通过分销身份审核
     * @return boolean [description]
     */
    private function isReview(){
        $this->layout = 'index';
        $model = new Model('widget_distributor');
        $obj = $model->where("user_id=".$this->user['id'])->find();
        if($obj&&isset($obj['status']) && $obj['status'] ==1){
            $this->assign('distribution',$obj);
        }else{
            $config = $this->readConfig();
            $this->assign('config',$config);
            $this->assign('obj',$obj);
            $this->redirect('apply');
            exit;
        }
    }

    private function initPre(){
        header("Content-type: text/html; charset=".$this->encoding);
        $this->model = new Model();
        $this->safebox =  Safebox::getInstance();
        $this->user = $this->safebox->get('user');
        $category = Category::getInstance();
        if($this->user==null){
            $this->user = Common::autoLoginUserInfo();
            $this->safebox->set('user',$this->user);
        }

        $this->category = $category->getCategory();

        $this->assign("category",$this->category);
        $keyword = urldecode(Req::args('keyword'));
        if($keyword!=null)$this->assign("keyword",Filter::text($keyword));
        $act = Req::args('act');
        $url_index = '/'.Req::args('con').'/'.$act;
        $url_index = Url::urlFormat($url_index);
        $this->assign("url_index",Url::requestUri());

        //配制中的站点信息
        $config = Config::getInstance();
        $site_config = $config->get("globals");
        $this->assign('seo_title',$site_config['site_name']);
        $this->assign('site_title',$site_config['site_name']);
        $this->assign('seo_keywords',$site_config['site_keywords']);
        $this->assign('seo_description',$site_config['site_description']);
    }

    private function initBack(){

        $_menus = include (APP_CODE_ROOT.'views/plugin/menu.php');

        $menu = new Menu();
        $menus = $menu->getMenus();
        $menus['nodes'] = array_merge($menus['nodes'],$_menus['nodes']);
        $menus['subMenu'] = array_merge($menus['subMenu'],$_menus['subMenu']);
        $menu->setMenus($menus);
        $menu->init_menu();
        // var_export($menus);
        // exit;



        $nav_act = Req::get('act') == null ? $this->defaultAction : Req::get('act');
        $nav_act = preg_replace("/(_edit)$/", "_list", $nav_act);

        $this->assign('mainMenu', $menu->getMenu());
        $menu_index = $menu->current_menu();
        $this->assign('menu_index', $menu_index);
        $this->assign('subMenu', $menu->getSubMenu($menu_index['menu']));
        $this->assign('menu', $menu);


        $this->assign('nav_link', '/' . Req::get('con') . '/' . $nav_act);
        $this->assign('node_index', $menu->currentNode());
        $this->safebox = Safebox::getInstance();
        $this->manager = $this->safebox->get('manager');
        $this->assign('manager', $this->safebox->get('manager'));

        $currentNode = $menu->currentNode();
        if (isset($currentNode['name'])) {
            $this->assign('admin_title', $currentNode['name']);
        }
    }

}
