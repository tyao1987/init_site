<?php
return array(
        'default' => array(
            array(
                'label' => '用户/权限',
                'module' => 'mod_acl',
                'route' => 'default',
                'controller' => 'acl',
                'pages' => array(
                    array(
                        'label' => 'Module',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'module-list',
                        'resource' => 'mod_acl_module-list',
                        'pages' => array(
                            array(
                                'label' => 'Add Module',
                                'module' => 'mod_acl',
                                'route' => 'default',
                                'controller' => 'acl',
                                'action'     => 'module-edit',
                                'resource' => 'mod_acl_module-edit',
                            ),
                        )
                    ),
                    array(
                        'label' => 'Controller',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'controller-list',
                        'resource' => 'mod_acl_controller-list',
                        'pages' => array(
                            array(
                                'label' => 'Add Controller',
                                'controller' => 'acl',
                                'action'     => 'controller-edit',
                                'resource' => 'mod_acl_controller-edit',
                            ),
                        )
                    ),
                    array(
                        'label' => 'Action',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'action-list',
                        'resource' => 'mod_acl_action-list',
                        'pages' => array(
                            array(
                                'label' => 'Add Action',
                                'controller' => 'acl',
                                'action'     => 'action-edit',
                                'resource' => 'mod_acl_action-edit',
                            ),
                        )
                    ),
                    array(
                        'label' => '用户',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'user-list',
                        'resource' => 'mod_acl_user-list',
                    ),
                    array(
                        'label' => '角色/权限',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'role-list',
                        'resource' => 'mod_acl_role-list',
                    ),
                ),	
            ),
            
            array(
                'label' => '日志',
                'route' => 'default',
                'module' => 'mod_log',
                'controller' => 'log',
                'action' => 'index',
                'resource' => 'mod_log_index',
                'link' => '/log/index',
            ),
            array(
                'label' => '新闻管理',
                'module' => 'mod_news',
                'route' => 'default',
                'controller' => 'news',
                'action' => 'list',
                'pages' => array(
                    array(
                        'label' => '列表',
                        'route' => 'default',
                        'controller' => 'news',
                        'action' => 'list',
                        'resource' => 'mod_news_news_list',
                        'link' => '/news/list',
                    ),
                    array(
                        'label' => '添加',
                        'controller' => 'news',
                        'action'     => 'manage',
                        'resource' => 'mod_news_news_manage',
                        'link' => '/news/manage',
                    ),
                )
            ),
            
            array(
                'label' => '招聘管理',
                'route' => 'default',
                'module' => 'mod_job',
                'controller' => 'job',
                'action' => 'list',
                'pages' => array(
                    array(
                        'label' => '列表',
                        'route' => 'default',
                        'controller' => 'job',
                        'action' => 'list',
                        'resource' => 'mod_job_job_list',
                        'link' => '/job/list',
                    ),
                    array(
                        'label' => '添加',
                        'controller' => 'job',
                        'action'     => 'manage',
                        'resource' => 'mod_job_job_manage',
                        'link' => '/job/manage',
                        
                    ),
                )
            ),
            
            array(
                'label' => '上传图片',
                'route' => 'default',
                'module' => 'mod_uploadimage',
                'controller' => 'uploadimage',
                'action' => 'index',
                'resource' => 'mod_uploadimage_index',
                'link' => '/uploadimage/index?basedir=upload&dirend=&Continue=Continue',
            ),
            
            array(
                'label' => '修改密码',
                'route' => 'default',
                'module' => 'admin',
                'controller' => 'index',
                'action' => 'updateMyPassword',
                'resource' => 'admin_index_update-my-password',
            ),
        ),
);