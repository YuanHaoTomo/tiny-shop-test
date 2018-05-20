<?php
/**
 * Tiny - A PHP Framework For Web Artisans
 * @author Tiny <tinylofty@gmail.com>
 * @copyright Copyright(c) 2010-2014 http://www.tinyrise.com All rights reserved
 * @version 1.0
 */
/**
 * 插件类
 *
 * @author Tiny
 * @class Widget
 */
// class Widget extends Object
// {
//     protected $encoding='UTF-8';
//     public $cache;
//     public $cacheTime;
//     private $action;
//     private $id;
//     /**
//      * 构造函数
//      *
//      * @access public
//      * @param string $id
//      * @return mixed
//      */
//     public function __construct($id='widget')
//     {
//         $this->id = $id;
//     }
//     /**
//      * 初始化
//      *
//      * @access public
//      * @return mixed
//      */
//     public function init()
//     {
//     }
//     /**
//      * 执行函数
//      *
//      * @access public
//      * @return mixed
//      */
//     public function run()
//     {

//         $method = $this->getAction();
//         $args = $this->args;
//         if($this->cache == "true"){
//             $cache = CacheFactory::getInstance();
//             $content = $cache->get($method.$args);
//             if($content===null){
//                 ob_start();
//                 ob_implicit_flush(false);
//                 $this->doMethod($method,$args);
//                 $content = ob_get_clean();
//                 $cache->set($method.$args,$content,intval($this->cacheTime));
//             }
//             echo $content;
//         }
//         else $this->doMethod($method,$args);

//     }
//     /**
//      * 执行方法
//      *
//      * @access global
//      * @param mixed $method
//      * @param mixed $args
//      * @return mixed
//      */
//     function doMethod($method,$args)
//     {
//         if(method_exists($this,$method)){
//             if($args===null)$this->$method();
//             else $this->$method($args);
//         }
//         else{
//             $className = $this->id;
//             $this->renderFile("$className/$method",$this->output);
//         }
//     }
//     /**
//      * 设置action
//      *
//      * @access public
//      * @param mixed $action
//      * @return mixed
//      */
//     public function setAction($action)
//     {
//         $this->action = $action;
//     }
//     /**
//      * 取得action
//      *
//      * @access public
//      * @return mixed
//      */
//     public function getAction()
//     {
//         if($this->action===null){
//             $action = Req::args('act')==null?'index':Req::args('act');
//             $this->setAction($action);
//         }
//         return $this->action;
//     }
//     /**
//      * 取得ID
//      *
//      * @access public
//      * @return mixed
//      */
//     public function getId()
//     {
//         return $this->id;
//     }
//     /**
//      * 取得视图文件
//      *
//      * @access public
//      * @param mixed $viewName
//      * @return mixed
//      */
//     public function getViewFile($viewName)
//     {
//         if(($theme = Tiny::app()->getTheme())!==null){
//             $viewFile = $theme->getBasePath().DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.$viewName.'.html';
//             if(file_exists($viewFile))return $viewFile;
//         }
//         return APP_CODE_ROOT.'widgets/'.$viewName.'.html';
//     }
//     /**
//      * 渲染视图
//      *
//      * @access public
//      * @param mixed $viewFile
//      * @param bool $return
//      * @return mixed
//      */
//     public function renderFile($viewFile,$return=false)
//     {
//         $content=$this->renderInternal($viewFile,$return);
//         return $content;
//     }
//     /**
//      * 渲染
//      *
//      * @access public
//      * @param mixed $viewFile
//      * @param bool $return
//      * @return mixed
//      */
//     public function renderInternal($viewFile,$return=false)
//     {

//         $data = $this->properties;
//         if(is_array($data)){
//             extract($data,EXTR_PREFIX_SAME,'data');
//         }
//         $tplfile  = $this->getViewFile($viewFile);
//         if(file_exists($tplfile)){
//             $runfile= Tiny::app()->getRuntimePath().DIRECTORY_SEPARATOR.'widgets/'.$viewFile.'.php';
//             if(!file_exists($runfile) || filemtime($runfile)<filemtime($tplfile)){
//                 $file = new File($runfile,'w+');
//                 $template = $file->getContents($tplfile);
//                 $t = new Tag();
//                 $tem = $t->resolve($template);
//                 $file->write($tem);
//             }
//             header("Content-type: text/html; charset=".$this->encoding);
//             if($return){
//                 ob_start();
//                 ob_implicit_flush(false);
//                 require($runfile);
//                 return ob_get_clean();
//             }
//             else
//                 require($runfile);
//         }
//         else
//         {
//             /**
//             if($this->id != Tiny::app()->getId()){
//                  trigger_error("{$this->id}Widget->{$this->getAction()}() not exists",E_USER_ERROR);
//             }
//             **/
//             //Tiny::msg('','无法找到请求的页面',404);
//             return ;
//         }
//     }
//     /**
//      * 创建插件
//      *
//      * @access public
//      * @param mixed $className
//      * @return mixed
//      */
//     public static function createWidget($className)
//     {

//         $classWidget = ucfirst($className);
//         $classWidget .='Widget';
//         if(class_exists($classWidget)) return new $classWidget($className);
//         else return new Widget(strtolower($className));
//     }

//     /**
//      * 共享数据的赋值，提供于Widget视图使用
//      *
//      * @access public
//      * @param mixed $name
//      * @param mixed $value
//      */
//     public function assign($name,$value=null)
//     {
//         if(is_string($name))$this->properties[$name]=$value;
//         else if(is_array($name)){
//             $this->properties += $name;
//         }
//     }
// }





/////
///
///
///

class Widget extends Controller
{
    public $encoding='UTF-8';
    public $cache;
    public $cacheTime;
    public $_action = null;
    public $id;
    //布局文件
    protected $layout = '';


    public function __construct($id="",$module=null)
    {
        $this->id=$id;
        $this->module=$module;
        //$this->init(); 原来位置 暂时先调到run中
    }

    /**
     * 控制器运行时的方法
     *
     * @access public
     * @return mixed
     */
    public function run()
    {
        if($this->isUseController() && !$this->getStatus()){
            Tiny::Msg($this,'404');
        }

        if(Tiny::app()->checkToken('redirect')){
            $data = Req::args();
            unset($data['con'],$data['act'],$data['tiny_token_redirect']);
            $this->setDatas($data);
        }
        $this->init();
        $id = Req::args('act');
        if($id ===null){
            $id = $this->defaultAction;
            Req::args('act',$id);
        }else if(!Validator::nameExt($id,1)){
            Tiny::Msg($this,"error");
        }

        //防止页面的循环调用
        //if(!$this->module->popRequestStack($this->id.'@'.$id))$this->module->pushRequestStack($this->id.'@'.$id);
        //else if($this->module->popRequestStack($this->id.'@'.$id)) {throw new Exception("Can't repeat redirect to the same position, you action is {$this->id}.",E_USER_ERROR);}
        $this->action = $this->createAction($id);

        //所有Controller处理的扩展处理
        $contExtensions = ExtensionFactory::getFactory('controllerExtension');
        if($contExtensions !== null )
        {
            $contExtensions->before($this);
            if(!is_null($this->action))$this->action->run();
            $contExtensions->after($this);
        }
        else if(!is_null($this->action))$this->action->run();


    }

    public function isUseController()
    {
        if($this->_action == null){
            return true;
        }else{
            return false;
        }
    }


    public  function getStatus(){
        $flag = true;
        $fileName = APP_CODE_ROOT.'widgets/'.strtolower($this->id).'/status.php';
        $theme = Tiny::app()->getTheme();
        if($theme!==null){
            $temfile = $theme->getBasePath().DIRECTORY_SEPARATOR.'widgets/'.strtolower($this->id).'/status.php';
            if(is_file($temfile))$fileName = $temfile;
        }
        if(is_file($fileName)){
            $flag = include($fileName);
        }
        return $flag===true;
    }

        /**
     * 取得布局文件
     *
     * @access public
     * @return mixed
     */
    public function getLayoutFile()
    {
        if($this->_action == null){
            return parent::getLayoutFile();
        }else{
            return "";
        }

    }

    public function getViewName($viewName){
        if($this->_action == null){
            return $viewName;
        }
        if($this->_action != $this->action->getId()){
            return $this->_action;
        }else{
            return $viewName;
        }
    }

    public function getViewFile($viewName)
    {
        if(($theme = Tiny::app()->getTheme())!==null){
            $viewFile = $theme->getBasePath().DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.strtolower($this->getId()).'/'.$this->getViewName($viewName).'.html';
            if(file_exists($viewFile))return $viewFile;
        }
        return APP_CODE_ROOT.'widgets/'.strtolower($this->getId()).'/'.$this->getViewName($viewName).'.html';
    }

    public function getRunFile($view){
        return Tiny::app()->getRuntimePath().DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.strtolower($this->getId()).DIRECTORY_SEPARATOR.$this->getViewName($view).$this->scriptExt;
    }

    public static function createWidget($className)
    {
        //通过标签调用
        $classWidget = ucfirst($className);
        $classWidget .='Widget';
        if(class_exists($classWidget)) return new $classWidget($className,Tiny::app());
        else return new Widget(strtolower($className),Tiny::app());
    }


    /**
     * 渲染文件视图
     *
     * @access public
     * @param mixed $view
     * @param mixed $data
     * @param bool $return
     * @return mixed
     */
    public function render($view,$data=null,$return=false)
    {
        //if(preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $view)==0)Tiny::Msg($this,'请求的页面不存在！',404);
        $viewfile = $this->getViewFile($view);
        $runfile= $this->getRunFile($view);
        //if(file_exists($viewfile))
        if(is_file($viewfile))
        {

            $layoutfile=$this->getLayoutFile().$this->templateExt;

            if(file_exists($layoutfile)) $layoutexists=true;
            else  $layoutexists=false;

            if(!file_exists($runfile) || filemtime($runfile)<filemtime($viewfile) || ($layoutexists && filemtime($runfile)<filemtime($layoutfile)))
            {
                $file = new File($runfile,'w+');
                $template = $file->getContents($viewfile);
                $t = new Tag();
                $tem = $t->resolve($template,dirname($view));
                if($layoutexists)
                {
                    $theme_str = $t->resolve(file_get_contents($layoutfile));
                    $tem = str_replace("{__viewcontents}",$tem,$theme_str);
                }
                $tem.= "\n ".Crypt::decode('cc7ff6936aAFYFBlJRVlRSUwwHV1JSXQ9bDFMEBwUHVlcCVgJTVV0LFRREYlpBUkFTBxgATRk3WVpOZFBBURRJXw');
                $file->write($tem);
                unset($file);
            }
            echo $this->renderExecute($runfile,$data);
        }
        else
        {
            if($this->_action!=null){
                return ;
            }else{
                Tiny::Msg($this,'404');
            }

        }
    }



    public function renderExecute($__runfile01234567890,$__data01234567890)
    {
            $__datas01234567890 = array();
            if(is_array($__data01234567890)) $__datas01234567890 += $__data01234567890;
            if(is_array($this->datas)) $__datas01234567890 += $this->datas;
            $__datas01234567890 += Tiny::app()->getDatas()!==null?Tiny::app()->getDatas():array();

            if($__datas01234567890!==null)
            {
                extract($__datas01234567890,EXTR_SKIP);
                unset($__datas01234567890,$__data01234567890);//防止干扰视图里的变量内容,同时防止无端过滤掉用户定义的变量（除非用户定义__data01234567890的变量）
            }
            header("Content-type: text/html; charset=".$this->encoding);
            ob_start();
            if($this->_action!=null){
                ob_implicit_flush(false);
                include($__runfile01234567890);
                $template_content = ob_get_clean();
            }else{
                include($__runfile01234567890);
                $template_content = ob_get_clean();
                if(ob_get_length()>0)ob_end_clean();

            }

            //if(ob_get_length()>0)ob_end_clean();
            if($this->lazyLoadVar)
            {
                $keys = array_keys($this->datas);
                foreach ($keys as $key=>$value)
                {
                    $keys[$key]='{'.$value.'}';
                }
                return str_ireplace($keys, array_values($this->datas), $template_content);
            }
            else
            {
                return $template_content;
            }
    }


}
