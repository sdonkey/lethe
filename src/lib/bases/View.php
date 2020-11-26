<?php
/**
 * Base View
 *
 * @author wuqiying@ruijie.com.cn
 */
namespace Lethe\Lib\Bases;

class View extends \Yaf\View\Simple
{
    /**
     * @param string $tpl
     * @param array  $vars
     * @return void
     */
    public function render($tpl, $vars = [])
    {
        $this->jsonOutput(array_merge($this->_tpl_vars, $vars));
    }
    /**
     * @param string $tpl
     * @param array  $vars
     * @return void
     */
    public function display($tpl, $vars = [])
    {
        $this->jsonOutput(array_merge($this->_tpl_vars, $vars));
    }
    /**
     * @param $data
     */
    private function jsonOutput($data)
    {
        $data['code'] = $data['code'] ?: 0;
        $data['status'] = $data['code'] ? 'error' : 'success';
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
