<?php

/**
 * Map all tags containing data-i18n of an input file(s) to a json file
 *
 * This script when launched and provided input files as arguments, will generate for each file
 * the json result of its data-i18n mapping. Sometimes these tags are not well formed, this script will display
 * warnings or errors telling you which tag is malformed.
 * 
 * PHP version 7.2
 *
 * @author     Oussama Ben Ghorbel <d.oussamabenghorbel@gmail.com>
 * @version    0.0.1
 * @link       https://github.com/Dainerx/mapper_i18n
 * @see        https://github.com/wikimedia/jquery.i18n
 */

const VERSION = "0.0.1";
//output constants
const OUTPUT_RUNNING = "Running mapper_i18n by Dainer(https://github.com/Dainerx) " . VERSION . " from " . __DIR__ . " ...\n";
//output constants
const OUTPUT_OK = "OK.";
const OUTPUT_NEWLINE = " \n";
const OUTPUT_JSON_EXTENSION = ".json";
//output type constants
const SUCCESS = "sucess";
const INFO = "info";
const WARNING = "warning";
const ERROR = "error";
//colors
const BLACK = "40";
const GREEN = "0;32";
const RED = "0;31";
const YELLOW = "1;33";
const WHITE = "0;37";
//other constants
const PATTERN_I18N = "/data-i18n[ ]{0,}=[ ]{0,}\".*?\"/"; //the ? here is needed for lazy matching

error_reporting(E_ERROR); // 3:)

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
    else if ($type == WARNING)
        $color = YELLOW;
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

/**
 * Recursively find the needed place to insert the key and value 
 * then backtrack to update the multidimensional array.
 * @param  array $records the global array that is going to result into the json object
 * @param  array $path an array of keys to follow as path
 * @param  mixed $step simple counter
 * @param  mixed $keyToInsert the key to insert 
 * @param  mixed $valueToInsert the value to insert can be an array or a literal
 * @return array
 */
function putKeyAndVal(&$records, $path, $step, $keyToInsert, $valueToInsert)
{
    foreach ($records as $key => $value) {
        if ($key == $path[$step]) {
            if ($path[count($path) - 1] == $key) {
                if (!array_key_exists($keyToInsert, $value))
                    $value[$keyToInsert] = $valueToInsert; //correct
                $records[$key] = $value; //correct
                return $records;
            } else {
                $records[$key] = putKeyAndVal($value, $path, $step + 1, $keyToInsert, $valueToInsert);
            }
        }
    }
    return $records;
}

function main($argv, $argc)
{
    $result = [];
    println(OUTPUT_RUNNING);
    println("Found $argc files");
    for ($fc = 1; $fc < $argc; $fc++) { //fc as for files counter
        $fileFullPath = $argv[$fc];
        if (file_exists($fileFullPath)) {
            $content =  file_get_contents($fileFullPath);
            preg_match_all(PATTERN_I18N, $content, $allMatchedArray);
            $allMatched = reset($allMatchedArray);
            $count_matches = count($allMatched);
            println("Found $count_matches tags in $fileFullPath");
            foreach ($allMatched as $match) {
                $match = rtrim($match);
                $firstOccurence = strposX($match, "\"");
                $secondOccurence = strposX($match, "\"", '2');
                //reduce the match to the string between the two double quotes
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
                if ($splittedArray[0] == "") { //if it hits here, there's a problem with the tag
                    println("Skipping this tag, please check your file near $match", ERROR);
                    continue;
                }

                //main builder block
                for ($i = 0; $i < count($splittedArray); $i++) {
                    $keyToInsert = $splittedArray[$i];
                    if ($i == 0) {
                        if (!array_key_exists($keyToInsert, $result))
                            $result[$keyToInsert] = array();
                    } else if ($i < count($splittedArray) - 1) {
                        putKeyAndVal($result, array_slice($splittedArray, 0, $i), 0, $keyToInsert, array());
                    } else {
                        if (strlen($keyToInsert) == 0)
                            println("This tag has no final key, please check your file near $match", WARNING);
                        putKeyAndVal($result, array_slice($splittedArray, 0, $i), 0, $keyToInsert, "TO_TRANSLATE");
                    }
                }
            }
            println(OUTPUT_OK, SUCCESS);
            $sepratedPath = explode("/", $fileFullPath);
            $fileName = $sepratedPath[count($sepratedPath) - 1];
            $fileNameExtensionFree = explode(".", $fileName)[0];
            file_put_contents($fileNameExtensionFree . OUTPUT_JSON_EXTENSION, json_encode($result, true));
            println("Generated " . $fileNameExtensionFree . OUTPUT_JSON_EXTENSION . " from " . $fileFullPath, SUCCESS);
        } else {
            println($argv[i] . " does not exist!", ERROR);
        }
    }
}

main($argv, $argc);
