<?php
/**
 * Tiny - A PHP Framework For Web Artisans
 * @author Tiny <tinylofty@gmail.com>
 * @copyright Copyright(c) 2010-2014 http://www.tinyrise.com All rights reserved
 * @version 1.0
 */

//系统主要接口集
//Application接口
interface Application
{
	public function run();
}
//扩展接口
interface Extension
{
	public function before($obj=null);
	public function after($obj=null);
}

//事件接口标准协议
interface EventInterface
{
    public function onAction($data);
}
