<?php

function stripVietnamese($str)
{
    $unicode = [
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd' => 'đ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
    ];

    foreach ($unicode as $nonAccent => $accents) {
        $str = preg_replace("/($accents)/i", $nonAccent, $str);
    }

    return $str;
}
if (! function_exists('highlightSearch')) {
    function highlightSearch(string $text, ?string $search, string $color = '#22c55e'): string
    {
        if (! $search) {
            return e($text);
        }

        $plainText = stripVietnamese(mb_strtolower($text));
        $plainSearch = stripVietnamese(mb_strtolower($search));

        $pos = mb_stripos($plainText, $plainSearch);

        if ($pos === false) {
            return e($text);
        }

        // Cắt chuỗi gốc theo vị trí match
        $before = mb_substr($text, 0, $pos);
        $match = mb_substr($text, $pos, mb_strlen($search));
        $after = mb_substr($text, $pos + mb_strlen($search));

        return e($before).
            '<span style="color:'.$color.';">'.e($match).'</span>'.
            e($after);
    }
}
