mapper_i18n
===========

mapper_i18n is a php script that automatically generate json translation files for html files containing jquery.i18n's DATA Api.

Quick start
-----------

```bash
git clone https://github.com/Dainerx/mapper_i18n
cd mapper_i18n
php mapper.php /path/to/file
```

Requires **PHP 7.0 or newer**

Usage
-----------
```bash
php mapper.php /path/to/file1 /path/to/file2 ... /path/to/fileN
```
If all is good: 
<img src=https://i.imgur.com/ldb9ZcN.jpg>

In case there your tags are malformed:
<img src=https://i.imgur.com/LoI5BWY.jpg>

**Note**: Unlike errors, in case of warning the mapper would still map these tags.
