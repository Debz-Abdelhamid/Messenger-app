<?php

function isEven(int $number): bool {
    return $number % 2 === 0;
}

$result = isEven(4);
var_dump($result); // يجب أن يعرض bool(true)

$result = isEven(5);
var_dump($result); // يجب أن يعرض bool(false)
