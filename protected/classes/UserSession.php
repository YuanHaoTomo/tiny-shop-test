<?php
class UserSession{
    private $open_id;
    private $session;

    function __construct($open_id){
        $this->open_id = $open_id;
        $model =  new Model("user_session");
        $obj = $model->where("open_id='".$open_id."'")->find();
        if(isset($obj['session'])){
            $this->session = unserialize($obj['session']);
        }else{
            $this->session = new Session();
            $model =  new Model("user_session");
            $model->data(array('session'=>serialize($this->session),"open_id"=>$this->open_id))->insert();
        }
    }

    function get($key){
        return $this->session->get($key);
    }

    function clear($key){
        $this->session->clear($key);
    }

    function clearAll(){
        $this->session->clearAll();
    }

    function set($name,$value='',$time='1800',$path='/',$domain=null){
        $this->session->set($name,$value,$time,$path,$domain);
        $session = serialize($this->session);
        $model =  new Model("user_session");
        $model->data(array('session'=>$session,"open_id"=>$this->open_id))->update();
    }
}
