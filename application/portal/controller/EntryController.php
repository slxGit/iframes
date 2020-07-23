<?php
namespace app\portal\controller;

use think\Controller;

class EntryController extends Controller
{   
    public function index()
    {
    	return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> KirinBDF<br/><span style="font-size:30px">专注 · 极致 · 开放</span></p></div><div class="think_default_text"><a href="/cms/category/index" title="CMS模块" target="_blank">CMS模块</a><span style=" padding-left: 10px;"></span><a href="/portal/entry/form" title="表单构建器" target="_blank">表单构建器</a><span style=" padding-left: 10px;"></span><a href="/portal/entry/table" title="表格构建器" target="_blank">表格构建器</a></div>';
    }

    // 表单构建器
    public function form()
    {
        if (request()->isPost()) {
            $params = $this->request->param();
            dump($params);die;
            sleep(1);
            $this->success('提交成功');
        } else {
            return \app\common\builder\Builder::form()
                // 设置表单标题
                ->setPageTitle('测试表单')
                // 添加表单项
                ->addFormItems([
                    ['group', [
                        '文本框' => [
                            ['hidden', 'hidden', '隐藏文本'],
                            ['static', 'static', '静态文本'],
                            ['text', 'text', '单行文本'],
                            ['password', 'password', '密码框'],
                            ['number', 'number', '数字框'],
                            ['textarea', 'textarea', '文本域']
                        ],
                        '下拉框' => [
                            ['select', 'select', '下拉菜单', '', [1 => '北京', 2 => '上海', 3 => '杭州']],
                            ['linkages', 'linkages', '快速联动', '', 'sys_region'],
                            ['linkage', 'province', '选择省份', '', ['js' => '江苏', 'sd' => '山东'], '', '6', url('getcity'), 'city'],
                            ['select', 'city', '选择城市'],
                            ['xmselect', 'xmselect', '多选下拉', '', [1 => '游泳', 2 => '唱歌', 3 => '跳舞']]
                        ],
                        '单/复选框' => [
                            ['switch', 'switch', '开关'],
                            ['checkbox', 'checkbox', '复选框', '', [1 => '游泳', 2 => '唱歌', 3 => '跳舞']],
                            ['radio', 'radio', '单选按钮', '', [1 => '显示姓名', 2 => '显示年龄', 3 => '显示职业']],
                            ['static', 'name', '姓名', '', 'Aaron'],
                            ['static', 'age', '年龄', '', '25'],
                            ['static', 'job', '职业', '', 'IT']
                        ],
                        '日期' => [
                            ['date', 'date', '日期'],
                            ['date', 'date_month', '月份', '', '', '', ['type' => 'month']],
                            ['date', 'date_year', '年份', '', '', '', ['type' => 'year']],
                            ['date', 'date_range', '日期范围', '', '', '', ['range' => true]]
                        ],
                        '图片/文件' => [
                            ['image', 'image', '单张图片'],
                            ['images', 'images', '多张图片'],
                            ['file', 'file1', '单个文件'],
                            ['files', 'files', '多个文件']
                        ],
                        '表格' => [
                            ['table', 'table', '展示表格', '', [
                                'name' => '姓名',
                                'age' => '年龄',
                                'sex' => '性别'
                            ], [
                                ['name' => '张三', 'age' => 25, 'sex' => '男'],
                                ['name' => '李四', 'age' => 20, 'sex' => '女']
                            ]],
                            ['datatable', 'datatable', '数据表格', '', [
                                'table_province' => [
                                    'type' => 'select', 'title' => '省份', 'options' => [
                                        'js' => '江苏省', 
                                        'sd' => '山东省'
                                    ]
                                ],
                                'table_inc' => '增量'
                            ]]
                        ], 
                        '编辑器' => [
                            ['layedit', 'layedit', 'Layui编辑器'],
                            ['ueditor', 'ueditor', '百度编辑器']
                        ],
                        '地图' => [
                            ['bmap', 'bmap', '百度地图']
                        ],
                        '其他' => [
                            ['button', 'button', '按钮'],
                            ['icon', 'icon', '图标选择器'],
                            ['colorpicker', 'color', '颜色选择器'],
                            ['rate', 'rate', '评分'],
                            ['range', 'range', '范围'],
                            ['sort', 'sort', '排序', '', [
                                'zs' => '张三', 
                                'ls' => '李四', 
                                'ww' => '王五'
                            ]],
                            ['transfer', 'transfer', '穿梭框', '', [
                                    'zs' => '张三', 
                                    'ls' => '李四', 
                                    'ww' => '王五'
                                ], '', '', ['title' => ['候选名单', '获奖名单']]
                            ]
                        ]
                    ]]
                ])
                // radio的触发器
                ->setTriggers([
                    ['radio', 1, 'name'],
                    ['radio', 2, 'age'],
                    ['radio', 3, 'job']
                ])
                // 提交确认按钮
                ->submitConfirm()
                ->fetch();
            }
    }

    // 表格构建器
    public function table()
    {           
        return \app\common\builder\Builder::table()
            // 添加列
            ->addColumns([
                ['index', '序列', 'index', ['sort' => true, 'width' => 100, 'totalRowText' => '合计：']],
                ['name', '用户名', 'text', '', ['width' => 100]],
                ['link_open', '链接', 'link', '', '', ['width' => 150]],
                ['link_href', '链接', 'link', '', 'href', ['width' => 150]],
                ['avatar', '头像', 'image', '', 2, '', ['width' => 100]],
                ['file', '下载', 'download', '', ['width' => 100]],
                ['birth', '生日', 'date', '', '', ['width' => 150]],
                ['lock', '锁定', 'checkbox', '', ['width' => 150]],
                ['status', '状态', 'switch', '', ['width' => 150]],
                ['job', '职业', 'tag', '', '', ['width' => 150]],
                ['marry', '婚姻', 'select', [1 => '未婚', 2 => '已婚', 3 => '离婚'], '', ['width' => 150]],
                ['hobby', '爱好', 'xmselect', [1 => '篮球', 2 => '游泳', 3 => '唱歌', 4 => '跳舞'], [], [], ['width' => 250]],
                ['score', '得分', '', '59', ['width' => 100, 'totalRow' => true]],
                ['assess', '考核', 'judge', '', ['width' => 100]]
            ])
            // 设置表格数据
            ->setTableData([
                [
                    'id'        => 1001, 
                    'name'      => '刘一', 
                    'link_open' => 'https://www.baidu.com, 打开新页面',
                    'link_href' => ['https://www.baidu.com', '跳转至页面'],
                    'avatar'    => 'http://pic1.win4000.com/wallpaper/2020-02-28/5e58877fdb777.jpg',
                    'file'      => ['/static/admin/img/download.png', '测试文件'],
                    'birth'     => '1995-03-22',
                    'lock'      => true,
                    'status'    => true,
                    'job'       => ['作家', 'IT'],
                    'marry'     => 2, 
                    'hobby'     => '1,2',
                    'score'     => 90,
                    'assess'    => 1
                ],
                [
                    'id'     => 1002, 
                    'name'   => '陈二',
                ],
                [
                    'id'     => 1003, 
                    'name'   => '张二',
                ],          
                [
                    'id'     => 1004, 
                    'name'   => '李四',
                ],
                [
                    'id'     => 1005, 
                    'name'   => '王五'
                ],
                [
                    'id'     => 1006, 
                    'name'   => '赵六'
                ],
                [
                    'id'     => 1007, 
                    'name'   => '孙七'
                ],
                [
                    'id'     => 1008, 
                    'name'   => '周八'
                ],
                [
                    'id'     => 1009, 
                    'name'   => '吴九'
                ],                
            ])
            // 添加搜索项
            ->addSearchItems([
                ['text', 'text', '文本', 2],
                ['select', 'select', '单选下拉', [1 => '刘一', 2 => '陈二', 3 => '张三']],
                ['xmselect', 'select', '多选下拉', [1 => '刘一', 2 => '陈二', 3 => '张三']],
                ['date', 'date', '日期']
            ])
            // 添加工具按钮
            ->addToolButtons('filter,print,exports')
            // 添加顶部按钮
            ->addTopButtons('add,enable,disable,delete')
            ->addTopButton('customize', [
                'title' => '外链',
                'method' => 'open',
                'url' => 'https://www.baidu.com',
            ])
            // 添加右侧按钮
            ->addRightButtons('edit,delete')
            // 替换按钮
            ->replaceRightButton(['id' => [1001]], ['edit'])
            // 设置右侧操作栏
            ->setRightToolbar(['width' => '15%'])
            // 设置表格配置
            ->setTableConfigs(['totalRow' => true])
            ->fetch();
    }


    // 获取城市列表
    public function getcity()
    {
        $province = $this->request->param('province');

        switch ($province) {
            case 'js':
                return json([
                    'code' => 1,
                    'msg' => '请求成功',
                    'data' => [
                        'xz' => '徐州',
                        'nj' => '南京',
                        'wx' => '无锡',
                        'sz' => '苏州'
                    ]
                ]);
                break;
            
            case 'sd':
                return json([
                    'code' => 1,
                    'msg'  => '请求成功',
                    'data' => [
                        'jn' => '济南',
                        'wf' => '潍坊',
                        'dz' => '德州',
                        'qd' => '青岛'
                    ]
                ]);                
                break;
        }
    }

    // 快捷编辑
    public function quickEdit()
    {
        $this->success('保存成功');
    }

    public function delete()
    {
        $this->success('删除成功');
    }

    public function enable()
    {
        $this->success('启用成功');
    }

    public function disable()
    {
        $this->success('禁用成功');
    }
}
