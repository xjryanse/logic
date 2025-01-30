<?php
namespace xjryanse\logic;

/**
 * Excel表格逻辑
 */
class Excel
{
    /**
     * 列数行数转excel单元格
     * @param type $column
     * @param type $row
     * @return type
     */
    public static function toExcelCell($column, $row) {
        return self::numberToColumn($column).$row;
    }
    /**
     * 列转数字
     */
    public static function columnToNumber($column){
        $number = 0;
        $length = strlen($column);
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord($column[$i]) - 64);
        }
        return $number;
    }
    
    public static function numberToColumn($number) {
        $colStr = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $colStr = chr(65 + $remainder) . $colStr;
            $number = intval(($number - 1) / 26);
        }
        return $colStr;
    }
    
    /**
     * 20250126:计算合并结束单元格
     */
    public static function calMergeEnd($startCell, $mergeColumns = 1, $mergeRows = 1){
        // $startCell = 'B5';
        // 提取起始列和起始行
        preg_match('/([A-Z]+)(\d+)/', $startCell, $matches);
        $startColumn = $matches[1];
        $startRow = (int)$matches[2];

        $startColumnNumber = self::columnToNumber($startColumn);
        // 计算结束列和结束行
        $endColumnNumber = $startColumnNumber + $mergeColumns - 1;
        $endRow = $startRow + $mergeRows - 1;

        // 将结束列数字转换为字母
        $endColumn = self::numberToColumn($endColumnNumber);

        // 得到结束单元格位置
        $endCell = $endColumn . $endRow;

        return $endCell;
    }
    

}
