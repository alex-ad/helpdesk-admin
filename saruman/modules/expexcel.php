<?php
session_start();
include_once '../lib/PHPExcel.php';

class Tools_Grid2Excel
{
    public function getExcelFile($storeData, $headerData = array(), $reportName = 'Exported Report')
    {
        // создание объекта Excell
        $php_excel = new PHPExcel();

        // установка базовых параметров
        $properties = $php_excel->getProperties();
        $php_excel->setProperties($properties);
        $rowDataInsertIndex = 1;

        // выбираем активны й лист
        $php_excel->setActiveSheetIndex(0);

        // определяем формат листа
        $php_excel->getActiveSheet()->getPageSetup()->setOrientation(
            PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE
        );
        $php_excel->getActiveSheet()->getPageSetup()->setPaperSize(
            PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4
        );

        // заполнение листа данными
        $sheet = $php_excel->getActiveSheet();

        // параметры заголовка
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->setCellValue('A' . $rowDataInsertIndex, $reportName);
        $sheet->getStyle('A' . $rowDataInsertIndex)->getAlignment()->setWrapText(
            true
        ); // для использования перевода строки "\n"
        $rowDataInsertIndex++;

        // название листа
        $sheet->setTitle('Лист1');
        // параметры листа
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
        // параметры ячеек
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getDefaultRowDimension()->setRowHeight(16);
        $sheet->getDefaultColumnDimension()->setWidth(16);

        /* вставляем заголовок в лист */
        if (!empty($headerData)) {
            $sheet->fromArray($headerData, null, 'A' . $rowDataInsertIndex, true);
            $rowDataInsertIndex++;
        }

        /* вставляем данные отчёта на лист */
        $sheet->fromArray($storeData, null, 'A' . $rowDataInsertIndex, true);

        /* уменьшение индекса вствки для использования в дальнейшем форматировании */
        $rowDataInsertIndex--;

        // определение максимальных размеров документа
        $max_col = $sheet->getHighestColumn();
        $max_row = $sheet->getHighestRow();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($max_col);

        // применение форматирование рамки
        $sheet->getStyle("A$rowDataInsertIndex:" . $max_col . $max_row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );
        // выравнивание по горизонтали и вертикали всех
        $sheet->getStyle("A$rowDataInsertIndex:" . $max_col . $max_row)->getAlignment()
            ->setVertical(
                PHPExcel_Style_Alignment::VERTICAL_CENTER
            );
        $sheet->getStyle("A$rowDataInsertIndex:" . $max_col . $max_row)->getAlignment()
            ->setHorizontal(
                PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            );
        // заливка шапки сереньким (D8D8D8D8)
        $sheet->getStyle("A$rowDataInsertIndex:" . $max_col . $rowDataInsertIndex)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true,
                ),
                'fill' => array(
                    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array(
                        'argb' => 'D8D8D8D8',
                    ),
                )
            )
        );

        // объединение ячеек заголовка
        $sheet->mergeCells("A1:" . $max_col . '1');
        // выравнивание по горизонтали и вертикали заголовка
        $sheet->getStyle("A1:" . $max_col . '1')->getAlignment()
            ->setVertical(
                PHPExcel_Style_Alignment::VERTICAL_CENTER
            );
        $sheet->getStyle("A1:" . $max_col . '1')->getAlignment()
            ->setHorizontal(
                PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            );

        /* автоширина */
        for ($col = 'A'; $col <= $max_col; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }


        // вывод файла
        $writer = PHPExcel_IOFactory::createWriter($php_excel, 'Excel5');
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

}

/* обработка входящих параметров и создание файла */
if (!empty($_REQUEST['jsonData'])) {
    /* инициализация PHPEXCEL */
    $g2excel = new Tools_Grid2Excel();

    /* кодирование имени файла, для сохранения в сессии */
    $fileKey = md5($_REQUEST['jsonData']);

    /* сохранение имени файла в сессии */
    if (!empty($_REQUEST['reportName'])) {
        $_SESSION['gridDataExcelFile'][$fileKey]['name']
            = preg_replace('/\s/', '_', $_REQUEST['reportName'])
            . '.xls';
        $reportName = $_REQUEST['reportName'];
    } else {
        $_SESSION['gridDataExcelFile'][$fileKey]['name'] = 'Report.xls';
        $reportName = null;
    }

    /* проверка на наличие заголовка */
    if (!empty($_REQUEST['headersArray'])) {
        $headersArray = json_decode($_REQUEST['headersArray'], true);
    } else {
        $headersArray = array();
    }

    /* сохранение данных в сессии */
    $_SESSION['gridDataExcelFile'][$fileKey]['data'] = $g2excel->getExcelFile(
        json_decode($_REQUEST['jsonData'], true),
        $headersArray,
        $reportName
    );

    /* добавление в результаты работы скрипта поля с именем файла в сессии */
    header('Content-Type: application/json');
    echo json_encode(array('fileKey'=>$fileKey));
} elseif (!empty($_REQUEST['fileKey'])) {
    header('Content-Type: application/vnd.ms-excel');
    header(
        'Content-Disposition: attachment;filename="'
        . $_SESSION['gridDataExcelFile'][$_REQUEST['fileKey']]['name'] . '"'
    );
    header('Cache-Control: max-age=0');
    echo $_SESSION['gridDataExcelFile'][$_REQUEST['fileKey']]['data'];
    unset($_SESSION['gridDataExcelFile'][$_REQUEST['fileKey']]);
}