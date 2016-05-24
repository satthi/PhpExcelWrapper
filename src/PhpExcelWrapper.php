<?php

namespace PhpExcelWrapper;

use \PHPExcel_IOFactory;
use \PHPExcel_Cell;
use \PHPExcel_Worksheet_Drawing;
use \PHPExcel_Style_Border;
use \PHPExcel_Style_Alignment;
use \PHPExcel_Style_Fill;

/**
* PhpExcelWrapper
* PHPExcelを記載しやすくするためのラッパー
*/
class PhpExcelWrapper
{
    private $__phpexcel;
    private $__sheet = [];
    private $__deleteSheetList = [];
    private static $__borderType = [
        'none' => PHPExcel_Style_Border::BORDER_NONE,
        'thin' => PHPExcel_Style_Border::BORDER_THIN,
        'medium' => PHPExcel_Style_Border::BORDER_MEDIUM,
        'dashed' => PHPExcel_Style_Border::BORDER_DASHED,
        'dotted' => PHPExcel_Style_Border::BORDER_DOTTED,
        'thick' => PHPExcel_Style_Border::BORDER_THICK,
        'double' => PHPExcel_Style_Border::BORDER_DOUBLE,
        'hair' => PHPExcel_Style_Border::BORDER_HAIR,
        'mediumdashed' => PHPExcel_Style_Border::BORDER_MEDIUMDASHED,
        'dashdot' => PHPExcel_Style_Border::BORDER_DASHDOT,
        'mediumdashdot' => PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT,
        'dashdotdot' => PHPExcel_Style_Border::BORDER_DASHDOTDOT,
        'mediumdashdotdot' => PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT,
        'slantdashdot' => PHPExcel_Style_Border::BORDER_SLANTDASHDOT,
    ];

    private static $__alignHolizonalType = [
        'general' => PHPExcel_Style_Alignment::HORIZONTAL_GENERAL,
        'center' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'left' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        'right' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        'justify' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
        'countinuous' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER_CONTINUOUS,
    ];

    private static $__alignVerticalType = [
        'bottom' => PHPExcel_Style_Alignment::VERTICAL_BOTTOM,
        'center' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'justify' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
        'top' => PHPExcel_Style_Alignment::VERTICAL_TOP,
    ];

    private static $__fillType = [
        'linear' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
        'path' => PHPExcel_Style_Fill::FILL_GRADIENT_PATH,
        'none' => PHPExcel_Style_Fill::FILL_NONE,
        'darkdown' => PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN,
        'darkgray' => PHPExcel_Style_Fill::FILL_PATTERN_DARKGRAY,
        'darkgrid' => PHPExcel_Style_Fill::FILL_PATTERN_DARKGRID,
        'darkhorizontal' => PHPExcel_Style_Fill::FILL_PATTERN_DARKHORIZONTAL,
        'darktrellis' => PHPExcel_Style_Fill::FILL_PATTERN_DARKTRELLIS,
        'darkup' => PHPExcel_Style_Fill::FILL_PATTERN_DARKUP,
        'darkvertical' => PHPExcel_Style_Fill::FILL_PATTERN_DARKVERTICAL,
        'gray0625' => PHPExcel_Style_Fill::FILL_PATTERN_GRAY0625,
        'gray125' => PHPExcel_Style_Fill::FILL_PATTERN_GRAY125,
        'lightdown' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTDOWN,
        'lightgray' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTGRAY,
        'lightgrid' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTGRID,
        'lighthorizontal' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTHORIZONTAL,
        'lighttrellis' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTTRELLIS,
        'lightup' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTUP,
        'lightvertical' => PHPExcel_Style_Fill::FILL_PATTERN_LIGHTVERTICAL,
        'mediumgray' => PHPExcel_Style_Fill::FILL_PATTERN_MEDIUMGRAY,
        'solid' => PHPExcel_Style_Fill::FILL_SOLID,
    ];

    /**
    * __construct
    *
    * @param string $template テンプレートファイルのパス
    * @author hagiwara
    */
    public function __construct($template = null, $type = 'Excel2007')
    {
        if ($template === null) {
            //テンプレート無し
            $this->__phpexcel = new \PHPExcel();
        } else {
            //テンプレートの読み込み
            $reader = PHPExcel_IOFactory::createReader($type);
            $this->__phpexcel = $reader->load($template);
        }
    }

    /**
    * setVal
    * 値のセット
    * @param string $value 値
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param integer $refCol 参照セル行
    * @param integer $refRow 参照セル列
    * @param integer $refSheet 参照シート
    * @author hagiwara
    */
    public function setVal($value, $col, $row, $sheetNo = 0, $refCol = null, $refRow = null, $refSheet = 0)
    {
        $cellInfo = $this->cellInfo($col, $row);
        //値のセット
        $this->getSheet($sheetNo)->setCellValue($cellInfo, $value);

        //参照セルの指定がある場合には書式をコピーする
        if (!is_null($refCol) && !is_null($refRow)) {
            $this->styleCopy($col, $row, $sheetNo, $refCol, $refRow, $refSheet);
        }
    }

    /**
    * setImage
    * 画像のセット
    * @param string $img 画像のファイルパス
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param integer $height 画像の縦幅
    * @param integer $width 画像の横幅
    * @param boolean $proportial 縦横比を維持するか
    * @param integer $offsetx セルから何ピクセルずらすか（X軸)
    * @param integer $offsety セルから何ピクセルずらすか（Y軸)
    * @author hagiwara
    */
    public function setImage($img, $col, $row, $sheetNo = 0, $height = null, $width = null, $proportial = false, $offsetx = null, $offsety = null)
    {
        $cellInfo = $this->cellInfo($col, $row);

        $objDrawing = new PHPExcel_Worksheet_Drawing();//画像用のオプジェクト作成
        $objDrawing->setPath($img);//貼り付ける画像のパスを指定
        $objDrawing->setCoordinates($cellInfo);//位置
        if (!is_null($height)) {
            $objDrawing->setHeight($height);//画像の高さを指定
        }
        if (!is_null($width)) {
            $objDrawing->setWidth($width);//画像の高さを指定
        }
        if (!is_null($proportial)) {
            $objDrawing->setResizeProportional($proportial);//縦横比の変更なし
        }
        if (!is_null($offsetx)) {
            $objDrawing->setOffsetX($offsetx);//指定した位置からどれだけ横方向にずらすか。
        }
        if (!is_null($offsety)) {
            $objDrawing->setOffsetY($offsety);//指定した位置からどれだけ縦方向にずらすか。
        }
        $objDrawing->setWorksheet($this->getSheet($sheetNo));
    }

    /**
    * cellMerge
    * セルのマージ
    * @param integer $col1 行
    * @param integer $row1 列
    * @param integer $col2 行
    * @param integer $row2 列
    * @param integer $sheetNo シート番号
    * @author hagiwara
    */
    public function cellMerge($col1, $row1, $col2, $row2, $sheetNo)
    {
        $cell1Info = $this->cellInfo($col1, $row1);
        $cell2Info = $this->cellInfo($col2, $row2);

        $this->getSheet($sheetNo)->mergeCells($cell1Info . ':' . $cell2Info);
    }


    /**
    * styleCopy
    * セルの書式コピー
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param integer $refCol 参照セル行
    * @param integer $refRow 参照セル列
    * @param integer $refSheet 参照シート
    * @author hagiwara
    */
    public function styleCopy($col, $row, $sheetNo, $refCol, $refRow, $refSheet)
    {
        $cellInfo = $this->cellInfo($col, $row);
        $refCellInfo = $this->cellInfo($refCol, $refRow);
        $style = $this->getSheet($refSheet)->getStyle($refCellInfo);

        $this->getSheet($sheetNo)->duplicateStyle($style, $cellInfo);
    }

    /**
    * setStyle
    * 書式のセット(まとめて)
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param array $style スタイル情報
    * @author hagiwara
    */
    public function setStyle($col, $row, $sheetNo, $style)
    {
        $default_style = [
            'font' => null,
            'underline' => null,
            'bold' => null,
            'italic' => null,
            'strikethrough' => null,
            'color' => null,
            'size' => null,
            'alignh' => null,
            'alignv' => null,
            'bgcolor' => null,
            'bgpattern' => null,
        ];
        $style = array_merge($default_style, $style);
        $this->setFontName($col, $row, $sheetNo, $style['font']);
        $this->setUnderline($col, $row, $sheetNo, $style['underline']);
        $this->setFontBold($col, $row, $sheetNo, $style['bold']);
        $this->setItalic($col, $row, $sheetNo, $style['italic']);
        $this->setStrikethrough($col, $row, $sheetNo, $style['strikethrough']);
        $this->setColor($col, $row, $sheetNo, $style['color']);
        $this->setSize($col, $row, $sheetNo, $style['size']);
        $this->setAlignHolizonal($col, $row, $sheetNo, $style['alignh']);
        $this->setAlignVertical($col, $row, $sheetNo, $style['alignv']);
        $this->setBackgroundColor($col, $row, $sheetNo, $style['bgcolor'], $style['bgpattern']);
    }

    /**
    * setFontName
    * フォントのセット
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param string|null $fontName フォント名
    * @author hagiwara
    */
    public function setFontName($col, $row, $sheetNo, $fontName)
    {
        if (is_null($fontName)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->setName($fontName);
    }

    /**
    * setUnderline
    * 下線のセット
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param boolean|null $underline 下線を引くか
    * @author hagiwara
    */
    public function setUnderline($col, $row, $sheetNo, $underline)
    {
        if (is_null($underline)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->setUnderline($underline);
    }

    /**
    * setFontBold
    * 太字のセット
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param boolean|null $bold 太字を引くか
    * @author hagiwara
    */
    public function setFontBold($col, $row, $sheetNo, $bold)
    {
        if (is_null($bold)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->setBold($bold);
    }

    /**
    * setItalic
    * イタリックのセット
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param boolean|null $italic イタリックにするか
    * @author hagiwara
    */
    public function setItalic($col, $row, $sheetNo, $italic)
    {
        if (is_null($italic)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->setItalic($italic);
    }

    /**
    * setStrikethrough
    * 打ち消し線のセット
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param boolean|null $strikethrough 打ち消し線をつけるか
    * @author hagiwara
    */
    public function setStrikethrough($col, $row, $sheetNo, $strikethrough)
    {
        if (is_null($strikethrough)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->setStrikethrough($strikethrough);
    }

    /**
    * setColor
    * 文字の色
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param string|null $color 色(ARGB)
    * @author hagiwara
    */
    public function setColor($col, $row, $sheetNo, $color)
    {
        if (is_null($color)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->getColor()->setARGB($color);
    }

    /**
    * setSize
    * 文字サイズ
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param integer|null $size
    * @author hagiwara
    */
    public function setSize($col, $row, $sheetNo, $size)
    {
        if (is_null($size)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getFont($col, $row, $sheetNo)->setSize($size);
    }

    private function getFont($col, $row, $sheetNo)
    {
        $cellInfo = $this->cellInfo($col, $row);
        return $this->getSheet($sheetNo)->getStyle($cellInfo)->getFont();
    }

    /**
    * setAlignHolizonal
    * 水平方向のalign
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param string|null $type
    * typeはgetAlignHolizonalType参照
    * @author hagiwara
    */
    public function setAlignHolizonal($col, $row, $sheetNo, $type)
    {
        if (is_null($type)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getSheet($sheetNo)->getStyle($cellInfo)->getAlignment()->setHorizontal($this->getAlignHolizonalType($type));
    }

    /**
    * setAlignVertical
    * 垂直方法のalign
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param string|null $type
    * typeはgetAlignVerticalType参照
    * @author hagiwara
    */
    public function setAlignVertical($col, $row, $sheetNo, $type)
    {
        if (is_null($type)) {
            return;
        }
        $cellInfo = $this->cellInfo($col, $row);
        $this->getSheet($sheetNo)->getStyle($cellInfo)->getAlignment()->setVertical($this->getAlignVerticalType($type));
    }

    /**
    * setBorder
    * 罫線の設定
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param array $border
    * borderの内部はgetBorderType参照
    * @author hagiwara
    */
    public function setBorder($col, $row, $sheetNo, $border)
    {
        $cellInfo = $this->cellInfo($col, $row);
        $default_border = [
            'left' => null,
            'right' => null,
            'top' => null,
            'bottom' => null,
            'diagonal' => null,
            'all_borders' => null,
            'outline' => null,
            'inside' => null,
            'vertical' => null,
            'horizontal' => null,
        ];
        $border = array_merge($default_border, $border);
        foreach ($border as $border_position => $border_setting) {
            if (!is_null($border_setting)) {
                $borderInfo =  $this->getSheet($sheetNo)->getStyle($cellInfo)->getBorders()->{'get' . $this->camelize($border_position)}();
                if (array_key_exists('type', $border_setting)) {
                    $borderInfo->setBorderStyle($this->getBorderType($border_setting['type']));
                }
                if (array_key_exists('color', $border_setting)) {
                    $borderInfo->getColor()->setARGB($border_setting['color']);
                }
            }
        }
    }

    /**
    * setBackgroundColor
    * 背景色の設定
    * @param integer $col 行
    * @param integer $row 列
    * @param integer $sheetNo シート番号
    * @param string $color 色
    * @param string $fillType 塗りつぶし方(デフォルトsolid)
    * fillTypeの内部はgetFillType参照
    * @author hagiwara
    */
    public function setBackgroundColor($col, $row, $sheetNo, $color, $fillType = 'solid')
    {
        $cellInfo = $this->cellInfo($col, $row);

        $this->getSheet($sheetNo)->getStyle($cellInfo)->getFill()->setFillType($this->getFillType($fillType))->getStartColor()->setARGB($color);
    }

    /**
    * getBorderType
    * 罫線の種類の設定
    * @param string $type
    * @author hagiwara
    */
    private function getBorderType($type)
    {
        $type_list = self::$__borderType;
        if (array_key_exists($type, $type_list)) {
            return $type_list[$type];
        }
        return PHPExcel_Style_Border::BORDER_NONE;
    }

    /**
    * getAlignHolizonalType
    * 水平方向のAlignの設定
    * @param string $type
    * @author hagiwara
    */
    private function getAlignHolizonalType($type)
    {
        $type_list = self::$__alignHolizonalType;
        if (array_key_exists($type, $type_list)) {
            return $type_list[$type];
        }
        return PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
    }

    /**
    * getAlignVerticalType
    * 垂直方向のAlignの設定
    * @param string $type
    * @author hagiwara
    */
    private function getAlignVerticalType($type)
    {
        $type_list = self::$__alignVerticalType;
        if (array_key_exists($type, $type_list)) {
            return $type_list[$type];
        }
        return null;
    }

    /**
    * getFillType
    * 塗りつぶしの設定
    * @param string $type
    * @author hagiwara
    */
    private function getFillType($type)
    {
        $type_list = self::$__fillType;
        if (array_key_exists($type, $type_list)) {
            return $type_list[$type];
        }
        return PHPExcel_Style_Fill::FILL_SOLID;
    }

    /**
    * createSheet
    * シートの作成
    * @param string $name
    * @author hagiwara
    */
    public function createSheet($name = null)
    {
        //シートの新規作成
        $newSheet = $this->__phpexcel->createSheet();
        $sheetNo = $this->__phpexcel->getIndex($newSheet);
        $this->__sheet[$sheetNo] = $newSheet;
        if (!is_null($name)) {
            $this->renameSheet($sheetNo, $name);
        }
    }

    /**
    * deleteSheet
    * シートの削除
    * @param integer $sheetNo
    * @author hagiwara
    */
    public function deleteSheet($sheetNo)
    {
        //シートの削除は一番最後に行う
        $this->__deleteSheetList[] = $sheetNo;
    }

    /**
    * copySheet
    * シートのコピー
    * @param integer $sheetNo
    * @param integer $position
    * @param string $name
    * @author hagiwara
    */
    public function copySheet($sheetNo, $position = null, $name = null)
    {
        $base = $this->getSheet($sheetNo)->copy();
        if ($name === null) {
            $name = uniqid();
        }
        $base->setTitle($name);

        // $positionが null(省略時含む)の場合は最後尾に追加される
        $this->__phpexcel->addSheet($base, $position);
    }

    /**
    * renameSheet
    * シート名の変更
    * @param integer $sheetNo
    * @param string $name
    * @author hagiwara
    */
    public function renameSheet($sheetNo, $name)
    {
        $this->__sheet[$sheetNo]->setTitle($name);
    }

    /**
    * write
    * xlsxファイルの書き込み
    * @param string $file 書き込み先のファイルパス
    * @author hagiwara
    */
    public function write($file, $type = 'Excel2007')
    {
        //書き込み前に削除シートを削除する
        foreach ($this->__deleteSheetList as $deleteSheet) {
            $this->__phpexcel->removeSheetByIndex($deleteSheet);
        }
        $writer = PHPExcel_IOFactory::createWriter($this->__phpexcel, $type);
        $writer->save($file);
    }

    /**
    * getReader
    * readerを返す(※直接PHPExcelの関数を実行できるように)
    * @author hagiwara
    */
    public function getReader()
    {
        return $this->__phpexcel;
    }

    /**
    * getSheet
    * シート情報の読み込み
    * @param integer $sheetNo シート番号
    * @author hagiwara
    * @return null|\PHPExcel_Worksheet
    */
    private function getSheet($sheetNo)
    {
        if (!array_key_exists($sheetNo, $this->__sheet)) {
            $this->__sheet[$sheetNo] = $this->__phpexcel->setActiveSheetIndex(0);
        }
        return $this->__sheet[$sheetNo];
    }

    /**
    * cellInfo
    * R1C1参照をA1参照に変換して返す
    * @param integer $col 行
    * @param integer $row 列
    * @author hagiwara
    */
    private function cellInfo($col, $row)
    {
        $stringCol = PHPExcel_Cell::stringFromColumnIndex($col);
        return $stringCol . $row;
    }

    /**
    * cellInfo
    * http://qiita.com/Hiraku/items/036080976884fad1e450
    * @param string $str
    */
    private function camelize($str)
    {
        $str = ucwords($str, '_');
        return str_replace('_', '', $str);
    }
}
