<?php
class PluginController extends Controller
{
    public $layout           = 'admin';
    private $top             = null;
    public $needRightActions = array('*' => true);
    private $manager;
    public function init()
    {
        $_menus = include (APP_CODE_ROOT.'views/plugin/menu.php');

        $menu = new Menu();
        $menus = $menu->getMenus();
        $menus['nodes'] = array_merge($menus['nodes'],$_menus['nodes']);
        $menus['subMenu'] = array_merge($menus['subMenu'],$_menus['subMenu']);
        $menu->setMenus($menus);
        $menu->init_menu();

        $this->assign('mainMenu', $menu->getMenu());
        $menu_index = $menu->current_menu();
        $this->assign('menu_index', $menu_index);
        $this->assign('subMenu', $menu->getSubMenu($menu_index['menu']));
        $this->assign('menu', $menu);
        $nav_act = Req::get('act') == null ? $this->defaultAction : Req::get('act');
        $nav_act = preg_replace("/(_edit)$/", "_list", $nav_act);
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
    public function noRight()
    {
        $this->layout = 'blank';
        if ($this->is_ajax_request()) {
            echo JSON::encode(array('status' => 'fail', 'msg' => '没有该项操作权限!'));
        } else {
            $this->redirect("/admin/noright");
        }
    }

    //插件安装
    public function install_widget(){
        $widget = Req::args('widget');
        $name = Req::args('widget');
        $filePath = $this->widgetPath($name);
        $fileName = $filePath.'status.php';

        $file = New File($fileName,'w+');

        $file->write('<?php return true;');

        $lockFileName = $filePath.'install.lock';
        $lockfile = New File($lockFileName,'w+');
        $lockfile->write('');

        $this->opMenu($name,true);
        $this->opUcenterMenu($name,true);
        $this->opEvents($name,true);
        $this->opSql($name,true);
        $this->redirect('index');
    }

    //插件卸载
    public function uninstall_widget(){
        $name = Req::args('widget');
        $filePath = $this->widgetPath($name);
        $fileName = $filePath.'status.php';
        $file = New File($fileName,'w+');

        $file->write('<?php return false;');
        $lockFileName = $filePath.'install.lock';
        unlink($lockFileName);

        $this->opMenu($name,false);
        $this->opUcenterMenu($name,false);
        $this->opEvents($name,false);
        $this->opSql($name,false);
        $this->redirect('index');
    }

    //插件的开启
    public function open_widget(){
        $name = Req::args('widget');
        $filePath = $this->widgetPath($name);
        $lockFileName = $filePath.'install.lock';
        if(is_file($lockFileName)){
            $fileName = $filePath.'status.php';
            $file = New File($fileName,'w+');
            $file->write('<?php return true;');

            $this->opMenu($name,true);
            $this->opUcenterMenu($name,true);
            $this->opEvents($name,true);
        }
        $this->redirect('index');
    }
     //插件的关闭
    public function close_widget(){
        $name = Req::args('widget');
        $filePath = $this->widgetPath($name);
        $lockFileName = $filePath.'install.lock';
        if(is_file($lockFileName)){
            $fileName = $filePath.'status.php';
            $file = New File($fileName,'w+');

            $file->write('<?php return false;');
            $this->opMenu($name,false);
            $this->opUcenterMenu($name,false);
            $this->opEvents($name,false);
        }
        $this->redirect('index');
    }



    public function opMenu($name,$op){
        $filePath = $this->widgetPath($name);
        $installFile = $filePath.'install.php';
        $menuFile = APP_CODE_ROOT.'views/plugin/menu.php';
        if(is_file($installFile)){
            $installInfo = include($installFile);
            $menus = include($menuFile);
            if($op==true){
                $menus['subMenu'][$name] = array('name'=>$installInfo['name'],'parent'=>'plugin');
                foreach ($installInfo['menu'] as $item) {
                    $menus['nodes']['/'.$name.'/'.$item['action']] = array('name'=>$item['name'],'parent'=>$name);
                }

            }else{
                if(isset($menus['subMenu'][$name])){
                    unset($menus['subMenu'][$name]);
                }
                foreach ($menus['nodes'] as $key=>$value) {
                    if(substr($key,0,strlen($name)+2) == '/'.$name.'/'){
                        unset($menus['nodes'][$key]);
                    }
                }
            }
            $menus_str = var_export($menus,true);
            $file = new File($menuFile,'w+');
            $file->write('<?php return '.$menus_str.';');
        }
    }


    public function opUcenterMenu($name,$op){
        $filePath = $this->widgetPath($name);
        $installFile = $filePath.'install.php';
        $menuFile = APP_CODE_ROOT.'views/plugin/ucenter_menu.php';
        $menus =  Array();
        if(is_file($installFile)){
            $installInfo = include($installFile);
            if(is_file($menuFile)){
                $menus = include($menuFile);
                if($op==true){
                    if(isset($installInfo['ucenter_menu'])){
                        $action='/'.$name.'/'.$installInfo['ucenter_menu']['action'];
                        $menus[$action] = $installInfo['ucenter_menu']['name'];
                    }

                }else{
                    foreach ($menus as $key=>$value) {
                        if(substr($key,0,strlen($name)+2) == '/'.$name.'/'){
                            unset($menus[$key]);
                        }
                    }
                }
            }


            $menus_str = var_export($menus,true);
            $file = new File($menuFile,'w+');
            $file->write('<?php return '.$menus_str.';');
        }
    }


    public function opEvents($name,$op){
        $filePath = $this->widgetPath($name);
        $installFile = $filePath.'install.php';
        $eventsFile = APP_CODE_ROOT.'config/events.php';
        if(is_file($installFile)){
            $installInfo = include($installFile);
            $events = include($eventsFile);
            if($op){
                foreach ($installInfo['events'] as $item) {
                   $events[$item][ucfirst($name).'Widget'] = true;
                }
            }else{
                foreach ($installInfo['events'] as $item) {
                    foreach ($events[$item] as $k=>$v) {
                        // if($v==ucfirst($name).'Widget'){
                        //     unset($events[$item][$k]);
                        // }
                        unset($events[$item][ucfirst($name).'Widget']);
                    }
                }
            }
            $events_str = var_export($events,true);
            $file = new File($eventsFile,'w+');
            $file->write('<?php return '.$events_str.';');

        }

    }

    /**
     * SQL的安装或卸载
     * @param  string $name [description]
     * @param  [type] $op   [description]
     * @return [type]       [description]
     */
    public function opSql($name,$op){
        $filePath = $this->widgetPath($name);
        $installFile = $filePath.'install.php';
        if(is_file($installFile)){
            $installInfo = include($installFile);
            if($op){
                if(isset($installInfo['sqls']) && isset($installInfo['sqls']['install'])){
                    $backup = new BackUp();
                    $sqls   = $installInfo['sqls']['install'];
                    $backup->install($sqls);
                }
            }else{
                if(isset($installInfo['sqls']) && isset($installInfo['sqls']['uninstall'])){
                    $backup = new BackUp();
                    $sqls   = $installInfo['sqls']['uninstall'];
                    $backup->install($sqls);
                }
            }
        }
    }


    public function widgetPath($name)
    {
        $filePath = APP_CODE_ROOT.'widgets/'.strtolower($name).'/';
        $theme = Tiny::app()->getTheme();
        if($theme!==null){
            $temfilePath = $theme->getBasePath().DIRECTORY_SEPARATOR.'widgets/'.strtolower($name).'/';
            if(is_dir($temfilePath))$filePath = $temfilePath;
        }

        if(is_dir($filePath)){
            return $filePath;
        }
        return null;
    }

    public static function ucenterMenu(){
        $menuFile = APP_CODE_ROOT.'views/plugin/ucenter_menu.php';
        $menus =  Array();
        if(is_file($menuFile)){
            $menus = include($menuFile);
        }
        return $menus;
    }

}
