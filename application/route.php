<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[wechat]'     => [
        ''   => ['pay/auth/wechat', ['method' => 'get']]
    ],
    '[rooms]'     => [
        ''   => ['index/index/rooms', ['method' => 'get']]
    ],
    '[room]'     => [
        ''   => ['index/index/room', ['method' => 'get']]
    ],
    '[order]'     => [
        ''   => ['index/index/order', ['method' => 'get']]
    ],
    '[prebook]'     => [
        ''   => ['index/index/prebook', ['method' => 'get']]
    ],
    '[book]'     => [
        ''   => ['index/index/book', ['method' => 'post']]
    ],
    '[mppay]'     => [
        ''   => ['pay/index/mppay', ['method' => 'get']]
    ],
    '[notify]'     => [
        ''   => ['pay/index/notify', ['method' => 'get|post|put']]
    ],

];
