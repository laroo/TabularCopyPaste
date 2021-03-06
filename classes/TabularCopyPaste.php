<?php

class TabularData {

    /**
     * @var array
     */
    public $aColumns = array();

    /**
     * @var array
     */
    public $aData = array();

    /**
     * @param $sData
     * @param bool $bHeaderIncluded
     * @throws Exception
     */
    public function loadByString($sData, $bHeaderIncluded = TRUE) {

        // Detect newline
        $aRows = explode($this->_detectNewlineCharacter($sData), $sData);

        // Detect column separator by using first 15 rows
        $sSeparator = $this->_detectColumnSeparator(array_slice($aRows, 1, 15));

        $this->aData = array();
        $bHeaderDetected = false;
        foreach ($aRows as $iRowCnt => $sRow) {
            if (trim($sRow) == '') continue;
            $aRawColumns = explode($sSeparator, $sRow);
            if (!$bHeaderDetected) {
                $this->aColumns = array();
                foreach ($aRawColumns as $iRawIndex => $sRawColumnName) {
                    $sRawColumnName = trim($sRawColumnName);
                    // Check if column-name already exists
                    $sComment = null;
                    foreach ($this->aColumns as $iIndex => $oColumn) {
                        /** @var $oColumn TabularColumn */
                        if ($sRawColumnName == $oColumn->originalName) {
                            $sRawColumnName = $sRawColumnName.'1';
                            $sComment = 'Renamed because of duplicate column name at index '.$iIndex.'.';
                        }
                    }
                    
                    $this->aColumns[$iRawIndex] = new TabularColumn($sRawColumnName);
                    $this->aColumns[$iRawIndex]->comment = $sComment;
                }
                $bHeaderDetected = true;
            } else {
                // Normal row
                $this->aData[] = $aRawColumns;
            }
        }

        if (count($this->aData) == 0) {
            throw new Exception('No data found!');
        }

        $this->_detectDataTypeColumns();

    }

    /**
     * Detecteer UTF-8 encoding.
     *
     * Source: http://stackoverflow.com/questions/3542818/remove-accents-without-using-iconv
     * @param string $str
     * @return bool
     */
    protected static function detect_utf8($str)
    {
        $length = strlen($str);
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; # 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; # Does not match any model
            for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * Source: http://stackoverflow.com/questions/3542818/remove-accents-without-using-iconv
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    public static function remove_accents($string)
    {
        if ( !preg_match('/[\x80-\xff]/', $string) )
        {
            return $string;
        }

        if (self::detect_utf8($string))
        {
            $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
            // Euro Sign
            chr(226).chr(130).chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194).chr(163) => '');

            $string = strtr($string, $chars);
        }
        else
        {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                .chr(252).chr(253).chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    /**
     * @param $sColumnName
     * @param string $psSubstituteChar
     * @return string
     */
    static public function _getFriendlyColumnName($sColumnName, $psSubstituteChar = '_') {
        $sString = self::remove_accents($sColumnName);
        $sString = preg_replace('/[^_\-a-z0-9]+/i', $psSubstituteChar, trim($sString));
        $sString = trim($sString, $psSubstituteChar);
        return strtolower($sString);
    }

    /**
     * Detect new line character
     *
     * @param $sData
     * @return string
     */
    public function _detectNewlineCharacter($sData) {
        $iNumNewLines = substr_count($sData, chr(10));
        $iNumCarriageReturn = substr_count($sData, chr(13));
        if ($iNumNewLines == $iNumCarriageReturn) {
            return chr(13).chr(10); // Windows
        } elseif ($iNumCarriageReturn > 0 && $iNumNewLines == 0) {
            return chr(13); // Mac
        }
        return chr(10); // Unix
    }

    function _math_standard_deviation($x) {
        $summation = 0;
        $values = 0;
        foreach ($x as $value) {
            if (is_numeric($value)) {
                $summation = $summation + $value;
                $values++;
            }
        }
        $mean = $summation/$values;
        $ex2 = 0;
        foreach ($x as $value) {
            if (is_numeric($value)) {
                $ex2 = $ex2 + ($value*$value);
            }
        }
        $rawsd = ($ex2/$values) - ($mean * $mean);
        $sd = sqrt($rawsd);
        return $sd;
    }

    /**
     * Detect column seperator
     * 
     * FIXME Not implemented
     *
     * @param $aSampleRows
     * @return string
     */
    public function _detectColumnSeparator($aSampleRows) {

        $aChars = array(
            'tab' => chr(10),
            'comma' => ',',
            'semicolon' => ';',
            'space' => ' ',
        );

        $aCnt = array();
        foreach ($aChars as $sCharName => $sChar) {
            $aCnt[$sCharName] = array();
        }

        foreach ($aSampleRows as $sRow) {
            foreach ($aChars as $sCharName => $sChar) {
                $iCnt = substr_count($sRow, $sChar);
                if ($iCnt == 0) $iCnt = null;
                $aCnt[$sCharName][] = $iCnt;
            }
        }

        $aTop = array();
        foreach ($aChars as $sCharName => $sChar) {
            if (array_sum($aCnt[$sCharName]) == 0) {
                continue;
            }
            $fStdDev = $this->_math_standard_deviation($aCnt[$sCharName]);
            // No difference is 0. Take the char with the least deviation
            $aTop[$sCharName] = $fStdDev;
        }

        if (count($aTop) == 0) {
            return "\t"; // Fallback
        }

        asort($aTop);

        reset($aTop);
        $sWinner = key($aTop);
        return $aChars[$sWinner];
    }

    /**
     * Detect data type columns
     */
    public function _detectDataTypeColumns() {
        $aSampleData = array_slice($this->aData, 0, 20);
        foreach($this->aColumns as $iIndex => $oColumn) {
            /** @var $oColumn TabularColumn */
            $this->_detectDataTypeColumn_bySampleData($oColumn, array_column($aSampleData, $iIndex));
        }
    }

    /**
     * @param TabularColumn $poColumn
     * @param array $paSampleData
     * @throws Exception
     */
    public function _detectDataTypeColumn_bySampleData(TabularColumn $poColumn, array $paSampleData) {

        $bIsInteger = false;
        $bIsDecimal = false;
        $bIsString = false;
        $iSamples = count($paSampleData);
        $iEmptyCount = 0;
        foreach ($paSampleData as $sSample) {
            
            // Do not sample empty values
            if (trim($sSample) == '') {
                $iEmptyCount++;
                continue;
            }

            $bIsInteger = (bool)preg_match('/^[0-9]+$/', $sSample);
            if ($bIsInteger) {
                continue;
            }

            $bIsDecimal = (bool)preg_match('/^[0-9]+(\.[0-9]+)?$/', $sSample);
            if ($bIsDecimal) {
                continue;
            }

            $bIsString = true;
        }

        if (TRUE === $bIsInteger && FALSE === $bIsString) {
            $poColumn->datatype = new TabularColumnDataTypeInteger();
        } elseif (TRUE === $bIsDecimal && FALSE === $bIsString) {
            $poColumn->datatype = new TabularColumnDataTypeDecimal();
        } elseif (TRUE === $bIsString) {
            $poColumn->datatype = new TabularColumnDataTypeString();
        } elseif ($iSamples === $iEmptyCount) {
            // Unable to sample any data, fallback to string
            $poColumn->datatype = new TabularColumnDataTypeString();
        } else {
            throw new Exception(sprintf('Datatype not implemented! [i:%b d:%b s:%b]', $bIsInteger, $bIsDecimal, $bIsString));
        }
    }

    public function getAvailableDataTypes() {
        return array(
            'string',
            'integer',
            'decimal',
            'date',
        );
    }

    public function getDataTypeObject_byName($psName) {
        switch ($psName) {
            case "string":
                return new TabularColumnDataTypeString();
            case "integer":
                return new TabularColumnDataTypeInteger();
            case "decimal":
                return new TabularColumnDataTypeDecimal();
            case "date":
                return new TabularColumnDataTypeDate();
        }
        throw new Exception(sprintf('Unknown data type! [%s]', $psName));
    }
}

abstract class TabularColumnDataType {
    public $name;
    public $sqltype;
    public $bEscape = true;
    public $bNullOnEmpty = true;
}

class TabularColumnDataTypeString extends TabularColumnDataType {
    public $name = 'string';
    public $sqltype = 'VARCHAR';
    public $bEscape = true;
    public $bNullOnEmpty = true;
}

class TabularColumnDataTypeInteger extends TabularColumnDataType {
    public $name = 'integer';
    public $sqltype = 'INTEGER';
    public $bEscape = false;
    public $bNullOnEmpty = true;
}

class TabularColumnDataTypeDecimal extends TabularColumnDataType {
    public $name = 'decimal';
    public $sqltype = 'NUMERIC';
    public $bEscape = false;
    public $bNullOnEmpty = true;
}

class TabularColumnDataTypeDate extends TabularColumnDataType {
    public $name = 'date';
    public $sqltype = 'DATE';
    public $bEscape = false;
    public $bNullOnEmpty = true;
}

class TabularColumn {

    /**
     * @var string
     */
    public $originalName;

    /**
     * @var string
     */
    public $name;

    /**
     * @var TabularColumnDataTypeString
     */
    public $datatype;

    /**
     * @var string
     */
    public $comment;

    function __construct($name) {
        $this->originalName = $name;
        $this->name = TabularData::_getFriendlyColumnName($name);
        $this->datatype = new TabularColumnDataTypeString();
    }
}

abstract class TabularRenderer {

    /**
     * @param TabularData $poTabularData
     * @return mixed
     */
    abstract public function render(TabularData $poTabularData);

}

class TabularRendererPostgresql extends TabularRenderer {

    protected $sTableName = 'csv_to_query';

    public function setTableName($sTableName) {
        $this->sTableName = $sTableName;
    }

    /**
     * @param TabularData $poTabularData
     * @return string
     */
    public function render(TabularData $poTabularData) {

        $bUseFriendlyColumnName = true;

        $aOut = array();
        $aOut[] = 'DROP TABLE IF EXISTS "'.$this->sTableName.'";';
        $aOut[] = 'CREATE TABLE "'.$this->sTableName.'"';
        $aOut[] = "(";
        $aOut[] = "\tid SERIAL NOT NULL,";
        foreach($poTabularData->aColumns as $iIndex => $oColumn) {
            /** @var $oColumn TabularColumn */
            if ($iIndex+1 < count($poTabularData->aColumns)) {
                $sSeparator = ',';
            } else {
                $sSeparator = '';
            }
            $sColumnName = ($bUseFriendlyColumnName) ? $poTabularData->_getFriendlyColumnName($oColumn->name) : $oColumn->name;
            $aOut[] = "\t".'"'.$sColumnName.'"'." ".$oColumn->datatype->sqltype.$sSeparator;
        }
        $aOut[] = ");";
        $aOut[] = "";

        $aOut[] = 'INSERT INTO "'.$this->sTableName.'"';
        $aOut[] = "(";
        foreach($poTabularData->aColumns as $iIndex => $oColumn) {
            /** @var $oColumn TabularColumn */
            if ($iIndex+1 < count($poTabularData->aColumns)) {
                $sSeparator = ',';
            } else {
                $sSeparator = '';
            }
            $sColumnName = ($bUseFriendlyColumnName) ? $poTabularData->_getFriendlyColumnName($oColumn->name) : $oColumn->name;

            $aOut[] = "\t".'"'.$sColumnName.'"'.$sSeparator;
        }
        $aOut[] = ") VALUES ";
        foreach($poTabularData->aData as $iRowIndex => $aRow) {
            $aQueryColumn = array();
            if ($iRowIndex+1 < count($poTabularData->aData)) {
                $sSeparator = ',';
            } else {
                $sSeparator = ';';
            }
            foreach($poTabularData->aColumns as $iColIndex => $oColumn) {
                if (!isset($aRow[$iColIndex])) {
                    // Set at least empty
                    $aRow[$iColIndex] = '';
                }

                if ($oColumn->datatype->bNullOnEmpty && trim($aRow[$iColIndex]) == '') {
                    $aQueryColumn[] = 'NULL';
                } elseif ($oColumn->datatype->bEscape) {
                    // Needs escaping
                    //$aQueryColumn[] = "'".pg_escape_string($aRow[$iColIndex])."'";
                    //$aQueryColumn[] = "'".mysqli_real_escape_string($aRow[$iColIndex])."'";
                    //$aQueryColumn[] = "'".addslashes($aRow[$iColIndex])."'";
                    $aQueryColumn[] = "'".str_replace("'", "''", $aRow[$iColIndex])."'";
                } else {
                    $aQueryColumn[] = ($aRow[$iColIndex]) ? $aRow[$iColIndex] : 'NULL';
                }
            }
            $aOut[] = '('.implode(', ', $aQueryColumn).')'.$sSeparator;
        }

        return implode(chr(10), $aOut);
    }

}