<?php
const PATTERN_I18N = "/data-i18n[ ]{0,}=[ ]{0,}\".*?\"/";
//output constants
const OUTPUT_OK = "OK.";
const OUTPUT_NEWLINE = " \n";
//output type constants
const SUCCESS = "sucess";
const INFO = "info";
const ERROR = "error";
//colors
const BLACK = "40";
const GREEN = "0;32";
const RED = "0;31";
const WHITE = "0;37";

error_reporting(E_ERROR);
/**
 * Print a message and new line on the terminal using the color based on the message's type
 * @param  string $message
 * @param  string $type
 * @return void
 */
function println($message = "", $type = INFO)
{
    $color = WHITE;
    if ($type == SUCCESS)
        $color = GREEN;
    else if ($type == ERROR)
        $color = RED;
    $output = "\033[" . $color . "m" . $message . "\033[0m";
    echo $output . OUTPUT_NEWLINE;
}
/**
 * Find the position of the Xth occurrence of a substring in a string
 * @param $haystack
 * @param $needle
 * @param $number integer > 0
 * @return int
 */
function strposX($haystack, $needle, $number = '1')
{
    if ($number == '1') {
        return strpos($haystack, $needle);
    } elseif ($number > '1') {
        return strpos($haystack, $needle, strposX($haystack, $needle, $number - 1) + strlen($needle));
    } else {
        return error_log('Error: Value for parameter $number is out of range');
    }
}

function putValue($start, $keys, $keyToInsert, $isLiteral = false)
{
    println("key => " . $keyToInsert);

    $path = [];
    $i = 0;
    $n = count($keys);
    while ($i < $n - 1) {
        $start = $start[$keys[$i]];
        array_push($path, $keys[$i]);
        $i++;
    }
    if ($isLiteral == true)
        $start[$keyToInsert] = " ";
    else
        $start[$keyToInsert] = array();

    array_push($path, $keyToInsert);
    $builder[$path[count($path) - 1]] = $start[$keyToInsert];
    for ($i = count($path) - 2; $i >= 0; $i--) {
        $builder[$path[$i]] = array($path[$i + 1] => $builder[$path[$i + 1]]);
    }
    
    //$builder = array_reverse($builder);
    //print_r($builder);
    //$aa = array_red($builder);
    //print_r($aa);
    return $builder;
}
function main($argv, $argc)
{
    $result = [];
    for ($i = 1; $i < $argc; $i++) {
        if (file_exists($argv[$i])) {
            $content =  file_get_contents($argv[$i]);
            preg_match_all(PATTERN_I18N, $content, $allMatchedArray);
            $allMatched = reset($allMatchedArray);
            foreach ($allMatched as $match) {
                $match = rtrim($match);
                $firstOccurence = strposX($match, "\"");
                $secondOccurence = strposX($match, "\"", '2');
                $match = substr($match, $firstOccurence + 1, $secondOccurence - 1 - $firstOccurence);

                if ($match[0] == '[') {
                    $lengthOfBracketString = 1;
                    $secondBracket = false;
                    while ($secondBracket != true) {
                        if ($match[$lengthOfBracketString] == ']')
                            $secondBracket = true;
                        else
                            $lengthOfBracketString++;
                    }
                    $match = substr($match, $lengthOfBracketString + 1, strlen($match) - $lengthOfBracketString);
                }

                $splittedArray = explode(".", $match);
                for ($i = 0; $i < count($splittedArray); $i++) {
                    if ($i == 0) {
                        if (!array_key_exists($splittedArray[0], $result))
                            $result[$splittedArray[0]] = array();
                    } else if ($i < count($splittedArray) - 1) {
                        $result[$splittedArray[0]] = putValue($result[$splittedArray[0]], array_slice($splittedArray, 1, $i), $splittedArray[$i]);
                    } else {
                        $result[$splittedArray[0]] = putValue($result[$splittedArray[0]], array_slice($splittedArray, 1, $i), $splittedArray[$i], true);
                    }
                }
            }
            print_r($result);
        } else {
            println($argv[i] . " does not exist!", ERROR);
        }
    }
}

main($argv, $argc);
