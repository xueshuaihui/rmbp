headerNavs = [
    {
        'nav': '后台首页',
        'sub_nav': [
            {
                'title': '<i class="icon-home"></i>后台首页',
                'menu': [
                    {
                        'menu_title': '登录概览',
                        'menu_link': DIR + '/index/welcome/'
                    }
                ]
            }
        ]
    },
    {
        'nav': '项目管理',
        'sub_nav': [
            {
                'title': '<i class="icon-truck"></i>项目管理',
                'menu': [
                    {
                        'menu_title': '所有项目',
                        'menu_link': DIR + '/project/index/'
                    },
                    {
                        'menu_title': '待审核项目',
                        'menu_link': DIR + '/project/index/verify/1/'
                    },
                    {
                        'menu_title': '已通过项目',
                        'menu_link': DIR + '/project/index/verify/2/'
                    },
                    {
                        'menu_title': '已完成项目',
                        'menu_link': DIR + '/project/index/verify/3/'
                    },
                    {
                        'menu_title': '未通过项目',
                        'menu_link': DIR + '/project/index/verify/4/'
                    },
                    {
                        'menu_title': '下线项目',
                        'menu_link': DIR + '/project/index/verify/-2/'
                    },
                    {
                        'menu_title': '编辑中项目',
                        'menu_link': DIR + '/project/index/verify/5/'
                    },
                    {
                        'menu_title': '心元项目',
                        'menu_link': DIR + '/project/index/verify/-3/'
                    },
                    {
                        'menu_title': '心元候选项目',
                        'menu_link': DIR + '/project/index/verify/-4/'
                    }
                ]
            },
            {
                'title': '<i class="icon-reorder"></i>约见管理',
                'menu': [
                    {
                        'menu_title': '约见列表',
                        'menu_link': DIR + '/project/meet/'
                    }
                ]
            }
        ]
    },
    {
        'nav': '订单管理',
        'sub_nav': [
            {
                'title': '<i class="icon-reorder"></i>订单管理',
                'menu': [
                    {
                        'menu_title': '所有订单',
                        'menu_link': DIR + '/order/index/'
                    },
                    {
                        'menu_title': '支付成功订单',
                        'menu_link': DIR + '/order/index/search[ispaied]/1/search[isrefund]/0/'
                    },
                    {
                        'menu_title': '支付中订单',
                        'menu_link': DIR + '/order/index/search[ispaied]/0/search[iscancel]/0/search[isrefund]/0/'
                    },
                    {
                        'menu_title': '撤资中订单',
                        'menu_link': DIR + '/order/index/search[isrefund]/1/'
                    },
                    {
                        'menu_title': '已撤资订单',
                        'menu_link': DIR + '/order/index/search[isrefund]/2/'
                    },
                    {
                        'menu_title': '取消订单',
                        'menu_link': DIR + '/order/index/search[iscancel]/1/'
                    }
                ]
            }
        ]
    },
    {
        'nav': '交易管理',
        'sub_nav': [
            {
                'title': '<i class="icon-reorder"></i>交易项目',
                'menu': [
                    {
                        'menu_title': '交易项目列表',
                        'menu_link': DIR + '/trade/index/'
                    }
                ]
            },
            {
                'title': '<i class="icon-reorder"></i>交易列表',
                'menu': [
                    {
                        'menu_title': '所有转让',
                        'menu_link': DIR + '/trade/sell/'
                    },
                    {
                        'menu_title': '待审核转让',
                        'menu_link': DIR + '/trade/sell/status/0/'
                    },
                    {
                        'menu_title': '已通过转让',
                        'menu_link': DIR + '/trade/sell/status/1/'
                    },
                    {
                        'menu_title': '交易中转让',
                        'menu_link': DIR + '/trade/sell/status/2/'
                    },
                    {
                        'menu_title': '未通过转让',
                        'menu_link': DIR + '/trade/sell/status/-1/'
                    },
                    {
                        'menu_title': '待审核购买',
                        'menu_link': DIR + '/trade/buy/status/0/'
                    },
                    {
                        'menu_title': '未通过购买',
                        'menu_link': DIR + '/trade/buy/status/-2/'
                    },
                    {
                        'menu_title': '进行中交易',
                        'menu_link': DIR + '/trade/buy/status/1/'
                    },
                    {
                        'menu_title': '未成功交易',
                        'menu_link': DIR + '/trade/buy/status/-1/'
                    },
                    {
                        'menu_title': '已完成交易',
                        'menu_link': DIR + '/trade/buy/status/2/'
                    }
                ]
            }
        ]
    },
    {
        'nav': '用户管理',
        'sub_nav': [
            {
                'title': '<i class="icon-group"></i>投资人管理',
                'menu': [
                    {
                        'menu_title': '待审核投资人',
                        'menu_link': DIR + '/user/index/isauth/1/'
                    },
                    {
                        'menu_title': '已通过投资人',
                        'menu_link': DIR + '/user/index/isauth/2/'
                    },
                    {
                        'menu_title': '未通过投资人',
                        'menu_link': DIR + '/user/index/isauth/-1/'
                    }
                ]
            },
            {
                'title': '<i class="icon-user"></i>用户管理',
                'menu': [
                    {
                        'menu_title': '所有用户管理',
                        'menu_link': DIR + '/user/index/isauth/99/'
                    },
                    {
                        'menu_title': '普通用户管理',
                        'menu_link': DIR + '/user/index/isauth/0/'
                    },
                    {
                        'menu_title': '心元用户管理',
                        'menu_link': DIR + '/user/index/isauth/100/'
                    },
                    {
                        'menu_title': '管理员用户',
                        'menu_link': DIR + '/user/index/isauth/999/'
                    }
                ]
            }
        ]
    },
    /*{
     },*/
    {
        'nav': '通知管理',
        'sub_nav': [
            {
                'title': '<i class="icon-edit"></i>站内消息',
                'menu': [
                    {
                        'menu_title': '消息列表',
                        'menu_link': DIR + '/message/index/'
                    },
                    {
                        'menu_title': '发送消息',
                        'menu_link': DIR + '/message/send_list/sendtype/message/'
                    }
                ]
            },
            {
                'title': '<i class="icon-envelope"></i>邮件消息',
                'menu': [
                    {
                        'menu_title': '邮件列表',
                        'menu_link': DIR + '/message/send_list/sendtype/email/'
                    },
                    {
                        'menu_title': '发送队列',
                        'menu_link': DIR + '/message/send_queue/sendtype/email/'
                    }
                ]
            },
            {
                'title': '<i class="icon-envelope-alt"></i>短信消息',
                'menu': [
                    {
                        'menu_title': '短信列表',
                        'menu_link': DIR + '/message/send_list/sendtype/phone/'
                    },
                    {
                        'menu_title': '发送队列',
                        'menu_link': DIR + '/message/send_queue/sendtype/phone/'
                    }
                ]
            }
        ]
    },
    {
        'nav': '网站运营',
        'sub_nav': [
            {
                'title': '<i class="icon-edit"></i>首页设置',
                'menu': [
                    {
                        'menu_title': '首页设置',
                        'menu_link': DIR + '/var/banner/'
                    },
                    {
                        'menu_title': '网站设置',
                        'menu_link': DIR + '/var/index/'
                    }
                ]
            },
            {
                'title': '<i class="icon-envelope"></i>文章管理',
                'menu': [
                    {
                        'menu_title': '文章分类',
                        'menu_link': DIR + '/article/category/type/news/'
                    },
                    {
                        'menu_title': '标签分类',
                        'menu_link': DIR + '/article/category/type/label/'
                    },
                    {
                        'menu_title': '文章列表',
                        'menu_link': DIR + '/article/list/type/news/'
                    },

                ]
            },
            {
                'title': '<i class="icon-envelope-alt"></i>帮助中心',
                'menu': [
                    {
                        'menu_title': '帮助分类',
                        'menu_link': DIR + '/article/category/type/help/'
                    },
                    {
                        'menu_title': '帮助列表',
                        'menu_link': DIR + '/article/list/type/help/'
                    }
                ]
            },
            {
                'title': '<i class="icon-envelope-alt"></i>日志管理',
                'menu': [
                    {
                        'menu_title': '日志列表',
                        'menu_link': DIR + '/log/index/'
                    }
                ]
            }
        ]
    }
];


