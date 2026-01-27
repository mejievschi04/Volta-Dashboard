<?php
/**
 * SimpleXLSX php class v0.9.38
 * MS Excel 2007 workbooks reader
 *
 * Copyright (c) 2012 - 2024 SimpleXLSX
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the MIT Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @category   SimpleXLSX
 * @package    SimpleXLSX
 * @copyright  Copyright (c) 2012 - 2024
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.9.38
 */

/**
 * SimpleXLSX class
 * @package SimpleXLSX
 */
class SimpleXLSX {
    // Don't remove this string! Created by Sergey Schuchkin from http://www.sibvision.ru - professional PHP developers team 2012-2024
    public static $CF = array( // Cell formats
        0  => 'General',
        1  => '0',
        2  => '0.00',
        3  => '#,##0',
        4  => '#,##0.00',
        9  => '0%',
        10 => '0.00%',
        11 => '0.00E+00',
        12 => '# ?/?',
        13 => '# ??/??',
        14 => 'mm-dd-yy',
        15 => 'd-mmm-yy',
        16 => 'd-mmm',
        17 => 'mmm-yy',
        18 => 'h:mm AM/PM',
        19 => 'h:mm:ss AM/PM',
        20 => 'h:mm',
        21 => 'h:mm:ss',
        22 => 'm/d/yy h:mm',
        37 => '#,##0 ;(#,##0)',
        38 => '#,##0 ;[Red](#,##0)',
        39 => '#,##0.00;(#,##0.00)',
        40 => '#,##0.00;[Red](#,##0.00)',
        44 => '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)',
        45 => 'mm:ss',
        46 => '[h]:mm:ss',
        47 => 'mmss.0',
        48 => '##0.0E+0',
        49 => '@',
        27 => '[$-404]e/m/d',
        30 => 'm/d/yy',
        36 => '[$-404]e/m/d',
        50 => '[$-404]e/m/d',
        57 => '[$-404]e/m/d',
        59 => 't0',
        60 => 't0.00',
        61 => 't#,##0',
        62 => 't#,##0.00',
        67 => 't0%',
        68 => 't0.00%',
        69 => 't# ?/?',
        70 => 't# ??/??'
    );

    public $cellFormats = array();
    public $datetimeFormat = 'Y-m-d H:i:s';
    public $debug;

    protected $workbook;
    protected $sheetNames = array();
    public $sheets = array();
    protected $sheetFiles = array();
    protected $styles = array();

    public function __construct( $filename = null, $is_data = null, $debug = false ) {
        if ( $filename !== null && $is_data !== null ) {
            $this->parse( $filename, $is_data, $debug );
        }
        $this->debug = $debug;
    }

    public static function parse( $filename, $is_data = false, $debug = false ) {
        $xlsx = new self();
        $xlsx->debug = $debug;
        $result = $xlsx->parseFile( $filename, $is_data );
        // Returnează obiectul dacă parseFile() a reușit (true), sau false dacă a eșuat
        return $result === true ? $xlsx : false;
    }

    protected function parseFile( $filename, $is_data = false ) {
        if ( ! $is_data && ! is_readable( $filename ) ) {
            if ( $this->debug ) {
                trigger_error( 'SimpleXLSX: file not found ' . $filename, E_USER_WARNING );
            }
            return false;
        }

        $this->sheets  = array();
        $this->sheetNames = array();

        // Read main rels file
        $data = $is_data ? $filename : file_get_contents( $filename );
        if ( empty( $data ) ) {
            if ( $this->debug ) {
                trigger_error( 'SimpleXLSX: empty file', E_USER_WARNING );
            }
            return false;
        }

        // Verifică dacă este fișier ZIP (format .xlsx)
        if ( !$is_data && file_exists( $filename ) ) {
            $header = file_get_contents( $filename, false, null, 0, 4 );
            // Fișierele .xlsx sunt arhive ZIP care încep cu "PK"
            if ( substr( $header, 0, 2 ) !== "PK" ) {
                if ( $this->debug ) {
                    trigger_error( 'SimpleXLSX: Fișierul nu este un fișier ZIP valid. Fișierele .xlsx sunt arhive ZIP.', E_USER_WARNING );
                }
                return false;
            }
        }

        $zip = new ZipArchive();
        $zipResult = $zip->open( $is_data ? 'data://text/plain;base64,' . base64_encode( $data ) : $filename );
        if ( $zipResult !== true ) {
            if ( $this->debug ) {
                $errorMessages = [
                    ZipArchive::ER_OK => 'No error',
                    ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
                    ZipArchive::ER_RENAME => 'Renaming temporary file failed',
                    ZipArchive::ER_CLOSE => 'Closing zip archive failed',
                    ZipArchive::ER_SEEK => 'Seek error',
                    ZipArchive::ER_READ => 'Read error',
                    ZipArchive::ER_WRITE => 'Write error',
                    ZipArchive::ER_CRC => 'CRC error',
                    ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
                    ZipArchive::ER_NOENT => 'No such file',
                    ZipArchive::ER_EXISTS => 'File already exists',
                    ZipArchive::ER_OPEN => 'Can\'t open file',
                    ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
                    ZipArchive::ER_ZLIB => 'Zlib error',
                    ZipArchive::ER_MEMORY => 'Memory allocation failure',
                    ZipArchive::ER_CHANGED => 'Entry has been changed',
                    ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
                    ZipArchive::ER_EOF => 'Premature EOF',
                    ZipArchive::ER_INVAL => 'Invalid argument',
                    ZipArchive::ER_NOZIP => 'Not a zip archive',
                    ZipArchive::ER_INTERNAL => 'Internal error',
                    ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                    ZipArchive::ER_REMOVE => 'Can\'t remove file',
                    ZipArchive::ER_DELETED => 'Entry has been deleted'
                ];
                $errorMsg = isset($errorMessages[$zipResult]) ? $errorMessages[$zipResult] : 'Unknown error';
                trigger_error( 'SimpleXLSX: failed to open zip (error code: ' . $zipResult . ' - ' . $errorMsg . ')', E_USER_WARNING );
            }
            return false;
        }

        if ( ( $index = $zip->locateName( '_rels/.rels' ) ) === false ) {
            if ( $this->debug ) {
                trigger_error( 'SimpleXLSX: format not recognized', E_USER_WARNING );
            }
            return false;
        }

        $data = $zip->getFromIndex( $index );
        if ( ( $wb = $zip->locateName( 'xl/workbook.xml' ) ) === false ) {
            if ( $this->debug ) {
                trigger_error( 'SimpleXLSX: workbook not found', E_USER_WARNING );
            }
            return false;
        }
        $this->workbook = $zip->getFromIndex( $wb );
        if ( ( $ws = $zip->locateName( 'xl/_rels/workbook.xml.rels' ) ) === false ) {
            if ( $this->debug ) {
                trigger_error( 'SimpleXLSX: workbook rels not found', E_USER_WARNING );
            }
            return false;
        }

        $strings_file = $zip->locateName( 'xl/sharedStrings.xml' );
        if ( $strings_file === false ) {
            $this->sharedstrings = array();
        } else {
            $this->sharedstrings = $this->parse_shared_strings( $zip->getFromIndex( $strings_file ) );
        }

        $styles_file = $zip->locateName( 'xl/styles.xml' );
        if ( $styles_file !== false ) {
            $this->styles = $this->parseStyles( $zip->getFromIndex( $styles_file ) );
        }

        $workbookRels = simplexml_load_string( $zip->getFromIndex( $ws ) );
        foreach ( $workbookRels->Relationship as $rel ) {
            $type = basename( trim( (string) $rel['Type'] ) );
            $target = (string) $rel['Target'];
            if ( $type === 'worksheet' ) {
                $this->sheetFiles[ (string) $rel['Id'] ] = 'xl/' . $target;
            }
        }

        $workbook = simplexml_load_string( $this->workbook );
        foreach ( $workbook->sheets->sheet as $sheet ) {
            $this->sheetNames[] = (string) $sheet['name'];
        }

        foreach ( $this->sheetFiles as $id => $file ) {
            $index = $zip->locateName( $file );
            if ( $index !== false ) {
                $this->sheets[] = $this->parseSheet( $zip->getFromIndex( $index ) );
            }
        }

        $zip->close();

        return true;
    }

    protected function parseSheet( $data ) {
        $sheet = simplexml_load_string( $data );
        $rows = array();
        if (isset($sheet->sheetData->row)) {
            foreach ( $sheet->sheetData->row as $row ) {
                $cells = array();
                $rowNum = (int)$row['r'];
                
                // Procesează celulele cu index-ul lor
                $rowCells = array();
                foreach ( $row->c as $c ) {
                    // Extrage indexul coloanei din atributul 'r' (ex: "A1" -> coloana 0)
                    $cellRef = (string)$c['r'];
                    if (preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches)) {
                        $colIndex = $this->columnIndex($matches[1]);
                        $value = $this->parseCell( $c );
                        $rowCells[$colIndex] = $value;
                    } else {
                        // Dacă nu are referință, adaugă la final
                        $rowCells[] = $this->parseCell( $c );
                    }
                }
                
                // Convertim array-ul indexat în array secvențial
                if (!empty($rowCells)) {
                    $maxIndex = max(array_keys($rowCells));
                    for ($i = 0; $i <= $maxIndex; $i++) {
                        $cells[] = isset($rowCells[$i]) ? $rowCells[$i] : '';
                    }
                }
                
                $rows[] = $cells;
            }
        }
        return $rows;
    }

    protected function parseCell( $cell ) {
        // Verifică dacă celula are valoare
        if ( !isset( $cell->v ) ) {
            return '';
        }
        
        $value = (string) $cell->v;
        
        // Dacă tipul este 's' (shared string), obține valoarea din shared strings
        if ( isset( $cell['t'] ) && (string)$cell['t'] == 's' ) {
            $index = (int) $value;
            if ( isset( $this->sharedstrings[ $index ] ) ) {
                $value = $this->sharedstrings[ $index ];
            } else {
                $value = '';
            }
        } elseif ( isset( $cell['t'] ) && (string)$cell['t'] == 'inlineStr' ) {
            // Valoare inline (direct în celulă)
            if ( isset( $cell->is->t ) ) {
                $value = (string) $cell->is->t;
            }
        }
        
        return $value;
    }

    protected function parse_shared_strings( $data ) {
        $strings = array();
        $xml = simplexml_load_string( $data );
        foreach ( $xml->si as $si ) {
            $string = '';
            if ( isset( $si->t ) ) {
                $string = (string) $si->t;
            } elseif ( isset( $si->r ) ) {
                foreach ( $si->r as $r ) {
                    $string .= (string) $r->t;
                }
            }
            $strings[] = $string;
        }
        return $strings;
    }

    protected function parseStyles( $data ) {
        // Simplified - can be expanded
        return array();
    }

    public function rows( $worksheetIndex = 0 ) {
        if ( isset( $this->sheets[ $worksheetIndex ] ) ) {
            return $this->sheets[ $worksheetIndex ];
        }
        return array();
    }

    public function rowsEx( $worksheetIndex = 0 ) {
        // Extended version - can be implemented
        return $this->rows( $worksheetIndex );
    }

    public function sheets() {
        return $this->sheetNames;
    }

    public function sheetsCount() {
        return count( $this->sheets );
    }

    public function sheetName( $worksheetIndex ) {
        if ( isset( $this->sheetNames[ $worksheetIndex ] ) ) {
            return $this->sheetNames[ $worksheetIndex ];
        }
        return false;
    }

    public function sheetNames() {
        return $this->sheetNames;
    }

    public function rowsCount() {
        return count( $this->sheets[0] );
    }

    public function getCell( $worksheetIndex, $cell ) {
        if ( isset( $this->sheets[ $worksheetIndex ] ) ) {
            $rows = $this->sheets[ $worksheetIndex ];
            if ( preg_match( '/^([A-Z]+)(\d+)$/', $cell, $matches ) ) {
                $col = $this->columnIndex( $matches[1] );
                $row = (int) $matches[2] - 1;
                if ( isset( $rows[ $row ][ $col ] ) ) {
                    return $rows[ $row ][ $col ];
                }
            }
        }
        return false;
    }

    protected function columnIndex( $col ) {
        $index = 0;
        for ( $i = 0; $i < strlen( $col ); $i++ ) {
            $index = $index * 26 + ord( $col[ $i ] ) - ord( 'A' ) + 1;
        }
        return $index - 1;
    }
}

