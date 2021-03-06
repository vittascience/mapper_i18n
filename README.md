mapper_i18n
===========

mapper_i18n is a php script that automatically generate json translation files for html files containing [jquery.i18n's DATA Api](https://github.com/wikimedia/jquery.i18n).

Quick start
-----------

```bash
git clone https://github.com/Dainerx/mapper_i18n
cd mapper_i18n
php mapper.php -map /path/to/html/file
```

Requires **PHP 7.0 or newer**

Usage
-----------
**Map**

Map multiple files to generate .json translation files. 

```bash
php mapper.php -map /path/to/file1 /path/to/file2 ... /path/to/fileN
```
If all is good: 

![Imgur](https://i.imgur.com/ldb9ZcN.jpg "mapping successfully")

In case there your tags are malformed:

![Imgur](https://i.imgur.com/LoI5BWY.jpg "Error in mapping")


**Merge**

Merge .json translation files into one .json file.

```bash
php mapper.php -merge /path/to/file_to_merge_1.json /path/to/file_to_merge_2.json ... /path/to/file_to_merge_N.json /path/to/result_file.json
```

![Imgur](https://i.imgur.com/HoZtZ9j.jpg "Merging")

