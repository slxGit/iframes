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
 * @Last Modified Time: 2020-04-13 00:13:50
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use utils\Tree;
use app\common\builder\Builder;
use app\cms\model\TagModel;
use app\cms\model\ArticleModel;
use app\cms\model\CategoryModel;
use app\cms\model\ArticleTagModel;
use app\cms\validate\ArticleValidate;
use app\common\controller\AdminBaseController;

class AdminArticleController extends AdminBaseController
{
    /**
     * 属性
     */    
    protected $attributes = [
        1 => '发布',
        2 => '置顶',
        3 => '推荐'
    ];

	/**
     * 文章列表
     */
	public function index()
	{  
        $params = $this->request->param();

        // 查询条件
        $map = [];

        // 标题(查询条件)
        if (!empty($params['title'])) {
            $map[] = ['title', 'like', '%' . $params['title'] . '%'];
        }

        // 分类(查询条件)
        if (!empty($params['category_id'])) {
            $map[] = ['category_id', '=', $params['category_id']];
        }

        // 属性值
        if (!empty($params['attributes'])) {
            $attributes = explode(',', $params['attributes']);
            foreach ($attributes as $attribute) {
                switch ($attribute) {
                    case 1:
                        $map[] = ['is_publish', '=', 1];
                        break;
                    case 2:
                        $map[] = ['is_top', '=', 1];
                        break;
                    case 3:
                        $map[] = ['is_recommend', '=', 1];
                        break;                        
                }                
            }
        }

        $categoryList = CategoryModel::field('id,parent_id,name')->order('order', 'asc')->select();

        $categories = Tree::toLayer($categoryList, 3, function($item){
            $item['value'] = $item['id'];
            unset($item['icon_name']);
            unset($item['parent_id']);
            unset($item['level']);
            unset($item['extremity']);
            unset($item['id']);
            return $item;
        });

        // 获取文章列表
        $articleList = ArticleModel::where($map)
            ->field('id,title,category_id,is_publish,is_top,is_recommend,author,thumbnail,clicks,updated,published_time')
            ->order('updated', 'desc')
            ->paginate()->each(function($item, $key){
                $item->title = [url('cms/article/detail',['id'=>$item->id]), $item->title];
                if ($item->is_publish == 1) $attributes[] = 1;
                if ($item->is_top == 1) $attributes[] = 2;
                if ($item->is_recommend == 1) $attributes[] = 3;
                if (isset($attributes)) $item->attributes = $attributes;
            });

        // 获取分类列表
        $categoryList = CategoryModel::field('id,parent_id,name')->order('order', 'asc')->select();
        $categories = Tree::toLayer($categoryList, 3, function($item){
            $item['value'] = $item['id'];
            unset($item['icon_name']);
            unset($item['parent_id']);
            unset($item['level']);
            unset($item['extremity']);
            unset($item['id']);
            return $item;
        });

        return Builder::table()
            ->addColumns([
                ['id', 'ID', '', '', ['width' => '6%']],
                ['title', '标题', 'link', '', '', ['width' => '35%']],
                ['category_id', '分类', 'xmselect', $categories, '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]], ['width' => '20%']],
                ['is_publish', '发布', 'switch', '', ['width' => '10%']],
                ['is_top', '置顶', 'switch', '', ['width' => '10%']],
                ['is_recommend', '推荐', 'switch', '', ['width' => '10%']],
                ['author', '作者', 'text', '', ['width' =>'15%']],
                    ['thumbnail', '缩略图', 'image', '', '', ['width' => '10%']],
                ['clicks', '点击量', '', '', ['width' => '10%']],
                ['updated', '更新时间', '', '', ['width' => '15%']],
                ['published_time', '发布时间', '', '', ['width' => '15%']]
            ])
            ->addTopButtons('add')
            ->addTopButton('publish', [
                'title'    => '发布',
                'method'   => 'ajax',
                'url'      => url('publish'), 
                'param'    => [
                    'ids'  => '__id__'
                ],
                'complete' => 'refresh',
                'class'    => 'layui-btn layui-btn-sm'
            ])
            ->addTopButton('top', [
                'title'    => '置顶',
                'method'   => 'ajax',
                'url'      => url('top'), 
                'param'    => [
                    'ids'  => '__id__'
                ],
                'complete' => 'refresh',
                'class'    => 'layui-btn layui-bg-black layui-btn-sm'
            ])
            ->addTopButton('recommend', [
                'title'    => '推荐',
                'method'   => 'ajax',
                'url'      => url('recommend'), 
                'param'    => [
                    'ids'  => '__id__'
                ],
                'complete' => 'refresh',
                'class'    => 'layui-btn layui-btn-warm layui-btn-sm'
            ])
            ->addTopButton('delete')
            ->addSearchItems([
                ['text', 'title', '标题'],
                ['xmselect', 'category_id', '分类', $categories, ['tree' => ['show' => true, 'strict' => false], 'radio' => true, 'clickClose' => true, 'model' => ['label' => ['type' => 'text']]]],
                ['xmselect', 'attributes', '属性', $this->attributes, [], 4]
            ])
            ->addRightButtons('edit,delete')
            ->setRightToolbar(['width' => '15%'])
            ->addToolButtons('filter')
            ->setTableData($articleList)
            ->fetch();
	}

    /**
     * 文章添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new ArticleValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            // 属性处理
            if (isset($params['attributes'])) {
                $attributes = $params['attributes'];
                foreach ($attributes as $attribute) {
                    switch ($attribute) {
                        case 1:
                            $params['is_publish'] = 1;
                            $params['published_time'] = time();
                            break;
                        case 2:
                            $params['is_top'] = 1;
                            break;
                        case 3:
                            $params['is_recommend'] = 1;
                            break;                        
                    }
                }
            }
  
            $articleModel = new ArticleModel;
            $result = $articleModel->save($params);
   
            if ($result) {
                // 标签处理
                if (!empty($params['tags'])) {
                    $tags = explode(',', $params['tags']);
                    $tagModel = new TagModel;
                    // 添加标签并写入关系表
                    $tagList = [];
                    foreach ($tags as $tag) {
                        // 查询该标签是否已经存在
                        $dbTagId = TagModel::where('name', $tag)->value('id');
                        if ($dbTagId) {
                            $tagList[] = $dbTagId;
                        } else {
                            $tagList[] = ['name' => $tag];
                        }
                    }
                    $articleModel->tags()->saveAll($tagList);
                }
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            $categoryList = CategoryModel::field('id,parent_id,name')->order('order', 'asc')->select();

            $categories = Tree::toLayer($categoryList, 3, function($item){
                $item['value'] = $item['id'];
                unset($item['icon_name']);
                unset($item['parent_id']);
                unset($item['level']);
                unset($item['extremity']);
                unset($item['id']);
                return $item;
            });

            return Builder::form()
                ->setPageTitle('文章添加')
                ->addFormItems([
                    ['xmselect', 'category_id', '分类', '', $categories, '', '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]]
                    ],
                    ['text', 'title', '标题'],
                    ['checkbox', 'attributes', '属性', '', $this->attributes],
                    ['tags', 'tags', '标签'],
                    ['image', 'thumbnail', '缩略图', '', '', '', '100x100'],
                    ['text', 'author', '作者'],
                    ['tags', 'keywords', '关键字'],
                    ['textarea', 'excerpt', '摘要'],
                    ['ueditor', 'content', '内容']
                ])
                ->fetch();
        }
    }

    /**
     * 文章编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new ArticleValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            // 发布状态
            if (isset($params['attributes']) && in_array(1, $params['attributes'])) {
                $params['is_publish'] = 1;
                $dbIsPublish = ArticleModel::where('id', $params['id'])->value('is_publish');
                if ($dbIsPublish == 0) $params['published_time'] = time();
            } else {
                $params['is_publish'] = 0;
            }

            // 置顶状态
            if (isset($params['attributes']) && in_array(2, $params['attributes'])) {
                $params['is_top'] = 1;
            } else {
                $params['is_top'] = 0;
            }

            // 推荐状态
            if (isset($params['attributes']) && in_array(3, $params['attributes'])) {
                $params['is_recommend'] = 1;
            } else {
                $params['is_recommend'] = 0;
            }

            $articleModel = new ArticleModel;
            $result = $articleModel->save($params, ['id' => $params['id']]);

            if ($result) {
                // 获取文章信息
                $article = ArticleModel::get($params['id']);
                // 数据库中的标签
                $dbTags = array_column($article->tags()->select()->toArray(), 'name', 'id');
                // 修改后的标签名称
                $tagsName = explode(',', $params['tags']);
                // 需要新增的标签名称
                $insertTagsName = array_diff($tagsName, $dbTags);
                // 需要删除的标签ID
                $deleteTagsIds = array_keys(array_diff($dbTags, $tagsName));
                // 新增标签
                if (!empty($insertTagsName)) {
                    $tagList = [];
                    foreach ($insertTagsName as $tagName) {
                        // 已存在的标签不进行新增
                        $dbTagId = TagModel::where('name', $tagName)->value('id');
                        if ($dbTagId) {
                            $tagList[] = $dbTagId;
                        } else {
                            $tagList[] = ['name' => $tagName];
                        }
                    }
                    $articleModel->tags()->saveAll($tagList);
                }
                // 删除标签
                if (!empty($deleteTagsIds)) {
                    // 删除文章标签关联数据
                    $article->tags()->detach($deleteTagsIds);
                    // 剔除要删除的标签中，其他文章也正在使用
                    foreach ($deleteTagsIds as $k => $tagId) {
                        if (ArticleTagModel::where('tag_id', $tagId)->find()) {
                            unset($deleteTagsIds[$k]);
                        }
                    }
                    // 删除文章标签
                    if (!empty($deleteTagsIds)) {
                        TagModel::destroy(array_values($deleteTagsIds));
                    }   
                }
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $article = ArticleModel::get($id);
            $article['tags'] = $article->tags()->column('name');
            $attributes = [];
            if ($article['is_publish'] == 1) $attributes[] = 1;
            if ($article['is_top'] == 1) $attributes[] = 2;
            if ($article['is_recommend'] == 1) $attributes[] = 3;
            $article['attributes'] = $attributes;

            $categoryList = CategoryModel::field('id,parent_id,name')->order('order', 'asc')->select();

            $categories = Tree::toLayer($categoryList, 3, function($item){
                $item['value'] = $item['id'];
                unset($item['icon_name']);
                unset($item['parent_id']);
                unset($item['level']);
                unset($item['extremity']);
                unset($item['id']);
                return $item;
            });

            return Builder::form()
                ->setPageTitle('文章编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['xmselect', 'category_id', '分类', '', $categories, '', '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]]
                    ],
                    ['text', 'title', '标题'],
                    ['checkbox', 'attributes', '属性', '', $this->attributes],
                    ['tags', 'tags', '标签'],
                    ['image', 'thumbnail', '缩略图', '', '', '', '100x100'],
                    ['text', 'author', '作者'],
                    ['tags', 'keywords', '关键字'],
                    ['textarea', 'excerpt', '摘要'],
                    ['ueditor', 'content', '内容']
                ])
                ->setFormData($article)
                ->fetch();
        }        
    }

    /**
     * 文章删除
     */
    public function delete()
    {
        $ids = $this->request->param('ids');

        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            // 获取文章信息
            $article = ArticleModel::get($id);
            
            // 获取文章标签
            $tags = $article->tags()->select()->toArray();

            if (!empty($tags)) {
                $tagIds = array_column($tags, 'id');

                // 删除文章标签关联数据
                $article->tags()->detach($tagIds);

                // 剔除要删除的标签中，其他文章也正在使用
                foreach ($tagIds as $k => $tagId) {
                    if (ArticleTagModel::where('tag_id', $tagId)->find()) {
                        unset($tagIds[$k]);
                    }
                }
                
                // 删除文章标签
                if (!empty($tagIds)) {
                    TagModel::destroy(array_values($tagIds));
                }            
            }
        }

        // 删除文章
        $result = ArticleModel::destroy($ids);

        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 文章发布
     */    
    public function publish($ids = '')
    {
        $result = ArticleModel::where('id', 'in', $ids)->update(['is_publish' => 1, 'published_time' => time()]);
        if ($result !== false) {
            $this->success('发布成功'); 
        } else {
            $this->error('发布失败');
        }
    }

    /**
     * 文章置顶
     */    
    public function top($ids = '')
    {
        $result = ArticleModel::where('id', 'in', $ids)->update(['is_top' => 1]);
        if ($result !== false) {
            $this->success('置顶成功'); 
        } else {
            $this->error('置顶失败');
        }
    }

    /**
     * 文章推荐
     */    
    public function recommend($ids = '')
    {
        $result = ArticleModel::where('id', 'in', $ids)->update(['is_recommend' => 1]);
        if ($result !== false) {
            $this->success('推荐成功'); 
        } else {
            $this->error('推荐失败');
        }
    }

    /**
     * 快捷编辑
     */
    public function quickEdit()
    {
        $params = $this->request->param();
 
        switch ($params['name']) {
            case 'is_publish':
                if ($params['value'] == 1) {
                    $result = ArticleModel::where('id', $params['id'])->update(['is_publish' => 1, 'published_time' => time()]);
                } else {
                    $result = ArticleModel::where('id', $params['id'])->update(['is_publish' => 0]);
                }
                break;
            
            case 'is_top':
                if ($params['value'] == 1) {
                    $result = ArticleModel::where('id', $params['id'])->update(['is_top' => 1]);
                } else {
                    $result = ArticleModel::where('id', $params['id'])->update(['is_top' => 0]);
                }
                break;

            case 'is_recommend':
                if ($params['value'] == 1) {
                    $result = ArticleModel::where('id', $params['id'])->update(['is_recommend' => 1]);
                } else {
                    $result = ArticleModel::where('id', $params['id'])->update(['is_recommend' => 0]);
                }
                break;
        }

        if ($result !== false) {
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }
}