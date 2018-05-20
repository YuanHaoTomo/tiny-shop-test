<?php
return array(
    'name'    => '三级分销系统',
    'author'  => 'TinyRise官方',
    'version' => '1.0',
    'events'  => array(
        'reguser',
        'login',
        'payorder',
    ),
    'menu'    => array(
        array('name' => '分销管理', 'action' => 'index'),
        array('name' => '分销商列表', 'action' => 'user_list'),
    ),
    'ucenter_menu' => array('name'=>'分销中心','action'=>'info'),
    'sqls'    => array(
        'install'=>array(
            "CREATE TABLE `tiny_widget_distributor_settlement_log` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `user_id` bigint(20) NOT NULL, `amount` double NOT NULL, `op_time` datetime NOT NULL, PRIMARY KEY (`id`) ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",
        "CREATE TABLE `tiny_widget_distributor_promotion` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `goods_id` bigint(20) NOT NULL, `user_id` int(11) NOT NULL, `sort` int(11) DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",
        "CREATE TABLE `tiny_widget_distributor_order` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `user_id` bigint(20) NOT NULL, `order_id` int(11) NOT NULL, `commission` double NOT NULL DEFAULT '0', `status` int(11) NOT NULL, `create_time` datetime NOT NULL, `settlement_id` bigint(20) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8; ",
        "CREATE TABLE `tiny_widget_distributor` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `user_id` bigint(20) NOT NULL, `parent_user_id` bigint(20) NOT NULL, `parent_id_path` varchar(255) NOT NULL, `code` varchar(16) NOT NULL, `category` int(11) NOT NULL DEFAULT '0', `status` int(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
        ),
        'uninstall'=>array(
            "DROP TABLE IF EXISTS `tiny_widget_distributor_settlement_log`;",
            "DROP TABLE IF EXISTS `tiny_widget_distributor_promotion`;",
            "DROP TABLE IF EXISTS `tiny_widget_distributor_order`;",
            "DROP TABLE IF EXISTS `tiny_widget_distributor`;"
        )
    ),
);
