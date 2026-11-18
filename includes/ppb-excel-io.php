<?php
/**
 * Engine I/O Pure-PHP untuk Excel (.xlsx, .xls XML), CSV/TSV & JSON
 * Tanpa dependensi ekstensi zip, xml, simplexml, atau dom maupun Composer.
 * Memanfaatkan zlib (gzinflate/gzdeflate/crc32) dan mbstring standar PHP.
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. HELPER XML, STRING & KOLOM EXCEL                                        */
/* ========================================================================== */

if (!function_exists('ppb_io_ensure_utf8')) {
function ppb_io_ensure_utf8($str) {
    $str = (string) $str;
    if ($str === '') return '';
    if (function_exists('mb_check_encoding') && !mb_check_encoding($str, 'UTF-8')) {
        if (function_exists('mb_convert_encoding')) {
            $str = mb_convert_encoding($str, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
        }
    }
    return $str;
}
}

if (!function_exists('ppb_io_xml_escape')) {
function ppb_io_xml_escape($str) {
    $str = ppb_io_ensure_utf8($str);
    // Strip karakter kontrol ilegal menurut spesifikasi XML 1.0
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $str);
    if ($str === null) {
        $str = '';
    }
    $flags = defined('ENT_XML1') ? (ENT_XML1 | ENT_QUOTES) : ENT_QUOTES;
    return htmlspecialchars($str, $flags, 'UTF-8');
}
}

if (!function_exists('ppb_io_xml_unescape')) {
function ppb_io_xml_unescape($str) {
    $str = (string) $str;
    if ($str === '') return '';
    // Decode referensi karakter numerik desimal & heksadesimal
    $str = preg_replace_callback('/&#x([0-9a-fA-F]+);/', function($m) {
        $cp = hexdec($m[1]);
        if ($cp >= 1 && $cp <= 0x10FFFF) {
            return function_exists('mb_chr') ? mb_chr($cp, 'UTF-8') : html_entity_decode($m[0], ENT_QUOTES, 'UTF-8');
        }
        return '';
    }, $str);
    $str = preg_replace_callback('/&#(\d+);/', function($m) {
        $cp = (int) $m[1];
        if ($cp >= 1 && $cp <= 0x10FFFF) {
            return function_exists('mb_chr') ? mb_chr($cp, 'UTF-8') : html_entity_decode($m[0], ENT_QUOTES, 'UTF-8');
        }
        return '';
    }, $str);
    return strtr($str, array(
        '&lt;'   => '<',
        '&gt;'   => '>',
        '&quot;' => '"',
        '&apos;' => "'",
        '&#39;'  => "'",
        '&amp;'  => '&',
    ));
}
}

if (!function_exists('ppb_io_parse_xml_attrs')) {
function ppb_io_parse_xml_attrs($attr_str) {
    $attrs = array();
    if (preg_match_all('/([a-zA-Z0-9_:\-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/s', (string) $attr_str, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $attrs[$m[1]] = ppb_io_xml_unescape($m[2] !== '' ? $m[2] : ($m[3] ?? ''));
        }
    }
    return $attrs;
}
}

if (!function_exists('ppb_io_col_letter')) {
function ppb_io_col_letter($index) {
    $index = (int) $index;
    $out = '';
    while ($index >= 0) {
        $out = chr(65 + ($index % 26)) . $out;
        $index = (int) floor($index / 26) - 1;
    }
    return $out;
}
}

if (!function_exists('ppb_io_col_index')) {
function ppb_io_col_index($letters) {
    $letters = strtoupper(preg_replace('/[^A-Z]/i', '', (string) $letters));
    $len = strlen($letters);
    if ($len === 0) return 0;
    $num = 0;
    for ($i = 0; $i < $len; $i++) {
        $num = $num * 26 + (ord($letters[$i]) - 64);
    }
    return $num - 1;
}
}

if (!function_exists('ppb_io_sanitize_sheet_name')) {
function ppb_io_sanitize_sheet_name($name, &$used = array()) {
    $name = preg_replace('/[\\\\\/\*\?\:\[\]]/', ' ', trim((string) $name));
    $name = preg_replace('/\s+/', ' ', (string) $name);
    if ($name === '') $name = 'Data';
    if (function_exists('mb_substr')) {
        $base = mb_substr($name, 0, 31, 'UTF-8');
    } else {
        $base = substr($name, 0, 31);
    }
    $cand = $base;
    $n = 2;
    while (isset($used[strtolower($cand)])) {
        $suf = ' ' . $n;
        $max = 31 - strlen($suf);
        $prefix = function_exists('mb_substr') ? mb_substr($name, 0, $max, 'UTF-8') : substr($name, 0, $max);
        $cand = trim($prefix) . $suf;
        $n++;
    }
    $used[strtolower($cand)] = true;
    return $cand;
}
}

/* ========================================================================== */
/* 2. PURE-PHP ZIP READER & WRITER                                            */
/* ========================================================================== */

if (!function_exists('ppb_io_zip_read_entries')) {
function ppb_io_zip_read_entries($bytes) {
    $bytes = (string) $bytes;
    $len = strlen($bytes);
    if ($len < 22 || substr($bytes, 0, 2) !== 'PK') {
        return new WP_Error('invalid_zip', 'Berkas bukan arsip .xlsx (ZIP) yang valid.');
    }

    // Cari End of Central Directory (PK\x05\x06) dari belakang
    $search_start = max(0, $len - 65557);
    $eocd_pos = strrpos(substr($bytes, $search_start), "\x50\x4b\x05\x06");
    if ($eocd_pos === false) {
        return new WP_Error('invalid_zip', 'Struktur Central Directory .xlsx tidak ditemukan atau berkas terpotong.');
    }
    $eocd_pos += $search_start;
    $eocd = unpack('vtotal_entries/Vcd_size/Vcd_offset', substr($bytes, $eocd_pos + 10, 10));
    if (!$eocd) {
        return new WP_Error('invalid_zip', 'Header EOCD ZIP gagal dibaca.');
    }

    $cd_offset = (int) $eocd['cd_offset'];
    $cd_size   = (int) $eocd['cd_size'];
    $total     = (int) $eocd['total_entries'];
    if ($cd_offset < 0 || $cd_offset + $cd_size > $len) {
        return new WP_Error('invalid_zip', 'Offset Central Directory melebihi ukuran berkas.');
    }

    $entries = array();
    $pos = $cd_offset;
    for ($i = 0; $i < $total && ($pos + 46) <= $len; $i++) {
        if (substr($bytes, $pos, 4) !== "\x50\x4b\x01\x02") {
            break;
        }
        $hdr = unpack(
            'vver_made/vver_need/vflags/vmethod/vmtime/vmdate/Vcrc/Vcomp_size/Vuncomp_size/vname_len/vextra_len/vcomment_len/vdisk/vint_attr/Vext_attr/Vlocal_offset',
            substr($bytes, $pos + 4, 42)
        );
        $name_len    = (int) $hdr['name_len'];
        $extra_len   = (int) $hdr['extra_len'];
        $comment_len = (int) $hdr['comment_len'];
        $name        = substr($bytes, $pos + 46, $name_len);
        $pos        += 46 + $name_len + $extra_len + $comment_len;

        if ($name === '' || substr($name, -1) === '/') {
            continue;
        }
        if (((int) $hdr['flags'] & 0x0001) !== 0) {
            return new WP_Error('encrypted_zip', 'File Excel terenkripsi password tidak dapat diimpor. Simpan ulang tanpa proteksi password.');
        }

        $loc = (int) $hdr['local_offset'];
        if ($loc + 30 > $len || substr($bytes, $loc, 4) !== "\x50\x4b\x03\x04") {
            continue;
        }
        $lhdr = unpack('vname_len/vextra_len', substr($bytes, $loc + 26, 4));
        $data_start = $loc + 30 + (int) $lhdr['name_len'] + (int) $lhdr['extra_len'];
        $comp_size  = (int) $hdr['comp_size'];
        if ($data_start + $comp_size > $len) {
            return new WP_Error('corrupt_zip', 'Data entri ZIP rusak pada: ' . $name);
        }

        $raw = substr($bytes, $data_start, $comp_size);
        $method = (int) $hdr['method'];
        if ($method === 0) {
            $entries[$name] = $raw;
        } elseif ($method === 8) {
            if (!function_exists('gzinflate')) {
                return new WP_Error('no_zlib', 'Ekstensi PHP zlib tidak aktif untuk mendekompresi .xlsx.');
            }
            $dec = ($comp_size === 0 && (int) $hdr['uncomp_size'] === 0) ? '' : @gzinflate($raw);
            if ($dec === false) {
                return new WP_Error('corrupt_zip', 'Gagal mendekompresi entri XML dalam .xlsx: ' . $name);
            }
            $entries[$name] = $dec;
        } else {
            return new WP_Error('unsupported_zip_method', 'Metode kompresi ZIP (' . $method . ') tidak didukung pada: ' . $name);
        }
    }

    return $entries;
}
}

if (!function_exists('ppb_io_zip_build')) {
function ppb_io_zip_build($files) {
    $local_stream = '';
    $cd_stream    = '';
    $count        = 0;

    $ts = time();
    $dt = getdate($ts);
    $dos_time = (($dt['hours'] & 0x1F) << 11) | (($dt['minutes'] & 0x3F) << 5) | (($dt['seconds'] >> 1) & 0x1F);
    $dos_date = ((max(0, $dt['year'] - 1980) & 0x7F) << 9) | (($dt['mon'] & 0x0F) << 5) | ($dt['mday'] & 0x1F);

    foreach ($files as $name => $data) {
        $name = ltrim(str_replace('\\', '/', (string) $name), '/');
        $data = (string) $data;
        $uncomp_len = strlen($data);
        $crc = crc32($data) & 0xFFFFFFFF;

        $method = 0;
        $comp   = $data;
        if (function_exists('gzdeflate') && $uncomp_len > 32) {
            $def = @gzdeflate($data, 6);
            if ($def !== false && strlen($def) < $uncomp_len) {
                $method = 8;
                $comp   = $def;
            }
        }
        $comp_len = strlen($comp);
        $name_len = strlen($name);
        $flags    = 0x0800; // UTF-8 filename flag
        $offset   = strlen($local_stream);

        $local_header = "\x50\x4b\x03\x04"
            . pack('vvvvvVVVvv', 20, $flags, $method, $dos_time, $dos_date, $crc, $comp_len, $uncomp_len, $name_len, 0);

        $local_stream .= $local_header . $name . $comp;

        $cd_header = "\x50\x4b\x01\x02"
            . pack('vvvvvvVVVvvvvvVV', 20, 20, $flags, $method, $dos_time, $dos_date, $crc, $comp_len, $uncomp_len, $name_len, 0, 0, 0, 0, 32, $offset);

        $cd_stream .= $cd_header . $name;
        $count++;
    }

    $cd_offset = strlen($local_stream);
    $cd_size   = strlen($cd_stream);

    $eocd = "\x50\x4b\x05\x06"
        . pack('vvvvVVv', 0, 0, $count, $count, $cd_size, $cd_offset, 0);

    return $local_stream . $cd_stream . $eocd;
}
}

/* ========================================================================== */
/* 3. GENERATOR EXCEL .XLSX MULTI-SHEET                                       */
/* ========================================================================== */

if (!function_exists('ppb_io_excel_serial_to_date')) {
function ppb_io_excel_serial_to_date($serial, $has_time = false) {
    $num = (float) $serial;
    if ($num < 0) return (string) $serial;
    $days = (int) floor($num);
    $frac = $num - $days;

    // Waktu saja (mis. 0.375 -> 09:00)
    if ($days === 0 && $frac > 0) {
        $secs = (int) round($frac * 86400);
        $h = (int) floor($secs / 3600) % 24;
        $m = (int) floor(($secs % 3600) / 60);
        return sprintf('%02d:%02d', $h, $m);
    }

    // Epoch Excel 1900 dengan koreksi bug tahun kabisat 1900 Lotus 1-2-3
    $unix_days = $days > 59 ? ($days - 25569) : ($days - 25568);
    $ts = $unix_days * 86400;
    $date_str = gmdate('Y-m-d', $ts);
    if ($has_time && $frac > 0.0001) {
        $secs = (int) round($frac * 86400);
        $h = (int) floor($secs / 3600) % 24;
        $m = (int) floor(($secs % 3600) / 60);
        return $date_str . sprintf(' %02d:%02d', $h, $m);
    }
    return $date_str;
}
}

if (!function_exists('ppb_io_xlsx_build')) {
function ppb_io_xlsx_build($sheets) {
    if (!is_array($sheets) || empty($sheets)) {
        return '';
    }

    $shared_strings = array();
    $ss_index       = array();
    $add_shared = function($val) use (&$shared_strings, &$ss_index) {
        $s = ppb_io_ensure_utf8($val);
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s);
        if ($s === null) $s = '';
        if (!isset($ss_index[$s])) {
            $ss_index[$s] = count($shared_strings);
            $shared_strings[] = $s;
        }
        return $ss_index[$s];
    };

    $used_names = array();
    $norm_sheets = array();
    foreach ($sheets as $sh) {
        $name    = ppb_io_sanitize_sheet_name($sh['name'] ?? 'Data', $used_names);
        $headers = array_values($sh['headers'] ?? array());
        $rows    = array_values($sh['rows'] ?? array());
        $widths  = array_values($sh['widths'] ?? array());
        $norm_sheets[] = array(
            'name'          => $name,
            'headers'       => $headers,
            'rows'          => $rows,
            'widths'        => $widths,
            'freeze_header' => array_key_exists('freeze_header', $sh) ? (bool) $sh['freeze_header'] : !empty($headers),
            'autofilter'    => array_key_exists('autofilter', $sh) ? (bool) $sh['autofilter'] : (!empty($headers) && count($headers) > 1),
            'is_guide'      => !empty($sh['is_guide']),
        );
    }

    $files = array();
    $sheet_rels_xml = '';
    $workbook_sheets_xml = '';
    $ct_overrides = '';

    foreach ($norm_sheets as $idx => $sh) {
        $num = $idx + 1;
        $headers = $sh['headers'];
        $rows    = $sh['rows'];
        $all_rows = !empty($headers) ? array_merge(array($headers), $rows) : $rows;

        $col_count = 0;
        foreach ($all_rows as $r) {
            if (is_array($r)) {
                $col_count = max($col_count, count($r));
            }
        }
        $col_count = max($col_count, count($sh['widths']), 1);

        // Hitung lebar kolom otomatis dari panjang isi sel jika tidak ditetapkan
        $cols_xml = '';
        $cols_xml .= '<cols>';
        for ($c = 0; $c < $col_count; $c++) {
            $w = isset($sh['widths'][$c]) && (float) $sh['widths'][$c] > 0 ? (float) $sh['widths'][$c] : 0;
            if ($w <= 0) {
                $max_len = 12;
                $sample_limit = min(count($all_rows), 80);
                for ($ri = 0; $ri < $sample_limit; $ri++) {
                    $cell_val = (string) ($all_rows[$ri][$c] ?? '');
                    $lines = explode("\n", $cell_val);
                    foreach ($lines as $ln) {
                        $ln_len = function_exists('mb_strlen') ? mb_strlen($ln, 'UTF-8') : strlen($ln);
                        if ($ln_len + 4 > $max_len) {
                            $max_len = $ln_len + 4;
                        }
                    }
                }
                $w = min(56, max(12, $max_len));
            }
            $col_1 = $c + 1;
            $cols_xml .= '<col min="' . $col_1 . '" max="' . $col_1 . '" width="' . number_format($w, 2, '.', '') . '" customWidth="1"/>';
        }
        $cols_xml .= '</cols>';

        $sheet_views = '<sheetViews><sheetView workbookViewId="0">';
        if ($sh['freeze_header']) {
            $sheet_views .= '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>';
        }
        $sheet_views .= '</sheetView></sheetViews>';

        $rows_xml = '<sheetData>';
        foreach ($all_rows as $r_idx => $row) {
            $r_num = $r_idx + 1;
            $is_header = (!empty($headers) && $r_idx === 0);
            $ht_attr = $is_header ? ' ht="28" customHeight="1"' : '';
            $rows_xml .= '<row r="' . $r_num . '"' . $ht_attr . '>';

            $row_vals = is_array($row) ? array_values($row) : array();
            for ($c_idx = 0; $c_idx < $col_count; $c_idx++) {
                $ref = ppb_io_col_letter($c_idx) . $r_num;
                $val = array_key_exists($c_idx, $row_vals) ? $row_vals[$c_idx] : '';

                if ($is_header) {
                    $style_id = 1;
                } elseif ($sh['is_guide'] && $c_idx === 0 && trim((string) $val) !== '') {
                    $style_id = 4;
                } else {
                    $style_id = ($r_idx % 2 === 0) ? 2 : 3;
                }

                if ($val === '' || $val === null) {
                    $rows_xml .= '<c r="' . $ref . '" s="' . $style_id . '"/>';
                    continue;
                }

                // Tulis numerik asli hanya bila integer/float PHP agar NIP/NIM/Telepon tetap teks
                $is_numeric_cell = (!$is_header && (is_int($val) || is_float($val)));
                if ($is_numeric_cell) {
                    $rows_xml .= '<c r="' . $ref . '" s="' . $style_id . '" t="n"><v>' . $val . '</v></c>';
                } else {
                    $str_id = $add_shared((string) $val);
                    $rows_xml .= '<c r="' . $ref . '" s="' . $style_id . '" t="s"><v>' . $str_id . '</v></c>';
                }
            }
            $rows_xml .= '</row>';
        }
        $rows_xml .= '</sheetData>';

        $autofilter_xml = '';
        if ($sh['autofilter'] && count($all_rows) >= 1) {
            $last_col = ppb_io_col_letter(max(0, $col_count - 1));
            $last_row = max(1, count($all_rows));
            $autofilter_xml = '<autoFilter ref="A1:' . $last_col . $last_row . '"/>';
        }

        $files['xl/worksheets/sheet' . $num . '.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $sheet_views
            . '<sheetFormatPr defaultRowHeight="20"/>'
            . $cols_xml
            . $rows_xml
            . $autofilter_xml
            . '</worksheet>';

        $rid = 'rId' . $num;
        $sheet_rels_xml .= '<Relationship Id="' . $rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $num . '.xml"/>';
        $workbook_sheets_xml .= '<sheet name="' . ppb_io_xml_escape($sh['name']) . '" sheetId="' . $num . '" r:id="' . $rid . '"/>';
        $ct_overrides .= '<Override PartName="/xl/worksheets/sheet' . $num . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }

    // sharedStrings.xml
    $ss_count = count($shared_strings);
    $ss_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $ss_count . '" uniqueCount="' . $ss_count . '">';
    foreach ($shared_strings as $s) {
        $esc = ppb_io_xml_escape($s);
        $preserve = (preg_match('/^\s|\s$/u', $s) || strpos($s, "\n") !== false) ? ' xml:space="preserve"' : '';
        $ss_xml .= '<si><t' . $preserve . '>' . $esc . '</t></si>';
    }
    $ss_xml .= '</sst>';
    $files['xl/sharedStrings.xml'] = $ss_xml;

    // styles.xml (0=default, 1=header #0D3B22, 2=data putih, 3=data zebra, 4=guide key #E8F0EA)
    $files['xl/styles.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="3">'
        .   '<font><sz val="10"/><color rgb="FF1E293B"/><name val="Calibri"/></font>'
        .   '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
        .   '<font><b/><sz val="10"/><color rgb="FF0D3B22"/><name val="Calibri"/></font>'
        . '</fonts>'
        . '<fills count="5">'
        .   '<fill><patternFill patternType="none"/></fill>'
        .   '<fill><patternFill patternType="gray125"/></fill>'
        .   '<fill><patternFill patternType="solid"><fgColor rgb="FF0D3B22"/></patternFill></fill>'
        .   '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>'
        .   '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F0EA"/></patternFill></fill>'
        . '</fills>'
        . '<borders count="2">'
        .   '<border><left/><right/><top/><bottom/><diagonal/></border>'
        .   '<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border>'
        . '</borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="5">'
        .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
        .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
        .   '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
        .   '<xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
        . '</cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>';

    $next_rid = count($norm_sheets) + 1;
    $sheet_rels_xml .= '<Relationship Id="rId' . $next_rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
    $sheet_rels_xml .= '<Relationship Id="rId' . ($next_rid + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';

    $files['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets>' . $workbook_sheets_xml . '</sheets>'
        . '</workbook>';

    $files['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . $sheet_rels_xml
        . '</Relationships>';

    $files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';

    $files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . $ct_overrides
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
        . '</Types>';

    return ppb_io_zip_build($files);
}
}

/* ========================================================================== */
/* 4. PARSER EXCEL .XLSX & SPREADSHEETML .XLS                                 */
/* ========================================================================== */

if (!function_exists('ppb_io_xlsx_extract_styles_date_map')) {
function ppb_io_xlsx_extract_styles_date_map($styles_xml) {
    $date_style_map = array();
    if ($styles_xml === '') return $date_style_map;

    $custom_fmts = array();
    if (preg_match_all('/<(?:\w+:)?numFmt\b([^>]*)\/?\s*>/s', $styles_xml, $fmts)) {
        foreach ($fmts[1] as $attr_s) {
            $a = ppb_io_parse_xml_attrs($attr_s);
            if (isset($a['numFmtId'], $a['formatCode'])) {
                $custom_fmts[(int) $a['numFmtId']] = (string) $a['formatCode'];
            }
        }
    }

    $is_date_fmt = function($num_fmt_id) use ($custom_fmts) {
        $id = (int) $num_fmt_id;
        if (($id >= 14 && $id <= 22) || ($id >= 45 && $id <= 47)) {
            return array(true, in_array($id, array(22), true));
        }
        if (isset($custom_fmts[$id])) {
            $code = preg_replace('/"[^"]*"|\[[^\]]*\]/', '', $custom_fmts[$id]);
            if (preg_match('/[yd]/i', (string) $code) && strpos((string) $code, '#') === false && strpos((string) $code, '0.0') === false) {
                $has_time = (preg_match('/[hs]/i', (string) $code) === 1);
                return array(true, $has_time);
            }
        }
        return array(false, false);
    };

    if (preg_match('/<(?:\w+:)?cellXfs\b[^>]*>(.*?)<\/(?:\w+:)?cellXfs>/s', $styles_xml, $xfs_block)) {
        if (preg_match_all('/<(?:\w+:)?xf\b([^>]*?)(?:\/>|>.*?<\/(?:\w+:)?xf>)/s', $xfs_block[1], $xfs)) {
            foreach ($xfs[1] as $idx => $attr_s) {
                $a = ppb_io_parse_xml_attrs($attr_s);
                if (isset($a['numFmtId'])) {
                    list($is_d, $has_t) = $is_date_fmt($a['numFmtId']);
                    if ($is_d) {
                        $date_style_map[$idx] = $has_t;
                    }
                }
            }
        }
    }
    return $date_style_map;
}
}

if (!function_exists('ppb_io_xlsx_extract_shared_strings')) {
function ppb_io_xlsx_extract_shared_strings($ss_xml) {
    $strings = array();
    if ($ss_xml === '') return $strings;

    // Hapus phonetic runs <rPh>...</rPh> agar teks Jepang/Mandarin/khusus tidak terduplikasi
    $ss_xml = preg_replace('/<(?:\w+:)?rPh\b[^>]*>.*?<\/(?:\w+:)?rPh>/s', '', $ss_xml);
    if (preg_match_all('/<(?:\w+:)?si\b[^>]*>(.*?)<\/(?:\w+:)?si>/s', (string) $ss_xml, $sis)) {
        foreach ($sis[1] as $si_inner) {
            $buf = '';
            if (preg_match_all('/<(?:\w+:)?t\b[^>]*>(.*?)<\/(?:\w+:)?t>/s', $si_inner, $ts)) {
                foreach ($ts[1] as $t_part) {
                    $buf .= ppb_io_xml_unescape($t_part);
                }
            }
            $strings[] = $buf;
        }
    }
    return $strings;
}
}

if (!function_exists('ppb_io_xlsx_parse')) {
function ppb_io_xlsx_parse($bytes) {
    $entries = ppb_io_zip_read_entries($bytes);
    if (is_wp_error($entries)) {
        return $entries;
    }

    // Lookup case-insensitive nama file dalam ZIP
    $entry_lc = array();
    foreach ($entries as $path => $content) {
        $clean_p = ltrim(str_replace('\\', '/', $path), '/');
        $entry_lc[strtolower($clean_p)] = $content;
    }

    $wb_xml = $entry_lc['xl/workbook.xml'] ?? '';
    if ($wb_xml === '') {
        return new WP_Error('invalid_xlsx', 'File .xlsx tidak memiliki xl/workbook.xml.');
    }

    // Map rId -> target worksheet path
    $rels_xml = $entry_lc['xl/_rels/workbook.xml.rels'] ?? '';
    $rel_map  = array();
    if (preg_match_all('/<(?:\w+:)?Relationship\b([^>]*)\/?\s*>/s', $rels_xml, $rm)) {
        foreach ($rm[1] as $attr_s) {
            $a = ppb_io_parse_xml_attrs($attr_s);
            if (!empty($a['Id']) && !empty($a['Target'])) {
                $target = ltrim(str_replace('\\', '/', $a['Target']), '/');
                if (stripos($target, 'xl/') !== 0) {
                    $target = 'xl/' . ltrim($target, './');
                }
                $rel_map[$a['Id']] = strtolower($target);
            }
        }
    }

    $shared_strings = ppb_io_xlsx_extract_shared_strings($entry_lc['xl/sharedstrings.xml'] ?? '');
    $date_styles    = ppb_io_xlsx_extract_styles_date_map($entry_lc['xl/styles.xml'] ?? '');

    // Ambil daftar sheet sesuai urutan di workbook.xml
    $sheet_defs = array();
    if (preg_match_all('/<(?:\w+:)?sheet\b([^>]*)\/?\s*>/s', $wb_xml, $sm)) {
        foreach ($sm[1] as $idx => $attr_s) {
            $a = ppb_io_parse_xml_attrs($attr_s);
            $name = $a['name'] ?? ('Sheet' . ($idx + 1));
            $rid  = $a['r:id'] ?? ($a['id'] ?? '');
            $path = ($rid !== '' && isset($rel_map[$rid])) ? $rel_map[$rid] : ('xl/worksheets/sheet' . ($idx + 1) . '.xml');
            if (isset($entry_lc[$path])) {
                $sheet_defs[] = array('name' => $name, 'xml' => $entry_lc[$path]);
            }
        }
    }

    if (empty($sheet_defs)) {
        return new WP_Error('empty_xlsx', 'Tidak ditemukan lembar kerja (worksheet) di dalam berkas .xlsx.');
    }

    $sheets_out = array();
    foreach ($sheet_defs as $sdef) {
        $xml  = $sdef['xml'];
        $rows = array();

        if (preg_match_all('/<(?:\w+:)?row\b([^>]*)>(.*?)<\/(?:\w+:)?row>/s', $xml, $row_matches, PREG_SET_ORDER)) {
            $next_row_num = 1;
            foreach ($row_matches as $rm) {
                $rattrs = ppb_io_parse_xml_attrs($rm[1]);
                $r_num  = !empty($rattrs['r']) ? (int) $rattrs['r'] : $next_row_num;
                $next_row_num = $r_num + 1;

                $row_cells = array();
                $seq_col   = 0;
                $max_col   = -1;

                if (preg_match_all('/<(?:\w+:)?c\b([^>]*?)(?:\/>|>(.*?)<\/(?:\w+:)?c>)/s', $rm[2], $cell_matches, PREG_SET_ORDER)) {
                    foreach ($cell_matches as $cm) {
                        $cattrs = ppb_io_parse_xml_attrs($cm[1]);
                        $inner  = $cm[2] ?? '';
                        if (!empty($cattrs['r']) && preg_match('/^([A-Z]+)\d+$/i', $cattrs['r'], $col_m)) {
                            $col_idx = ppb_io_col_index($col_m[1]);
                        } else {
                            $col_idx = $seq_col;
                        }
                        $seq_col = $col_idx + 1;
                        if ($col_idx > $max_col) $max_col = $col_idx;

                        $type = $cattrs['t'] ?? '';
                        $val  = '';

                        if ($type === 'inlineStr') {
                            $buf = '';
                            if (preg_match_all('/<(?:\w+:)?t\b[^>]*>(.*?)<\/(?:\w+:)?t>/s', $inner, $ts)) {
                                foreach ($ts[1] as $tp) {
                                    $buf .= ppb_io_xml_unescape($tp);
                                }
                            }
                            $val = $buf;
                        } elseif (preg_match('/<(?:\w+:)?v\b[^>]*>(.*?)<\/(?:\w+:)?v>/s', $inner, $vm)) {
                            $raw_v = ppb_io_xml_unescape($vm[1]);
                            if ($type === 's') {
                                $s_i = (int) $raw_v;
                                $val = $shared_strings[$s_i] ?? '';
                            } elseif ($type === 'b') {
                                $val = ($raw_v === '1' || strtolower($raw_v) === 'true') ? '1' : '0';
                            } elseif ($type === 'str' || $type === 'e') {
                                $val = $raw_v;
                            } else {
                                // Numerik atau serial tanggal Excel
                                $style_i = isset($cattrs['s']) ? (int) $cattrs['s'] : -1;
                                if ($style_i >= 0 && isset($date_styles[$style_i]) && is_numeric($raw_v)) {
                                    $val = ppb_io_excel_serial_to_date($raw_v, (bool) $date_styles[$style_i]);
                                } else {
                                    $val = $raw_v;
                                }
                            }
                        }

                        $row_cells[$col_idx] = trim((string) $val) === '' && strpos((string) $val, ' ') === false ? '' : (string) $val;
                    }
                }

                if ($max_col >= 0) {
                    $dense = array();
                    $has_non_empty = false;
                    for ($c = 0; $c <= $max_col; $c++) {
                        $cv = $row_cells[$c] ?? '';
                        if (trim($cv) !== '') $has_non_empty = true;
                        $dense[] = $cv;
                    }
                    if ($has_non_empty) {
                        $rows[] = $dense;
                    }
                }
            }
        }

        $sheets_out[] = array(
            'name' => $sdef['name'],
            'rows' => $rows,
        );
    }

    return array('sheets' => $sheets_out);
}
}

if (!function_exists('ppb_io_xls_xml_parse')) {
function ppb_io_xls_xml_parse($bytes) {
    $xml = ppb_io_ensure_utf8($bytes);
    if (stripos($xml, '<Worksheet') === false && stripos($xml, '<ss:Worksheet') === false) {
        return new WP_Error('invalid_xls_xml', 'Format SpreadsheetML XML (.xls) tidak dikenali.');
    }

    $sheets_out = array();
    if (preg_match_all('/<(?:\w+:)?Worksheet\b([^>]*)>(.*?)<\/(?:\w+:)?Worksheet>/s', $xml, $ws_matches, PREG_SET_ORDER)) {
        foreach ($ws_matches as $widx => $wm) {
            $wattrs = ppb_io_parse_xml_attrs($wm[1]);
            $name   = $wattrs['ss:Name'] ?? ($wattrs['Name'] ?? ('Sheet' . ($widx + 1)));
            $rows   = array();

            if (preg_match_all('/<(?:\w+:)?Row\b[^>]*>(.*?)<\/(?:\w+:)?Row>/s', $wm[2], $row_matches, PREG_SET_ORDER)) {
                foreach ($row_matches as $rm) {
                    $cells = array();
                    $col_i = 0;
                    $max_c = -1;
                    if (preg_match_all('/<(?:\w+:)?Cell\b([^>]*?)(?:\/>|>(.*?)<\/(?:\w+:)?Cell>)/s', $rm[1], $cell_matches, PREG_SET_ORDER)) {
                        foreach ($cell_matches as $cm) {
                            $cattrs = ppb_io_parse_xml_attrs($cm[1]);
                            if (!empty($cattrs['ss:Index'])) {
                                $col_i = max(0, (int) $cattrs['ss:Index'] - 1);
                            } elseif (!empty($cattrs['Index'])) {
                                $col_i = max(0, (int) $cattrs['Index'] - 1);
                            }
                            $val = '';
                            if (!empty($cm[2]) && preg_match('/<(?:\w+:)?Data\b[^>]*>(.*?)<\/(?:\w+:)?Data>/s', $cm[2], $dm)) {
                                $val = ppb_io_xml_unescape($dm[1]);
                            }
                            $cells[$col_i] = (string) $val;
                            if ($col_i > $max_c) $max_c = $col_i;
                            $col_i++;
                        }
                    }
                    if ($max_c >= 0) {
                        $dense = array();
                        $has_val = false;
                        for ($c = 0; $c <= $max_c; $c++) {
                            $v = $cells[$c] ?? '';
                            if (trim($v) !== '') $has_val = true;
                            $dense[] = $v;
                        }
                        if ($has_val) $rows[] = $dense;
                    }
                }
            }

            $sheets_out[] = array('name' => $name, 'rows' => $rows);
        }
    }

    if (empty($sheets_out)) {
        return new WP_Error('empty_xls_xml', 'Tidak ditemukan worksheet pada berkas .xls XML.');
    }
    return array('sheets' => $sheets_out);
}
}

/* ========================================================================== */
/* 5. PARSER & BUILDER CSV / TSV & JSON                                       */
/* ========================================================================== */

if (!function_exists('ppb_io_csv_parse')) {
function ppb_io_csv_parse($bytes) {
    $text = (string) $bytes;
    // Strip UTF-8 BOM
    if (substr($text, 0, 3) === "\xEF\xBB\xBF") {
        $text = substr($text, 3);
    }
    $text = ppb_io_ensure_utf8($text);
    $text = str_replace(array("\r\n", "\r"), "\n", $text);

    $lines = explode("\n", $text);
    $delimiter = ',';

    // Tangani instruksi "sep=;" khas Excel jika ada pada baris pertama
    if (!empty($lines) && preg_match('/^sep=(.)$/i', trim($lines[0]), $sm)) {
        $delimiter = $sm[1];
        array_shift($lines);
    } else {
        // Deteksi otomatis pemisah terbaik (, atau ; atau tab)
        $sample = '';
        foreach ($lines as $ln) {
            if (trim($ln) !== '') {
                $sample = $ln;
                break;
            }
        }
        $counts = array(
            ','  => substr_count($sample, ','),
            ';'  => substr_count($sample, ';'),
            "\t" => substr_count($sample, "\t"),
        );
        arsort($counts);
        $best = key($counts);
        if ($counts[$best] > 0) {
            $delimiter = $best;
        }
    }

    $body = implode("\n", $lines);
    $fp   = @fopen('php://temp', 'r+');
    if (!$fp) {
        return new WP_Error('csv_stream_err', 'Gagal membaca buffer CSV.');
    }
    fwrite($fp, $body);
    rewind($fp);

    $rows = array();
    while (($row = fgetcsv($fp, 0, $delimiter, '"', '\\')) !== false) {
        if ($row === array(null)) continue;
        $has_val = false;
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                $has_val = true;
                break;
            }
        }
        if ($has_val) {
            $rows[] = array_map(function($c) { return (string) $c; }, $row);
        }
    }
    fclose($fp);

    if (empty($rows)) {
        return new WP_Error('empty_csv', 'Berkas CSV kosong atau tidak memiliki baris data.');
    }

    return array('sheets' => array(array('name' => 'Data', 'rows' => $rows)));
}
}

if (!function_exists('ppb_io_csv_build')) {
function ppb_io_csv_build($headers, $rows, $delimiter = ',') {
    $fp = @fopen('php://temp', 'r+');
    if (!$fp) return '';
    // UTF-8 BOM agar Excel Windows membuka karakter UTF-8 dengan benar
    fwrite($fp, "\xEF\xBB\xBF");
    if (!empty($headers)) {
        fputcsv($fp, $headers, $delimiter);
    }
    foreach ($rows as $row) {
        fputcsv($fp, is_array($row) ? $row : array(), $delimiter);
    }
    rewind($fp);
    $out = stream_get_contents($fp);
    fclose($fp);
    return (string) $out;
}
}

if (!function_exists('ppb_io_json_assoc_list_to_rows')) {
function ppb_io_json_assoc_list_to_rows($list) {
    if (!is_array($list) || empty($list)) {
        return array();
    }
    $first = reset($list);
    // Jika sudah berbentuk matriks baris-per-baris (list of indexed arrays)
    if (is_array($first) && array_keys($first) === range(0, count($first) - 1)) {
        return array_values($list);
    }

    // Kumpulkan seluruh key unik secara berurutan agar kolom konsisten
    $headers = array();
    $seen    = array();
    foreach ($list as $item) {
        if (!is_array($item)) continue;
        foreach (array_keys($item) as $k) {
            $k_str = (string) $k;
            if (!isset($seen[$k_str])) {
                $seen[$k_str] = true;
                $headers[] = $k_str;
            }
        }
    }
    if (empty($headers)) return array();

    $rows = array($headers);
    foreach ($list as $item) {
        if (!is_array($item)) continue;
        $r = array();
        foreach ($headers as $h) {
            $val = array_key_exists($h, $item) ? $item[$h] : '';
            if (is_array($val)) {
                // Dukungan array seperti dosen_slugs atau kepakaran
                $flat = array();
                foreach ($val as $sub) {
                    if (is_scalar($sub)) $flat[] = (string) $sub;
                }
                $val = implode('; ', $flat);
            } elseif (is_bool($val)) {
                $val = $val ? '1' : '0';
            }
            $r[] = (string) $val;
        }
        $rows[] = $r;
    }
    return $rows;
}
}

if (!function_exists('ppb_io_json_parse')) {
function ppb_io_json_parse($bytes, $dataset_key = '') {
    $text = (string) $bytes;
    if (substr($text, 0, 3) === "\xEF\xBB\xBF") {
        $text = substr($text, 3);
    }
    $text = trim(ppb_io_ensure_utf8($text));
    if ($text === '') {
        return new WP_Error('empty_json', 'Konten JSON kosong.');
    }

    $decoded = json_decode($text, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('invalid_json', 'Format JSON tidak valid: ' . json_last_error_msg());
    }
    if (!is_array($decoded) || empty($decoded)) {
        return new WP_Error('empty_json', 'Data JSON tidak berisi array baris data.');
    }

    $sheets = array();

    // Kasus A: Objek memiliki properti "sheets" (multi-sheet export)
    if (isset($decoded['sheets']) && is_array($decoded['sheets'])) {
        foreach ($decoded['sheets'] as $sname => $slist) {
            if (!is_array($slist)) continue;
            $rows = ppb_io_json_assoc_list_to_rows($slist);
            if (!empty($rows)) {
                $sheets[] = array('name' => (string) $sname, 'rows' => $rows);
            }
        }
    } elseif ($dataset_key === 'pnbp' && (isset($decoded['riset']) || isset($decoded['pkm']))) {
        // Kasus B: Format native PNBP { "riset": [...], "pkm": [...] }
        if (!empty($decoded['riset']) && is_array($decoded['riset'])) {
            $sheets[] = array('name' => 'Hibah Penelitian', 'rows' => ppb_io_json_assoc_list_to_rows($decoded['riset']));
        }
        if (!empty($decoded['pkm']) && is_array($decoded['pkm'])) {
            $sheets[] = array('name' => 'Hibah PkM', 'rows' => ppb_io_json_assoc_list_to_rows($decoded['pkm']));
        }
    } else {
        // Kasus C: Pembungkus umum (rows/data/items/courses) atau langsung indexed array
        $list = $decoded;
        foreach (array('rows', 'data', 'items', 'courses') as $wrap_key) {
            if (isset($decoded[$wrap_key]) && is_array($decoded[$wrap_key])) {
                $list = $decoded[$wrap_key];
                break;
            }
        }
        if (array_keys($list) !== range(0, count($list) - 1) && is_array(reset($list))) {
            // Single object bukan list -> bungkus sebagai 1 baris jika berbentuk record datar
            $first = reset($list);
            if (!is_array($first)) {
                $list = array($list);
            }
        }
        $rows = ppb_io_json_assoc_list_to_rows($list);
        if (!empty($rows)) {
            $sheets[] = array('name' => 'Data', 'rows' => $rows);
        }
    }

    if (empty($sheets)) {
        return new WP_Error('empty_json', 'Tidak ditemukan baris data yang dapat diimpor dari berkas JSON.');
    }

    return array('sheets' => $sheets);
}
}

if (!function_exists('ppb_io_json_build')) {
function ppb_io_json_build($dataset_key, $data_sheets) {
    // $data_sheets: [ ['name' => ..., 'key' => ..., 'assoc_rows' => [ [...] ] ], ... ]
    $payload = array(
        'dataset'     => (string) $dataset_key,
        'exported_at' => current_time('mysql'),
        'version'     => '1.0',
    );
    if (count($data_sheets) === 1) {
        $rows = array_values($data_sheets[0]['assoc_rows'] ?? array());
        $payload['count'] = count($rows);
        $payload['rows']  = $rows;
    } else {
        $sheets_map = array();
        $total = 0;
        foreach ($data_sheets as $ds) {
            $key  = (string) ($ds['key'] ?? ($ds['name'] ?? 'sheet'));
            $rows = array_values($ds['assoc_rows'] ?? array());
            $sheets_map[$key] = $rows;
            $total += count($rows);
        }
        $payload['count']  = $total;
        $payload['sheets'] = $sheets_map;
    }
    return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
}

/* ========================================================================== */
/* 6. DISPATCHER INPUT UPLOAD & MAPPER HEADER                                 */
/* ========================================================================== */

if (!function_exists('ppb_io_parse_upload_input')) {
function ppb_io_parse_upload_input($file_arr = null, $raw_json = '', $dataset_key = '') {
    $raw_json = trim((string) $raw_json);
    $has_file = is_array($file_arr) && !empty($file_arr['tmp_name']) && (($file_arr['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);

    if (!$has_file && $raw_json !== '') {
        $parsed = ppb_io_json_parse($raw_json, $dataset_key);
        if (is_wp_error($parsed)) return $parsed;
        $parsed['format'] = 'json';
        return $parsed;
    }

    if (!$has_file) {
        return new WP_Error('no_input', 'Silakan pilih file (.xlsx, .xls XML, .csv, .json) atau tempel teks JSON terlebih dahulu.');
    }

    $err_code = (int) ($file_arr['error'] ?? UPLOAD_ERR_OK);
    if ($err_code !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_err', 'Upload berkas gagal (kode error PHP: ' . $err_code . ').');
    }

    $tmp  = (string) $file_arr['tmp_name'];
    $name = (string) ($file_arr['name'] ?? 'upload');
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $size = isset($file_arr['size']) ? (int) $file_arr['size'] : (int) @filesize($tmp);

    if ($size <= 0) {
        return new WP_Error('empty_file', 'Berkas yang diunggah berukuran 0 byte.');
    }
    if ($size > 10 * 1024 * 1024) {
        return new WP_Error('too_large', 'Ukuran berkas melebihi batas aman 10 MB.');
    }

    $bytes = @file_get_contents($tmp);
    if ($bytes === false || $bytes === '') {
        return new WP_Error('read_err', 'Gagal membaca isi berkas yang diunggah.');
    }

    // Deteksi format biner & ekstensi
    if (substr($bytes, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
        return new WP_Error(
            'legacy_biff8_xls',
            'Berkas ini memakai format biner Excel lama (.xls BIFF8). Silakan klik File → Save As sebagai Excel Workbook (.xlsx), CSV UTF-8 (.csv), atau gunakan format JSON.'
        );
    }

    if (substr($bytes, 0, 2) === 'PK' || $ext === 'xlsx') {
        $parsed = ppb_io_xlsx_parse($bytes);
        if (is_wp_error($parsed)) return $parsed;
        $parsed['format'] = 'xlsx';
        return $parsed;
    }

    $trimmed_head = ltrim(substr($bytes, 0, 512));
    if (stripos($trimmed_head, '<Workbook') !== false || (stripos($trimmed_head, '<?xml') === 0 && stripos($bytes, 'schemas-microsoft-com:office:spreadsheet') !== false)) {
        $parsed = ppb_io_xls_xml_parse($bytes);
        if (is_wp_error($parsed)) return $parsed;
        $parsed['format'] = 'xls_xml';
        return $parsed;
    }

    $head_first = substr(ltrim(substr($bytes, 0, 3) === "\xEF\xBB\xBF" ? substr($bytes, 3) : $bytes), 0, 1);
    if ($ext === 'json' || $head_first === '[' || $head_first === '{') {
        $parsed = ppb_io_json_parse($bytes, $dataset_key);
        if (is_wp_error($parsed)) return $parsed;
        $parsed['format'] = 'json';
        return $parsed;
    }

    if (in_array($ext, array('csv', 'tsv', 'txt', ''), true)) {
        $parsed = ppb_io_csv_parse($bytes);
        if (is_wp_error($parsed)) return $parsed;
        $parsed['format'] = 'csv';
        return $parsed;
    }

    return new WP_Error('unsupported_format', 'Format berkas .' . esc_html($ext) . ' belum didukung. Gunakan .xlsx, .csv, atau .json.');
}
}

if (!function_exists('ppb_io_normalize_header_token')) {
function ppb_io_normalize_header_token($text) {
    $s = strtolower(trim((string) $text));
    // Hapus catatan dalam kurung di akhir seperti "(Wajib)" atau "(1-8)" saat mencocokkan nama kolom
    $s = preg_replace('/\*+/', '', $s);
    $s = preg_replace('/\([^)]*\)/', ' ', $s);
    $s = preg_replace('/[^a-z0-9]+/', '_', trim($s));
    return trim((string) $s, '_');
}
}

if (!function_exists('ppb_io_map_sheet_rows')) {
function ppb_io_map_sheet_rows($rows, $columns_def) {
    if (!is_array($rows) || empty($rows)) {
        return array('rows' => array(), 'present_cols' => array(), 'matched_count' => 0);
    }

    // Bangun kamus alias header -> canonical column key
    $alias_map = array();
    foreach ($columns_def as $col) {
        $key = (string) $col['key'];
        $candidates = array_merge(
            array($key, (string) ($col['label'] ?? '')),
            isset($col['aliases']) && is_array($col['aliases']) ? $col['aliases'] : array()
        );
        foreach ($candidates as $cand) {
            $tok = ppb_io_normalize_header_token($cand);
            if ($tok !== '' && !isset($alias_map[$tok])) {
                $alias_map[$tok] = $key;
            }
            // Juga simpan versi full tanpa membuang isi tanda kurung
            $tok_full = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string) $cand))), '_');
            if ($tok_full !== '' && !isset($alias_map[$tok_full])) {
                $alias_map[$tok_full] = $key;
            }
        }
    }

    // Pindai maksimal 5 baris pertama untuk menemukan baris header terbaik
    $best_row_idx = 0;
    $best_score   = -1;
    $best_col_map = array();
    $scan_limit   = min(5, count($rows));

    for ($r = 0; $r < $scan_limit; $r++) {
        $cand_row = is_array($rows[$r]) ? $rows[$r] : array();
        $col_map  = array();
        $score    = 0;
        foreach ($cand_row as $c_idx => $cell) {
            $cell_s = trim((string) $cell);
            if ($cell_s === '') continue;
            $t1 = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower($cell_s)), '_');
            $t2 = ppb_io_normalize_header_token($cell_s);
            $matched_key = null;
            if ($t1 !== '' && isset($alias_map[$t1])) {
                $matched_key = $alias_map[$t1];
            } elseif ($t2 !== '' && isset($alias_map[$t2])) {
                $matched_key = $alias_map[$t2];
            }
            if ($matched_key !== null && !in_array($matched_key, $col_map, true)) {
                $col_map[$c_idx] = $matched_key;
                $score++;
            }
        }
        if ($score > $best_score) {
            $best_score   = $score;
            $best_row_idx = $r;
            $best_col_map = $col_map;
        }
    }

    if ($best_score <= 0 || empty($best_col_map)) {
        return array('rows' => array(), 'present_cols' => array(), 'matched_count' => 0);
    }

    $present_cols = array_values(array_unique(array_values($best_col_map)));
    $out_rows     = array();
    $total_rows   = count($rows);

    for ($r = $best_row_idx + 1; $r < $total_rows; $r++) {
        $raw_row = is_array($rows[$r]) ? $rows[$r] : array();
        $first_non_empty = '';
        foreach ($raw_row as $rv) {
            if (trim((string) $rv) !== '') {
                $first_non_empty = trim((string) $rv);
                break;
            }
        }
        if ($first_non_empty === '' || strpos($first_non_empty, '#') === 0) {
            continue;
        }

        $assoc = array('_row_num' => $r + 1);
        $has_mapped_val = false;
        foreach ($best_col_map as $c_idx => $key) {
            $val = array_key_exists($c_idx, $raw_row) ? trim((string) $raw_row[$c_idx]) : '';
            $assoc[$key] = $val;
            if ($val !== '') {
                $has_mapped_val = true;
            }
        }
        if ($has_mapped_val) {
            $out_rows[] = $assoc;
        }
    }

    return array(
        'rows'          => $out_rows,
        'present_cols'  => $present_cols,
        'matched_count' => $best_score,
    );
}
}
