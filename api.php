<?php

header('Content-type: text/json');

$content = iconv('windows-1254', 'utf-8', file_get_contents("http://www.koeri.boun.edu.tr/scripts/lst0.asp"));

preg_match_all("/<pre>(.*?)<\/pre>/s", $content, $pre);

$rows = explode("\n", str_replace(["<pre>", "</pre>"], "", $pre[0][0]));
for ($i = 0; $i < 7; $i++) array_shift($rows);

$rows = array_filter(array_map(function ($row) {

    $parts = explode('  ', $row);

    $parts = array_filter($parts, function ($row) {
        return strlen($row) > 1;
    });

    $parts = array_map(function ($part) {

        if ($part === '-.-') return null;

        return strip_tags(
            htmlspecialchars(
                str_replace(
                    ["\t", "\s", "\w", "\r", "\n"],
                    '',
                    trim($part)
                )
            )
        );
    }, $parts);

    return array_values($parts);

}, $rows), function ($row) {

    return count($row) > 2;
});

$arr = [];

foreach ($rows as $row) {

    $arr[] = [
        'fingerpass' => md5($row[0] . sprintf("%s,%s", $row[1], $row[2]) . $row[5] . $row[7]),
        'time' => array(
            'date' => explode(' ', $row[0])[0],
            'time' => explode(' ', $row[0])[1],
        ),
        'geolocation' => sprintf("%s,%s", $row[1], $row[2]),
        'depth' => $row[3],
        'ml' => $row[5],
        'location' => array(
            'full' => $row[7],
            'city' => str_replace(array('(', ')'), '', explode('(', $row[7])[1]),
            'state' => count(explode('-', explode('(', $row[7])[0])) > 1 ? explode('-', explode('(', $row[7])[0])[1] : explode('(', $row[7])[0],
            'district' => count(explode('-', explode('(', $row[7])[0])) > 1 ? explode('-', explode('(', $row[7])[0])[0] : null,
        ),
        'precision' => $row[8]
    ];
}

echo json_encode(array(
    'source' => 'http://www.koeri.boun.edu.tr',
    'data' => $arr
));
