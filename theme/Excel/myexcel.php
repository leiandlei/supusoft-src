<?php
require_once(__DIR__.'/phpexcel.php');
/**
 * Myexcel
 * 创建excell表格，导出数据用
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class Myexcel
{
    
    private $fileName;
    private $objPHPExcel;

    
    private $defaultProperties = array(
                'title' => 'Office 2007 XLSX Document',
                'subject' => 'Office 2007 XLSX Document',
                'description' => '数据导出'
            );
    
    
    public function __construct($fileName = '', $properties = array()){
        $this->fileName = $fileName;
        if(!is_file($fileName)){
            //新建文档
            $this->objPHPExcel = new PHPExcel();

            $properties = array_merge($this->defaultProperties, $properties);

            $this->objPHPExcel->getProperties()->setCreator($properties['creator'])
                ->setLastModifiedBy($properties['modifiedBy'])
                ->setTitle($properties['title'])
                ->setSubject($properties['subject'])
                ->setDescription($properties['description']);

        }else{
            //读取文档
            $PHPReader = new PHPExcel_Reader_Excel2007();

            if(!$PHPReader->canRead($fileName)){
                $PHPReader = new PHPExcel_Reader_Excel5();
            }

            if(!$PHPReader->canRead($fileName)){
                throw new Exception('no Excel');
            }

            $this->objPHPExcel = $PHPReader->load($fileName);

        }

        //默认在第一个sheet
        $this->setIndex(0);
    }
    
    /**
     * Myexcel::setIndex()
     * 
     * @param mixed $index
     * @return $this
     */
    public function setIndex($index){
        $this->objPHPExcel->setActiveSheetIndex($index);
        return $this;
    }
    
    /**
     * Myexcel::getCurrentSheet()
     * 
     * @return 当前工作表对象
     */
    public function getCurrentSheet(){
        return $this->objPHPExcel->getActiveSheet();
    }
    /**
     * Myexcel::string2index()
     * 将字母列名转数字键 如 A => 0
     * @param mixed $cloumnName
     * @return
     */
    public function string2index($cloumnName){
        if(preg_match("/^[a-zA-Z\s]+$/",$columnIndex)){
            $columnIndex = PHPExcel_Cell::columnIndexFromString($cloumnName)-1;
        }
        return $columnIndex;
    }
    /**
     * Myexcel::index2string()
     * 将列的数字序号转成字母 从0开始： 0 => A 
     * @param mixed $columnIndex
     * @return
     */
    public function index2string($columnIndex){
        if( is_numeric($columnIndex) ){
            $columnIndex = PHPExcel_Cell::stringFromColumnIndex($columnIndex);
        }
        if(preg_match("/^[a-zA-Z\s]+$/",$columnIndex)){
            $columnIndex = strtoupper($columnIndex);
        }
        return $columnIndex;
    }

    /**
     * @return string
     * @description: 获取最大列号，如E
     */
    public function getMaxCellName ()
    {
        return $this->getCurrentSheet()->getHighestColumn();
    }


    /**
     * @return int
     * @description: 获取最大行数
     */
    public function getMaxRowNumber ()
    {
        return $this->getCurrentSheet()->getHighestRow();
    }
    
    
    /**
     * Myexcel::setTitle()
     * 设置工作表名称
     * @param mixed $title
     * @param mixed $sheetIndex
     * @return $this
     */
    public function setTitle($title, $sheetIndex = null){
        $sheetIndex === null ?
            $this->getCurrentSheet()->setTitle($title) :
            $this->setIndex($sheetIndex)->setTitle($title);
        return $this;
    }
    
    
    
    /**
     * Myexcel::addData()
     * 一个格一个格地添加数据
     * @param mixed $position 如：A1
     * @param string $value 被添加的字符串
     * @return $this
     */
    public function addData($position, $value = ''){
        if( is_numeric($value) ){
            $this->getCurrentSheet()->setCellValueExplicit($position,$value.' ',PHPExcel_Cell_DataType::TYPE_STRING);
        }else{
            $this->getCurrentSheet()->setCellValue($position,$value);
        }
        return $this;
    }

    /**
     * 添加行
     * @param integer $begin  位置
     * @param integer $number 行数
     */
    public function addRow($begin=1,$number=1){
        $this->getCurrentSheet()->insertNewRowBefore($begin,$number);
        return $this;
    }
    public function removeRow($begin=1,$number=1){
        $this->getCurrentSheet()->removeRow($begin,$number);
        return $this;
    }
    
    /**
     * Myexcel::addOneRowFromArray()
     * 参数必须为一位数组，将数组的数据添加到excell的一行中
     * @param mixed $dataArray数组：array('第一格文字', '第二格文字', 4=>'第五格文字', '第六格文字', 'key' => '第七格文字')
     * @param integer $rowNumber 插入的行号
     * 
     * @return $this
     */
    public function addOneRowFromArray($dataArray = array(), $rowNumber = 1){
        $i = 0;
        foreach($dataArray as $column => $words){
            $cn = $this->index2string($column);
            $position = $cn.$rowNumber;
            $this->addData($position, $words);
        }
        return $this;
    }
    
    /**
     * Myexcel::addDataFromArray()
     * 
     * 参数必须为二位数组，将数组中的数据逐行添加到excell中
     * 
     * @param mixed $dataArray
     * @param integer $beginRow
     * @return $this
     */
    public function addDataFromArray($dataArray = array(), $beginRow = 1){
        foreach($dataArray as $row){
            $this->addOneRowFromArray($row, $beginRow);
            $beginRow++;
        }
        return $this;
    }
    
    /**
     * Myexcel::mergeCells()
     * 合并单元格，
     * @param mixed $leftTop 左上角开始的位置 如:A2
     * @param mixed $rightBottom 右下角结束的位置 如 C4 合并后 A2 A3 A4 B2 B3 B4 C2 C3 C4 这些都合并成A2一个了
     * @return $this
     */
    public function mergeCells($leftTop, $rightBottom){
        $this->getCurrentSheet()->mergeCells($leftTop.':'.$rightBottom);
        return $this;
    }
    
    /**
     * Myexcel::vAlign()
     * 设置单元格垂直格式
     * 
     * 当第一个参数为数组时，自动忽略第二个参数，第一个数组中的每个格单独设置如array('A1', 'A2', 'B3', 'E2')
     * 
     * 当第一个参数为字符串时，如果第二个参数为空，则只设置第一个参数指定的格，否则设置从第一个参数指定的格到第二个参数指定的格全部样式
     * 
     * @param mixed $beginCell
     * @param string $endCell
     * @param string $alignment 具体值可参考 PHPExcel_Style_Alignment 类，默认PHPExcel_Style_Alignment::VERTICAL_CENTER
     * @return $this
     */
    public function vAlign($beginCell, $endCell = '', $alignment = 'center'){
        if(is_string($beginCell)){
            if($endCell == ''){
                $this->getCurrentSheet()->getStyle($beginCell)->getAlignment()->setVertical($alignment);
            }else{
                $this->getCurrentSheet()->getStyle($beginCell.':'.$endCell)->getAlignment()->setVertical($alignment);
            }
        }elseif(is_array($beginCell)){
            foreach($beginCell as $cell){
                $this->getCurrentSheet()->getStyle($cell)->getAlignment()->setVertical($alignment);
            }
        }
        return $this;
    }
    
    /**
     * Myexcel::hAlign()
     * 设置单元格水平格式
     * @param mixed $beginCell
     * @param string $endCell
     * @param string $alignment 具体值可参考 PHPExcel_Style_Alignment 类，默认PHPExcel_Style_Alignment::HORIZONTAL_CENTER
     * @return $this
     */
    public function hAlign($beginCell, $endCell = '', $alignment = 'center'){
        if(is_string($beginCell)){
            if($endCell == ''){
                $this->getCurrentSheet()->getStyle($beginCell)->getAlignment()->setHorizontal($alignment);
            }else{
                $this->getCurrentSheet()->getStyle($beginCell.':'.$endCell)->getAlignment()->setHorizontal($alignment);
            }
        }elseif(is_array($beginCell)){
            foreach($beginCell as $cell){
                $this->getCurrentSheet()->getStyle($cell)->getAlignment()->setHorizontal($alignment);
            }
        }
        return $this;
    }

    /**
     * setDefaultWidth()
     * 说明：设置excel表默认宽度
     * @param int $width
     */
    public function setDefaultWidth ($width = 0)
    {
        $this->getCurrentSheet()->getDefaultColumnDimension()->setWidth($width);
        return $this;
    }

    /**
     * setDefaultHeight()
     * 说明：设置excel表默认行高
    * @param int $height
     */
    public function setDefaultHeight ($height = 0)
    {
        $this->getCurrentSheet()->getDefaultColumnDimension()->setRowHeight($height);
        return $this;
    }
    /**
     * Myexcel::setWidth()
     * 设置单元格宽度
     * @param mixed $column 列名，如A、B
     * @param string $width 宽度，设置数字如 30，否则设置自动宽度
     * @return $this
     */
    public function setWidth($column, $width = 'auto'){
        if(is_array($column)){
            if(is_numeric($width)){
                foreach($column as $c){
                    $this->getCurrentSheet()->getColumnDimension($c)->setWidth($width);
                }
            }else{
                foreach($column as $c){
                    $this->getCurrentSheet()->getColumnDimension($c)->setAutoSize(true);
                }
            }
        }else{
            if(is_numeric($width)){
                $this->getCurrentSheet()->getColumnDimension($column)->setWidth($width);
            }else{
                $this->getCurrentSheet()->getColumnDimension($column)->setAutoSize(true);
            }
        }
        
        return $this;
    }
    
    /**
     * Myexcel::setWidthFromArray()
     * 批量设置宽度
     * @param mixed $array array('A'=>30, 'B'=>20 ...)
     * @return $this
     */
    public function setWidthFromArray($array = array()){
        foreach($array as $column => $width){
            $this->setWidth($column, $width);
        }
        return $this;
    }
    
    /**
     * Myexcel::setRowHeight()
     * 设置行高
     * @param intiger $height 高度
     * @param string $lineNumber 要设置的行，如果为空则设置所有行的默认行高
     * @return void
     */
    public function setRowHeight($height, $lineNumber = ''){
        if($lineNumber != ''){
            $cell = $this->getCurrentSheet()->getRowDimension($lineNumber);
        }else{
            $cell = $this->getCurrentSheet()->getDefaultRowDimension();
        }
        $cell->setRowHeight($height);
        return $this;
    }
    
    
    /**
     * Myexcel::setRowHeightFromArray()
     * 批量设置某些行的行高
     * @param mixed $RowArray
     * @param integer $height
     * @return void
     */
    public function setRowHeightFromArray($RowArray){
        foreach($RowArray as $row => $height){
            $this->setRowHeight($height, $row);
        }
        return $this;
    }

    /**
     * @param           $beginPosition
     * @param string    $endPosition
     * @param bool|true $autoWrap
     *
     * @return $this
     * @discription:设置文字自动换行
     */
    public function setWrapText ($beginPosition, $endPosition = '', $autoWrap = true)
    {
        if(is_string($beginPosition)){
            if($endPosition == ''){
                $this->getCurrentSheet()->getStyle($beginPosition)->getAlignment()->setWrapText($autoWrap);
            }else{
                $this->getCurrentSheet()->getStyle($beginPosition.':'.$endPosition)->getAlignment()->setWrapText($autoWrap);
            }
        }elseif(is_array($beginPosition)){
            foreach($beginPosition as $cell){
                $this->getCurrentSheet()->getStyle($cell)->getAlignment()->setWrapText($autoWrap);
            }
        }
        return $this;
    }
  
  
    /**
     * Myexcel::setBold()
     * 设置字体加粗
     * @param mixed $beginPosition 单元格起始位置，如 A1 
     * @param string $endPosition 单元格结束位置，如 C3
     * @param bool $bold 如果为false则为取消加粗
     * 如果结束位置为空，则只设置起始位置一个格的格式
     * @return $this
     */
    public function setBold($beginPosition, $endPosition = '', $bold = true){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }
        $this->getCurrentSheet()->getStyle($beginPosition)->getFont()->setBold($bold);
        return $this;
    }
    
    
    /**
     * Myexcel::setColor()
     * 字体颜色
     * @param mixed $beginPosition
     * @param string $endPosition
     * @param mixed $color
     * @return $this
     */
    public function setColor($beginPosition, $endPosition = '', $color = PHPExcel_Style_Color::COLOR_BLACK){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }
        $this->getCurrentSheet()->getStyle($beginPosition)->getFont()->getColor()->setARGB($color);
        return $this;
    }
    
    /**
     * Myexcel::setSize()
     * 字体大小
     * @param mixed $fontSize 数字
     * @param mixed $beginPosition
     * @param string $endPosition
     * @return
     */
    public function setSize($fontSize, $beginPosition, $endPosition = ''){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }
        $this->getCurrentSheet()->getStyle($beginPosition)->getFont()->setSize($fontSize);
        return $this;
    }
    
    /**
     * Myexcel::setName()
     * 字体名称
     * @param mixed $fontName
     * @param mixed $beginPosition
     * @param string $endPosition
     * @return $this
     */
    public function setName($fontName, $beginPosition, $endPosition = ''){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }
        $this->getCurrentSheet()->getStyle($beginPosition)->getFont()->setName($fontName);
        return $this;
    }
    
    /**
     * Myexcel::setItalic()
     * 设置字体倾斜
     * @param mixed $beginPosition
     * @param string $endPosition
     * @param bool $italic
     * @return $this
     */
    public function setItalic($beginPosition, $endPosition = '', $italic = true){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }
        $this->getCurrentSheet()->getStyle($beginPosition)->getFont()->setItalic($italic);
        return $this;
    }
    
    /**
     * Myexcel::setUnderline()
     * 加下划线
     * @param mixed $beginPosition
     * @param string $endPosition
     * @param mixed $lineStyle
     * @return $this
     */
    public function setUnderline($beginPosition, $endPosition = '', $lineStyle = PHPExcel_Style_Font::UNDERLINE_SINGLE){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }
        $this->getCurrentSheet()->getStyle($beginPosition)->getFont()->setUnderline($lineStyle);
        return $this;
    }
    
    /**
     * Myexcel::setBackgroundColor()
     * 设置单元格背景色
     * @param mixed $beginPosition
     * @param string $endPosition
     * @param string $argbColor
     * @param mixed $fillStyle
     * @return $this
     */
    public function setBackgroundColor($beginPosition, $endPosition = '', $argbColor = '0xFFFFFFFF', $fillStyle = PHPExcel_Style_Fill::FILL_SOLID){
        if($endPosition != ''){
            $beginPosition .= ':'.$endPosition;
        }

        $this->getCurrentSheet()->getStyle($beginPosition)->getFill()->setFillType($fillStyle);
        $this->getCurrentSheet()->getStyle($beginPosition)->getFill()->getStartColor()->setARGB($argbColor);
        return $this;
    }
    
    
    /**
     * Myexcel::setBorder()
     * 给指定的行或列添加指定方向的边框
     * @param mixed $orientation
     * @param mixed $position
     * @param mixed $borderStyle
     * @return $this
     */
    public function setBorder($position, $orientation, $borderColor = 'FF000000', $borderStyle = PHPExcel_Style_Border::BORDER_THIN){
        
        switch($orientation){
            case 'top':
                $this->getCurrentSheet()->getStyle($position)->getBorders()->getTop()->setBorderStyle($borderStyle); 
                if($borderColor != 'FF000000' && $borderColor != ''){
                    $this->getCurrentSheet()->getStyle($position)->getBorders()->getTop()->getColor()->setARGB($borderColor);
                }
                break;
            case 'left':
                $this->getCurrentSheet()->getStyle($position)->getBorders()->getLeft()->setBorderStyle($borderStyle); 
                if($borderColor != 'FF000000' && $borderColor != ''){
                    $this->getCurrentSheet()->getStyle($position)->getBorders()->getLeft()->getColor()->setARGB($borderColor);
                }
                break;
            case 'right':
                $this->getCurrentSheet()->getStyle($position)->getBorders()->getRight()->setBorderStyle($borderStyle);
                if($borderColor != 'FF000000' && $borderColor != ''){
                    $this->getCurrentSheet()->getStyle($position)->getBorders()->getRight()->getColor()->setARGB($borderColor);
                }
                break;
            case 'bottom':
                $this->getCurrentSheet()->getStyle($position)->getBorders()->getBottom()->setBorderStyle($borderStyle);
                if($borderColor != 'FF000000' && $borderColor != ''){
                    $this->getCurrentSheet()->getStyle($position)->getBorders()->getBottom()->getColor()->setARGB($borderColor);
                }
                break;
        }
        
        return $this;
    }
    
    /**
     * Myexcel::setBorders()
     * 多行多列设置多个方向的边框，兼容setBorder方法
     * @param string $orientation 哪个方向加边框，上下左右：top bottom left right 多个用逗号分隔
     * @param mixed $beginPosition
     * @param string $endPosition
     * @param mixed $borderStyle
     * @return $this 
     */
    public function setBorders($beginPosition, $endPosition = '', $orientation='right,bottom', $borderColor = 'FF000000', $borderStyle = PHPExcel_Style_Border::BORDER_THIN){
        $orientation = explode(',', $orientation);
        if($endPosition != ''){
            preg_match("/^([A-Z]+)(\d+)$/", $beginPosition, $matched_begin);
            preg_match("/^([A-Z]+)(\d+)$/", $endPosition, $matched_end);
            if(!empty($matched_begin) && !empty($matched_end)){
                $beginColumn = $matched_begin[1];
                $beginRow = $matched_begin[2];
                
                $endColumn = $matched_end[1];
                $endRow = $matched_end[2];
                
                $beginColumnIndex = $this->string2index($beginColumn);
                $endColumnIndex = $this->string2index($endColumn);
                
                for(; $beginColumnIndex <= $endColumnIndex; $beginColumnIndex++){ //给每一列添加竖线
                    foreach($orientation as $o){
                        $o = trim($o);
                        if($o == 'left' || $o == 'right'){
                            $column = $this->index2string($beginColumnIndex);
                            $this->setBorder($column.$beginRow.':'.$column.$endRow, $o, $borderColor, $borderStyle);
                        }
                    }
                }
                for(;$beginRow <= $endRow; $beginRow++){//给每一行添加横线
                    foreach($orientation as $o){
                        $o = trim($o);
                        if($o == 'top' || $o == 'bottom'){
                            $column = $this->index2string($beginRow);
                            $this->setBorder($beginColumn.$beginRow.':'.$endColumn.$beginRow, $o, $borderColor, $borderStyle);
                        }
                    }
                }
                
                return $this;
            }
        }
        
        foreach($orientation as $o){
            $this->setBorder($beginPosition, trim($o), $borderColor, $borderStyle);
        }
        return $this;
        
    }

    /**
     * hideGridlines()
     * 说明：是否显示网格线
     * @param bool|false $show
     */
    public function hideGridlines ($show = false)
    {
        $this->getCurrentSheet()->setShowGridlines($show);
        return $this;
    }

    /**
     * @param string $column 列名，如A、B
     * @param bool|true $visiable true为显示，flase为隐藏
     *
     * @return $this
     * @discription: 显示或隐藏列
     */
    public function setColumnVisiable ($column, $visiable = true)
    {
        $this->getCurrentSheet()->getColumnDimension($column)->setVisible($visiable);
        return $this;
    }
   
    /**
     * Myexcel::addSheet()
     * 新建一个工作表
     * @param string $title 新工作表名称
     * @return $this
     */
    public function addSheet($title = ' '){
        $msgWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, $title); 
        $this->objPHPExcel->addSheet($msgWorkSheet); //插入工作表
        return $this;
    }
    
    /**
     *保存并下载
     * @param  string $fileName 文件名
     * @return
     */
    public function download($fileName = ''){
        if($fileName == ''){
            $fileName = $this->fileName;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Type:application/vnd.ms-execl");
        header('Content-Disposition:attachment;filename="'.$fileName.'"');
        header("Content-Transfer-Encoding:binary");
        $objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
        $objWriter->save('php://output');
    } 
    
    /**
     * 保存文件
     * @param  string $savePath 保存的目录 不要用__ROOT__之类的 要写相对路径
     * @param  string $fileName 保存文件的名称
     * @return array            返回原先的文件名和保存的文件名
     */
    public function saveAsFile($savePath = '', $fileName = ''){
        $objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
        if($fileName == ''){
            $fileName = $this->fileName;
        }

        //是否是windos
        $isWin = (strtolower(substr(PHP_OS,0,3))=='win');

        if($isWin){
            $saveFile = iconv('UTF-8','gbk',$savePath.$fileName);
        }else{
            $saveFile = $savePath.$fileName;
        }

        $saveName = $objWriter->save($saveFile);

        return $saveInfo = array(
                                 'saveName' => $saveFile
                                ,'oldName'  => $savePath.$fileName
                            );
    }
    
    
    
    //Excel 读
    /**
     * @param string|array $BeginPosition 起始位置，如A1，也可以为数组，如果为数组则忽略第二个参数，且以数组形式返回数组中所有的单元格内容
     * @param string $endPosition 结束位置 如 A100，如果为空则只读取起始位置的单元格内容
     *
     * @return string|array
     *
     * @discription: 读取Excel单元格内容
     */
    public function readData ($beginPosition, $endPosition = '')
    {
        if(is_string($beginPosition)){
            preg_match("/^([A-Z]+)(\d+)$/", $beginPosition, $matched_begin);
            preg_match("/^([A-Z]+)(\d+)$/", $endPosition, $matched_end);

            $beginColumn = $matched_begin[1];
            $beginRow = $matched_begin[2];


            if(count($matched_end) == 3){
                $beginColumnIndex = $this->string2index($beginColumn);
                $return  = array();

                $endColumn = $matched_end[1];
                $endRow = $matched_end[2];
                $endColumnIndex = $this->string2index($endColumn);

                for(; $beginColumnIndex <= $endColumnIndex; $beginColumnIndex++){
                    for($row = $beginRow;$row <= $endRow; $row++){
                        $column = $this->index2string($beginColumnIndex);
                        //$return[$column.$row] = $this->getCurrentSheet()->getCellByColumnAndRow($beginColumnIndex,$row)->getValue();
                        $return[$column.$row] = $this->getCurrentSheet()->getCell($column.$row)->getValue();
                    }
                }

                return $return;
            }else{
                //只读取$beginPosition内容
                return $this->getCurrentSheet()->getCell($beginColumn.$beginRow)->getValue();
            }


        }else{
            $return  = array();
            $beginPosition = array_unique($beginPosition);
            foreach($beginPosition as $position){
                $return[$position] = $this->readData($position);
            }
            return $return;
        }


    }
    
}