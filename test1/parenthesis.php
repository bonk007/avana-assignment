<?php

function find_close_parenthesis(string $string, int $indexOfOpenParent): int
{
    if ($string[$indexOfOpenParent] !== '(') {
        return -1;
    }
    $i = $indexOfOpenParent;
    $open = 0;
    $found = false;

    while(isset($string[$i]) && false === $found) {
        if ($string[$i] === '(') {
            $open += 1;
        }
        if ($string[$i] === ')') {
            $open -= 1;
        }

        $found = $open < 1;
        $i++;
    }

    return $i - 1;
}

$jsonCases = file_get_contents(__DIR__ . '/cases.json');
$cases = json_decode($jsonCases, true);

foreach ($cases as $case) {
    [$string, $indexOfOpenParent, $expected] = $case;
    $result = find_close_parenthesis($string, $indexOfOpenParent);

    if ($result === $expected) {
        echo "Correct! input: {$indexOfOpenParent}, output: {$result}, expected: {$expected}" . PHP_EOL;
        continue;
    }
    echo "Incorrect! input: {$indexOfOpenParent}, output: {$result}, expected: {$expected}" . PHP_EOL;
}
