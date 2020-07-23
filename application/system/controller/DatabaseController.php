<?php

/**
 * KirinBDF
 *
 * A PHP Basic Development Framework, Based on ThinkPHP 5.1 LTS.
 *
 * @System:     KirinBDF
 * @Link:       http://www.kirinbdf.com/
 * @Copyright:  Copyright (c) 2017-2019  KirinBDF Team.
 *
 * @Author:     Aaron
 * @Date:       2020-02-10 13:02:08
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-23 10:27:11
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use think\Db;
use utils\Database;
use app\common\builder\Builder;
use app\common\controller\AdminBaseController;

class DatabaseController extends AdminBaseController
{
	/**
     * 数据库备份/还原列表
     */
    public function index($group = 'export')
    {
        $listTab = [
            'export' => ['title' => '备份', 'url' => url('index', ['group' => 'export'])],
            'import' => ['title' => '还原', 'url' => url('index', ['group' => 'import'])]
        ];

        $builder = Builder::table()->setTabNav($listTab, $group);

        switch ($group) {
            case 'export':
                $list  = Db::query('SHOW TABLE STATUS');
                $list  = array_map('array_change_key_case', $list);
                $list = $this->transByte($list, 'data_length');
                $builder = $builder->addColumns([
                    ['name', '表名'],
                    ['rows', '数据量'],
                    ['data_length', '数据大小'],
                    ['create_time', '创建时间']
                ])
                    ->setTableData($list)
                    ->addTopButton('export', [
                        'title'      => '立即备份',
                        'method'     => 'ajax',
                        'url'        => url('export'), 
                        'param'      => [
                            'tables' => '__name__'
                        ],
                        'confirm'    => true
                    ])
                    ->addTopButton('optimize', [
                        'title'    => '优化表',
                        'method'   => 'ajax',
                        'url'      => url('optimize'), 
                        'param'    => [
                            'name' => '__name__'
                        ],
                        'class'    => 'layui-btn layui-bg-orange layui-btn-sm'
                    ])
                    ->addTopButton('repair', [
                        'title'    => '修复表',
                        'method'   => 'ajax',
                        'url'      => url('repair'), 
                        'param'    => [
                            'name' => '__name__'
                        ],
                        'class'    => 'layui-btn layui-btn-normal layui-btn-sm'
                    ])
                    ->addRightButton('optimize', [
                        'title'    => '优化表',
                        'method'   => 'ajax',
                        'url'      => url('optimize'), 
                        'param'    => [
                            'name' => '__name__'
                        ],
                        'class'    => 'layui-btn layui-bg-orange layui-btn-xs'                        
                    ])
                    ->addRightButton('repair', [
                        'title'    => '修复表',
                        'method'   => 'ajax',
                        'url'      => url('repair'), 
                        'param'    => [
                            'name' => '__name__'
                        ],
                        'class'    => 'layui-btn layui-btn-normal layui-btn-xs'
                    ]);
                break;

            case 'import':
                // 获取备份文件列表
                $path = config('data_backup_path');
                if(!is_dir($path)){
                    mkdir($path, 0755, true);
                }
                $path = realpath($path);
                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($path, $flag);
                $list = array();
                foreach ($glob as $name => $file) {
                    if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];

                        if(isset($list["{$date} {$time}"])){
                            $info = $list["{$date} {$time}"];
                            $info['part'] = max($info['part'], $part);
                            $info['size'] = $info['size'] + $file->getSize();
                        } else {
                            $info['part'] = $part;
                            $info['size'] = $file->getSize();
                        }
                        $extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                        $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                        $info['time']     = strtotime("{$date} {$time}");
                        $info['name']     = date('Ymd-His', strtotime("{$date} {$time}"));
                        $info['export_time'] = date('Y-m-d H:i:s', strtotime("{$date} {$time}"));
                        $list["{$date} {$time}"] = $info;
                    }
                }
                $list = $this->transByte($list, 'size');
                $builder = $builder->addColumns([
                    ['name', '备份名称'],
                    ['part', '卷数'],
                    ['compress', '压缩'],
                    ['size', '数据大小'],
                    ['export_time', '备份时间']
                ])
                    ->addRightButton('import', [
                        'title'    => '还原',
                        'method'   => 'ajax',
                        'url'      => url('import'), 
                        'param'    => [
                            'time' => '__time__'
                        ],
                        'confirm'  => true
                    ])
                    ->addRightButton('delete', [
                        'param'    => [
                            'time' => '__time__'
                        ]
                    ])
                    ->setTableData($list);
                break;
        }

        return $builder->fetch();
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        $tables = $this->request->param('name');

        if(is_array($tables)){
            $tables = implode('`,`', $tables);
            $list = Db::query("OPTIMIZE TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表优化完成！");
            } else {
                $this->error("数据表优化出错请重试！");
            }
        } else {
            $list = Db::query("OPTIMIZE TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表 {$tables} 优化完成！");
            } else {
                $this->error("数据表 {$tables} 优化出错请重试！");
            }
        }
    }

    /**
     * 修复表
     */
    public function repair()
    {
        $tables = $this->request->param('name');

        if(is_array($tables)){
            $tables = implode('`,`', $tables);
            $list = Db::query("REPAIR TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表修复完成！");
            } else {
                $this->error("数据表修复出错请重试！");
            }
        } else {
            $list = Db::query("REPAIR TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表 {$tables} 修复完成！");
            } else {
                $this->error("数据表 {$tables} 修复出错请重试！");
            }
        }
    }

    /**
     * 删除备份文件
     * @param  Integer $time 备份时间
     */
    public function delete($time = 0)
    {
        if($time){
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(config('data_backup_path')) . DIRECTORY_SEPARATOR . $name;
            array_map("unlink", glob($path));
            if(count(glob($path))){
                $this->error('删除失败，请检查权限！');
            } else {
                $this->success('删除成功！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

    /**
     * 备份数据库
     */
    public function export()
    {
        // 备份表名
        $tables = $this->request->param('tables');
        
        if (empty($tables) || !is_array($tables)) {
            $this->error('参数错误');
        }

        $path = config('data_backup_path');

        if(!is_dir($path)){
            mkdir($path, 0755, true);
        }

        // 读取备份配置
        $config = [
            'path'     => realpath($path) . DIRECTORY_SEPARATOR,
            'part'     => config('data_backup_part_size'),
            'compress' => config('data_backup_compress'),
            'level'    => config('data_backup_compress_level'),
        ];

        // 检查是否有正在执行的任务
        $lock = "{$config['path']}backup.lock";
        if(is_file($lock)){
            $this->error('检测到有一个备份任务正在执行，请稍后再试！');
        } else {
            // 创建锁文件
            file_put_contents($lock, $this->request->time());
        }

        // 检查备份目录是否可写
        is_writeable($config['path']) || $this->error('备份目录不存在或不可写，请检查后重试！');

        // 生成备份文件信息
        $file = [
            'name' => date('Ymd-His', $this->request->time()),
            'part' => 1
        ];

        // 创建备份文件
        $database = new Database($file, $config);

        if ($database->create() !== false) {
            // 备份表
            foreach ($tables as $table) {
                $start = $database->backup($table, 0);
                while ($start !== 0) {
                    if ($start === false) {
                        $this->error('备份出错！');
                    }
                    $start = $database->backup($table, $start[0]);
                }
            }
            //备份完成
            unlink($lock);
            $this->success('备份完成！');
        } else {
            $this->error('初始化失败，备份文件创建失败！');
        }
    }

    /**
     * 还原数据库
     */
    public function import()
    {               
        $time = $this->request->post('time');

        if (!$time) $this->error('参数错误！');

        // 获取备份文件信息
        $name  = date('Ymd-His', $time) . '-*.sql*';
        $path  = realpath(config('data_backup_path')) . DIRECTORY_SEPARATOR . $name;
        $files = glob($path);
        $list  = array();
        foreach ($files as $name) {
            $basename = basename($name);
            $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $name, $gz);
        }
        ksort($list);

        //检测文件正确性
        $last = end($list);
        if (count($list) === $last[0]) {
            set_time_limit(0)||ini_set("max_execution_time", 0);
            foreach ($list as $part) {
                $config = [
                    'path'     => realpath(config('data_backup_path')) . DIRECTORY_SEPARATOR,
                    'compress' => $part[2]
                ];
                $database = new Database($part, $config);
                $start = $database->import(0);
                while ($start !== 0) {
                    if ($start === false) {
                        $this->error('还原数据出错！');
                    }
                    $start = $database->import($start[0]);
                }
            }
            $this->success('还原成功');
        } else {
            $this->error('备份文件可能已经损坏，请检查！');
        }
    }

    /**
     * 转换字节格式
     */
    protected function transByte($list, $field)
    {
        $KB = 1024;       
        $MB = 1024 * $KB;
        $GB = 1024 * $MB;
        $TB = 1024 * $GB;

        foreach ($list as &$v) {
            $byte = $v[$field];
            if ($byte < $KB) {
                $v[$field] = $byte . 'B';
            } elseif ($byte < $MB) {
                $v[$field] = round($byte / $KB, 2) . ' KB';
            } elseif ($byte < $GB) {
                $v[$field] = round($byte / $MB, 2) . ' MB';
            } elseif ($byte < $TB) {
                $v[$field] = round($byte / $GB, 2) . ' GB';
            } else {
                $v[$field] = round($byte / $TB, 2) . ' TB';
            }
        }

        return $list;
    }
}