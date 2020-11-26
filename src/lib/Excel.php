<?php
namespace Lethe\Lib;

/* **********************************************************
 * 导出Excel表格
 * 在PHPExcel基础上封装
 * @author: wangjianrong<wangjr129@163.com>
 * @Date: 2018/10/15 下午7:41
 * @copyright Copyright 2018-2023 © www.ruiJie.com.cn All rights reserved.
 * @license http://www.ruijie.com.cn/gy/
 * *********************************************************** */

class Excel
{
    private $sheetTitle;
    private $filename;
    private $titleRow = [];
    private $phpExcel = null;
    private $currentSheet  = null;
    private static $row = 1;

    public function __construct()
    {
        $this->phpExcel = new PHPExcel();
        $this->currentSheet=$this->phpExcel->getActiveSheet();
        $this->currentSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $this->currentSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    /**
     * 设置文件名以及表单名
     * @param string $filename 文件名
     * @param string $sheetTitle 工作表sheet名
     */
    public function config($filename = '', $sheetTitle = '')
    {
        $this->filename = !empty($filename) ? $filename : time();
        $this->sheetTitle = !empty($sheetTitle) ? $sheetTitle : time();
    }

    /**
     * @param array $titleRow 头部数据
     */
    public function setTitleRow($titleRow = [])
    {
        $this->titleRow = $titleRow;
    }

    /**
     * 生成Excel表单
     * @param array $data 数据内容
     * @throws PHPExcel_Writer_Exception
     */
    public function excelXls($data = [])
    {
        $this->currentSheet->setTitle($this->sheetTitle);
        $this->addTitleRow();
        $this->addRow($data);

        $objWriter =  new PHPExcel_Writer_Excel5($this->phpExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename='.$this->filename.'-'.date('Y_m_d H:i:s').'.xls');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 添加标题数据
     * @return bool
     */
    public function addTitleRow()
    {
        if (empty($this->titleRow)) {
            return true;
        }
        $columnNum = 0;
        foreach ($this->titleRow as &$value) {
            $column = PHPExcel_Cell::stringFromColumnIndex($columnNum);
            $this->currentSheet->setCellValueExplicit($column.self::$row, $value, PHPExcel_Cell_DataType::TYPE_STRING);
            ++$columnNum;
        }
        self::$row++;
        return true;
    }

    /**
     * 生成内容数据
     * @param array $data
     * @return bool
     */
    public function addRow($data = [])
    {
        foreach ($data as &$rowVal) {
            $columnNum = 0;//列号
            foreach ($rowVal as &$val) {
                $column = PHPExcel_Cell::stringFromColumnIndex($columnNum);
                $this->currentSheet->setCellValueExplicit($column.self::$row, $val, PHPExcel_Cell_DataType::TYPE_STRING);
                $columnNum++;
            }
            ++self::$row;//行号
        }
        return true;
    }
}
