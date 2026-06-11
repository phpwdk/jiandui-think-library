<?php
declare (strict_types=1);

namespace think\simple\helper;

use think\simple\Helper;
use think\simple\service\SystemService;
use think\db\BaseQuery;
use think\Model;
use think\simple\service\TokenService;

/**
 * 表单视图管理器
 * Class FormHelper
 *
 * @package think\simple\helper
 */
class FormHelper extends Helper
{

    /**
     * 逻辑器初始化
     *
     * @param Model|BaseQuery|string $dbQuery
     * @param string                 $template 视图模板名称
     * @param string                 $key      指定数据主键
     * @param mixed                  $where    限定更新条件
     * @param array                  $edata    表单扩展数据
     * @param string                 $field    禁止返回字段
     * @param bool                   $refresh  是否刷新数据
     *
     * @return void|array|boolean
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @todo 判断模板为空时返回JSON数据
     */
    public function init($dbQuery, string $template = '', string $key = '', $where = [], array $edata = [], string $field = '', bool $refresh = true)
    {
        $query = $this->buildQuery($dbQuery);
        $key   = $key ?: ($query->getPk() ?: 'id');
        $value = $edata[$key] ?? input($key);
        if ($this->app->request->isGet()) {
            if ($value !== null) {
                empty($field) || $query->withoutField($field);
                $exist = $query->where([$key => $value])->where($where)->find();
                if ($exist instanceof Model) $exist = $exist->toArray();
                $edata = array_merge($edata, $exist ?: []);
            }
            $csrf  = TokenService::instance()->buildFormToken();
            $edata = array_merge($edata, ['csrf' => $csrf['token']]);
            if (false !== $this->class->callback('_form_filter', $edata)) {
                $template ? $this->class->fetch($template, ['vo' => $edata]) : $this->class->success(lang('success'), $edata);
            } else {
                return $edata;
            }
        }
        if ($this->app->request->isPost()) {
            $edata = array_merge($this->app->request->post(), $edata);
            if (false !== $this->class->callback('_form_filter', $edata, $where)) {
                $result = SystemService::instance()->save($query, $edata, $key, $where) !== false;
                if (false !== $this->class->callback('_form_result', $result, $edata)) {
                    if ($result !== false) {
                        $this->class->success(lang('think_library_form_success'), '{-null-}', $refresh ? 1 : 200);
                    } else {
                        $this->class->error(lang('think_library_form_error'));
                    }
                }
                return $result;
            }
        }
    }
}
