<?php
class ApiController extends Controller
{
    public $layout='';
    public $model = null;
    public $needRightActions = array('*'=>false);
    public function init()
    {
        header("Content-type: application/json; charset=".$this->encoding);
        // header("Content-type: text/html; charset=".$this->encoding);
        $this->model = new Model();
    }

    public function __call($name,$args=null)
    {
        // $server = Oauth2Service::getInstance();
        // if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {

        //     $server->getResponse()->send();
        //     die;
        // }else{
            $args = Req::args();
            unset($args['con'],$args['act']);
            $ApiService = new ApiService();
            echo JSON::encode($ApiService->api($name,$args));
        //}
    }
    public function test(){
        //$dbinfo = Tiny::app()->db;
        //$pdo = TPdo::getInstance($dbinfo);
        //$sql="select * from tiny_goods ";
       // $info = $pdo->doSql($sql);//->doSql($sql,array(":id"=>32));

       // Tiny::dump($info);
       // $model =  new Model('test');
       // $model->beginTransaction();
       // $insert1 = $model->data(array('id'=>1,'name'=>'1','content'=>'1'))->insert();
       // $insert2 = $model->data(array('id'=>2,'name'=>'2','content'=>'2'))->insert();
       // if($insert2 && $insert1){
       //  $model->commit();
       // }else{
       //  $model->rollBack();
       // }
       // $insert2 = $model->data(array('id'=>2,'name'=>'3','content'=>'3'))->insert();
    }
}
