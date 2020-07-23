<?php
namespace app\common\builder;

use think\Controller;

/**
 * 构建器
 */
class Builder extends Controller
{
	/**
     * 实例化构建器
     * @access public
     * @param string $name 构建器名称
     * @return mixed
     */
	protected function instanceBuilder($name)
	{	
		$name = strtolower($name);

		$className = ucfirst($name) . 'Builder';

        $class = "\\app\\common\\builder\\{$name}\\{$className}";

       	return new $class;
	}

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
	public function __call($method, $args){
	    return $this->instanceBuilder($method);
  	}

    /**
     * 魔术方法 有不存在的静态操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
  	public static function __callStatic($method, $args)
	{
		return (new self())->instanceBuilder($method);
	}
}