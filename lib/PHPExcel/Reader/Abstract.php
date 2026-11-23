<?php

/**
 * PHPExcel_Reader_Abstract
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */
abstract class PHPExcel_Reader_Abstract implements PHPExcel_Reader_IReader
{
    /**
     * Read data only?
     * Identifies whether the Reader should only read data values for cells, and ignore any formatting information;
     *        or whether it should read both data and formatting
     *
     * @var    boolean
     */
    protected $readDataOnly = false;

    /**
     * Read empty cells?
     * Identifies whether the Reader should read data values for cells all cells, or should ignore cells containing
     *         null value or empty string
     *
     * @var    boolean
     */
    protected $readEmptyCells = true;

    /**
     * Read charts that are defined in the workbook?
     * Identifies whether the Reader should read the definitions for any charts that exist in the workbook;
     *
     * @var    boolean
     */
    protected $includeCharts = false;

    /**
     * Restrict which sheets should be loaded?
     * This property holds an array of worksheet names to be loaded. If null, then all worksheets will be loaded.
     *
     * @var array of string
     */
    protected $loadSheetsOnly;

    /**
     * PHPExcel_Reader_IReadFilter instance
     *
     * @var PHPExcel_Reader_IReadFilter
     */
    protected $readFilter;

    protected $fileHandle = null;


    /**
     * Read data only?
     *        If this is true, then the Reader will only read data values for cells, it will not read any formatting information.
     *        If false (the default) it will read data and formatting.
     *
     * @return    boolean
     */
    public function getReadDataOnly()
    {
        return $this->readDataOnly;
    }

    /**
     * Set read data only
     *        Set to true, to advise the Reader only to read data values for cells, and to ignore any formatting information.
     *        Set to false (the default) to advise the Reader to read both data and formatting for cells.
     *
     * @param    boolean    $pValue
     *
     * @return    PHPExcel_Reader_IReader
     */
    public function setReadDataOnly($pValue = false)
    {
        $this->readDataOnly = $pValue;
        return $this;
    }

    /**
     * Read empty cells?
     *        If this is true (the default), then the Reader will read data values for all cells, irrespective of value.
     *        If false it will not read data for cells containing a null value or an empty string.
     *
     * @return    boolean
     */
    public function getReadEmptyCells()
    {
        return $this->readEmptyCells;
    }

    /**
     * Set read empty cells
     *        Set to true (the default) to advise the Reader read data values for all cells, irrespective of value.
     *        Set to false to advise the Reader to ignore cells containing a null value or an empty string.
     *
     * @param    boolean    $pValue
     *
     * @return    PHPExcel_Reader_IReader
     */
    public function setReadEmptyCells($pValue = true)
    {
        $this->readEmptyCells = $pValue;
        return $this;
    }

    /**
     * Read charts in workbook?
     *        If this is true, then the Reader will include any charts that exist in the workbook.
     *      Note that a ReadDataOnly value of false overrides, and charts won't be read regardless of the IncludeCharts value.
     *        If false (the default) it will ignore any charts defined in the workbook file.
     *
     * @return    boolean
     */
    public function getIncludeCharts()
    {
        return $this->includeCharts;
    }

    /**
     * Set read charts in workbook
     *        Set to true, to advise the Reader to include any charts that exist in the workbook.
     *      Note that a ReadDataOnly value of false overrides, and charts won't be read regardless of the IncludeCharts value.
     *        Set to false (the default) to discard charts.
     *
     * @param    boolean    $pValue
     *
     * @return    PHPExcel_Reader_IReader
     */
    public function setIncludeCharts($pValue = false)
    {
        $this->includeCharts = (boolean) $pValue;
        return $this;
    }

    /**
     * Get which sheets to load
     * Returns either an array of worksheet names (the list of worksheets that should be loaded), or a null
     *        indicating that all worksheets in the workbook should be loaded.
     *
     * @return mixed
     */
    public function getLoadSheetsOnly()
    {
        return $this->loadSheetsOnly;
    }

    /**
     * Set which sheets to load
     *
     * @param mixed $value
     *        This should be either an array of worksheet names to be loaded, or a string containing a single worksheet name.
     *        If NULL, then it tells the Reader to read all worksheets in the workbook
     *
     * @return PHPExcel_Reader_IReader
     */
    public function setLoadSheetsOnly($value = null)
    {
        if ($value === null) {
            return $this->setLoadAllSheets();
        }

        $this->loadSheetsOnly = is_array($value) ? $value : array($value);
        return $this;
    }

    /**
     * Set all sheets to load
     *        Tells the Reader to load all worksheets from the workbook.
     *
     * @return PHPExcel_Reader_IReader
     */
    public function setLoadAllSheets()
    {
        $this->loadSheetsOnly = null;
        return $this;
    }

    /**
     * Read filter
     *
     * @return PHPExcel_Reader_IReadFilter
     */
    public function getReadFilter()
    {
        return $this->readFilter;
    }

    /**
     * Set read filter
     *
     * @param PHPExcel_Reader_IReadFilter $pValue
     * @return PHPExcel_Reader_IReader
     */
    public function setReadFilter(PHPExcel_Reader_IReadFilter $pValue)
    {
        $this->readFilter = $pValue;
        return $this;
    }

    /**
     * Open file for reading
     *
     * @param string $pFilename
     * @throws    PHPExcel_Reader_Exception
     * @return resource
     */
    protected function openFile($pFilename)
    {
        // Check if file exists
        if (!file_exists($pFilename) || !is_readable($pFilename)) {
            throw new PHPExcel_Reader_Exception("Could not open " . $pFilename . " for reading! File does not exist.");
        }

        // Open file
        $this->fileHandle = fopen($pFilename, 'r');
        if ($this->fileHandle === false) {
            throw new PHPExcel_Reader_Exception("Could not open file " . $pFilename . " for reading.");
        }
    }

    /**
     * Can the current PHPExcel_Reader_IReader read the file?
     *
     * @param     string         $pFilename
     * @return boolean
     * @throws PHPExcel_Reader_Exception
     */
    public function canRead($pFilename)
    {
        // Check if file exists
        try {
            $this->openFile($pFilename);
        } catch (Exception $e) {
            return false;
        }

        $readable = $this->isValidFormat();
        fclose($this->fileHandle);
        return $readable;
    }

    /**
     * Scan theXML for use of <!ENTITY to prevent XXE/XEE attacks
     *
     * @param     string         $xml
     * @throws PHPExcel_Reader_Exception
     */
    public function securityScan($xml)
    {
        $pattern = '/\\0?' . implode('\\0?', str_split('<!DOCTYPE')) . '\\0?/';
        if (preg_match($pattern, $xml)) {
            throw new PHPExcel_Reader_Exception('Detected use of ENTITY in XML, spreadsheet file load() aborted to prevent XXE/XEE attacks');
        }
        // gnuboard KVE-2026-3262: the pattern above only tolerates NUL bytes between the letters,
        // while libxml decodes the declared or detected encoding (ISO-2022 escape sequences,
        // UTF-32, EBCDIC ...) before it sees the DOCTYPE. Normalise to UTF-8 first and refuse
        // any encoding that cannot be inspected reliably.
        $normalised = str_replace("\0", '', self::securityScanNormalise($xml));
        if (preg_match('/<!\s*(DOCTYPE|ENTITY)/i', $normalised)) {
            throw new PHPExcel_Reader_Exception('Detected use of ENTITY in XML, spreadsheet file load() aborted to prevent XXE/XEE attacks');
        }
        return $xml;
    }

    /**
     * gnuboard KVE-2026-3262: return the XML as UTF-8 text for scanning.
     * Only UTF-8 (or ASCII) and UTF-16 documents are accepted.
     *
     * @param     string         $xml
     * @return    string
     * @throws PHPExcel_Reader_Exception
     */
    protected static function securityScanNormalise($xml)
    {
        $xml = (string) $xml;
        // ESC is not a valid XML character; it only appears in ISO-2022 style shift sequences.
        if (strpos($xml, "\x1b") !== false) {
            throw new PHPExcel_Reader_Exception('Detected escape sequence in XML, spreadsheet file load() aborted');
        }
        $head4 = substr($xml, 0, 4);
        $unsupported = array("\x00\x00\xFE\xFF", "\xFF\xFE\x00\x00", "\x00\x00\x00\x3C", "\x3C\x00\x00\x00",
            "\x00\x00\x3C\x00", "\x00\x3C\x00\x00", "\x4C\x6F\xA7\x94", "\x2B\x2F\x76");
        if (in_array($head4, $unsupported, true) || in_array(substr($xml, 0, 3), $unsupported, true)) {
            throw new PHPExcel_Reader_Exception('Unsupported XML encoding, spreadsheet file load() aborted');
        }
        $head2 = substr($xml, 0, 2);
        $from = '';
        if ($head2 === "\xFF\xFE" || $head2 === "\x3C\x00") {
            $from = 'UTF-16LE';
        } elseif ($head2 === "\xFE\xFF" || $head2 === "\x00\x3C") {
            $from = 'UTF-16BE';
        }
        if ($from !== '') {
            if (function_exists('mb_convert_encoding')) {
                $xml = mb_convert_encoding($xml, 'UTF-8', $from);
            } elseif (function_exists('iconv')) {
                $xml = @iconv($from, 'UTF-8//IGNORE', $xml);
            } else {
                throw new PHPExcel_Reader_Exception('Cannot inspect UTF-16 XML, spreadsheet file load() aborted');
            }
            if (!is_string($xml)) {
                throw new PHPExcel_Reader_Exception('Invalid UTF-16 XML, spreadsheet file load() aborted');
            }
        }
        if (substr($xml, 0, 3) === "\xEF\xBB\xBF") {
            $xml = substr($xml, 3);
        }
        if (preg_match('/^\s*<\?xml\s([^>]*)>/', $xml, $declaration)
            && preg_match('/encoding\s*=\s*["\']\s*([^"\']*)["\']/i', $declaration[1], $encoding)) {
            $encoding = strtoupper(trim($encoding[1]));
            $allowed = ($from === '') ? array('UTF-8', 'UTF8', 'US-ASCII', 'ASCII') : array('UTF-16', 'UTF-16LE', 'UTF-16BE');
            if (!in_array($encoding, $allowed, true)) {
                throw new PHPExcel_Reader_Exception('Unsupported XML encoding, spreadsheet file load() aborted');
            }
        }
        return $xml;
    }

    /**
     * Scan theXML for use of <!ENTITY to prevent XXE/XEE attacks
     *
     * @param     string         $filestream
     * @throws PHPExcel_Reader_Exception
     */
    public function securityScanFile($filestream)
    {
        return $this->securityScan(file_get_contents($filestream));
    }
}
