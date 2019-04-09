## Global functions

```PHP
lyx_config_set(string $name, mixed $value);
lyx_config_get(string $name = null, mixed $default = '');
lyx_config_unset(string $name);
lyx_config_merge(array $arr, string $merge_to = 'user_config');
lyx_app_config_merge(array $arr);
lyx_load_php(
  string $file,
  mixed $incl,
  mixed $func,
  bool $force_config = false
);
lyx_req(string $file);
lyx_req_once(string $file);
lyx_inc(string $file);
lyx_inc_once(string $file);
lyx_slash_dir(string $dir);
lyx_dircat();
lyx_has_flags(int $value, int $flags);
lyx_include(mixed $a1, mixed $a2 = null);
lyx_require(mixed $a1, mixed $a2 = null);
lyx_configf(mixed $a1, mixed $a2 = null);
lyx_import(mixed $imports);
lyx_millitime();
lyx_msleep(int $milliseconds);
lyx_set_html_utf8();

// Useful for debug functions
lyx_pre_r(array $arr);
lyx_pre_rx(array $arr);
lyx_block_r(
  mixed $arr,
  string $title = '',
  string $type = 'debug',
  mixed $expanded = null
);
lyx_var_dump_return(mixed $var);
lyx_block_dump(
  mixed $var,
  string $title = '',
  string $type = 'debug',
  mixed $expanded = null
);
lyx_dbg(
  mixed $arr,
  string $title = 'Debug',
  string $in_fn = '',
  string $on_line = ''
);
lyx_dbgx(
  mixed $arr,
  string $title = 'Debug',
  string $in_fn = '',
  string $on_line = ''
);
lyx_dbg_dump(
  mixed $var,
  string $title = '',
  string $in_fn = '',
  string $on_line = ''
);
lyx_dbg_dumpx(
  mixed $var,
  string $title = '',
  string $in_fn = '',
  string $on_line = ''
);
lyx_debug();
lyx_debugx();
lyx_debug_print_r();
lyx_debug_print_r_return(mixed $a, int $insize = 0);
lyx_debug_block_begin();
lyx_debug_block_end();
lyx_debug_block(mixed $inblock);
lyx_debug_var_dump();
lyx_debugx_var_dump();
lyx_debug_tab(int $length = LYX_DEBUG_TAB_LENGTH);
lyx_print();
lyx_println();
lyx_printbr();
```

## Class tree

### `Lyx` class
```PHP
Lyx                      // Basic class with some handfull functions
 ├─ ::Logger()           // Returns the default logger
 ├─ ::import()           // Alias of lyx_import function
 ├─ ::configFile()       // Alias of lyx_configf function
 ├─ ::requireFile()      // Alias of lyx_require function
 ├─ ::includeFile()      // Alias of lyx_include function
 ├─ ::lyxConfig()        // 1 param := lyx_config_get, 2 params := lyx_config_set
 ├─ ::appsConfigMerge()  // Alias of lyx_app_config_merge function
 ├─ ::userConfigMerge()  // Alias of lyx_config_merge function
 ├─ ::addIncludePath()   // Safely add PHP include path
 ├─ ::pre_r()            // Alias of lyx_pre_r function
 ├─ ::pre_rx()           // Alias of lyx_pre_rx function
 ├─ ::block_r()          // Alias of lyx_block_r function
 ├─ ::block_dump()       // Alias of lyx_block_dump function
 ├─ ::dbg()              // Alias of lyx_dbg function
 ├─ ::dbgx()             // Alias of lyx_dbgx function
 ├─ ::dbg_dump()         // Alias of lyx_dbg_dump function
 ├─ ::dbg_dumpx()        // Alias of lyx_dbg_dumpx function
 ├─ ::debug()            // Alias of lyx_debug function
 ├─ ::debugx()           // Alias of lyx_debugx function
 ├─ ::print()            // Alias of lyx_print function
 ├─ ::println()          // Alias of lyx_println function
 ├─ ::printbr()          // Alias of lyx_printbr function
 └─ ::redirect()         // Redirects uses Location header
```

## Namespace tree

### `Lyx` namespace `Console` class

```PHP
Lyx
 └─ Console     // Class for generating friendly debug views
    ├─ __construct();
    ├─ clear();
    ├─ beginBlock(...);
    ├─ endBlock();
    ├─ writeLine($line);
    ├─ writeValue($name, $value, $display_type = false);
    └─ flush();
```

### `Lyx` namespace `ConsoleBlock` class

```PHP
Lyx
 └─ ConsoleBlock  // Class for generating friendly debug blocks
    ├─ __construct(
    │    $name = null,
    │    $value = null,
    │    $display_type = false,
    │    $parent_block = null
    │  );
    ├─ getParent();
    ├─ setParent($parent_block);
    ├─ setName($name);
    ├─ getName();
    ├─ setValue($value);
    ├─ getValue();
    ├─ valueToString();
    ├─ addBlock();
    ├─ flushHeader($insize = 0);
    └─ hasChildren();
```

### `Lyx\Base` namespace `ObjectProperties` class

```PHP
Lyx\Base
 └─ ObjectProperties     // Class for automatic object getters/setters
    ├─ __get($name);
    ├─ __set($name, $value);
    ├─ __isset($name);
    ├─ hasProperty($name);
    ├─ canGetProperty($name);
    └─ canSetProperty($name);
```

### `Lyx\Db` namespace `Database` class

```PHP
Lyx\Db
 └─ Database             // Class for manipulating mysql database data
    ├─ __construct();    // Alias of $this->connect(...)
    ├─ connect(...);     // Uses DbInfo class to analyze the connection params
    ├─ isConnected();
    ├─ reset($keepdb = false);
    ├─ disconnect();
    ├─ delete($table, $conditions = '', $params = []);
    ├─ select(mixed $columns = '*', string $option = '');
    ├─ from($tables);
    ├─ update(
    │    $table, 
    │    $params, 
    │    $conditions = null /* array('id=:id', array(...)) */
    │  );
    ├─ insert($table, $columns);
    ├─ where($conditions, $params = []);
    ├─ limit($limit, $offset = null);
    ├─ offset($offset);
    ├─ orderBy($fields);
    ├─ query($query, $bind = null);
    ├─ bindValue($pos, $value = null, $type = null);
    ├─ bindParam($pos, &$param = null, $type = null);
    ├─ bind($pos, &$value = null, $type = null, $func = 'bindValue');
    ├─ execute($input_parameters = []);
    ├─ exec($statement);
    ├─ resultScalar($column_number = 0);
    ├─ resultAll();
    ├─ resultRow($fetch_obj = false);
    ├─ resultColumn();
    ├─ lastInsertId();
    ├─ beginTransaction();
    ├─ commitTransaction();
    ├─ rollbackTransaction();
    ├─ rowCount();
    ├─ queryCounter();
    ├─ debugDumpParams();
    ├─ errorInfo();
    ├─ truncateTable($table);
    ├─ getTableStatus($table, $field = null);
    ├─ getTableAutoIncrement($table);
    ├─ countRows($table);
    ├─ getTables();
    └─ quoteTableName($name);
```

### `Lyx\Db` namespace `DbInfo` class

```PHP
Lyx\Db
 └─ DbInfo             // Class for handling database connection info
    ├─ $driver;
    ├─ $host;
    ├─ $port;
    ├─ $unix_socket;
    ├─ $dbname;
    ├─ $username;
    ├─ $password;
    ├─ __construct();  // Alias of $this->set(...)
    ├─ set(...);
    ├─ reset();
    ├─ loadFromKeys($arr);
    ├─ parseDsn($dsn);
    ├─ compilePdoDsn($addCredentials = false, $defaultDriver = 'mysql');
    └─ parseDsn($dsn);
```

### `Lyx\Geom` namespace `Point` class

```PHP
Lyx\Geom
 └─ Point                 // Class for manipulating x, y
    ├─ $x;
    ├─ $y;
    ├─ __construct(...);
    ├─ resetPoint();
    ├─ setPoint($x, $y);
    ├─ setX($x);
    ├─ setY($y);
    ├─ getPoint();
    └─ toString();
```

### `Lyx\Geom` namespace `Rect` class

```PHP
Lyx\Geom
 └─ Rect                 // Class for manipulating x, y, width, height
    ├─ $x;
    ├─ $y;
    ├─ $width;
    ├─ $height;
    ├─ __construct(...);
    ├─ resetRect();
    ├─ setRect($x, $y, $w, $h);
    ├─ setSize($width, $height);
    ├─ setPoint($x, $y);
    ├─ setWidth($width);
    ├─ setHeight($height);
    ├─ setX($x);
    ├─ setY($y);
    ├─ getSize();
    ├─ getPoint();
    ├─ getX();
    ├─ getY();
    ├─ getWidth();
    ├─ getHeight();
    ├─ getLeft();
    ├─ getTop();
    ├─ getRight();
    ├─ getBottom();
    ├─ getTotalWidth();
    ├─ setTotalWidth($totalwidth);
    ├─ getTotalHeight();
    └─ setTotalHeight($totalheight);
```

### `Lyx\Geom` namespace `Rotation` class

```PHP
Lyx\Geom
 └─ Rotation               // Class some useful rotation functions
    ├─ ::rotateX($x, $y, $radians);
    ├─ ::rotateY($x, $y, $radians);
    └─ ::getRotBoxBounds($width, $height, $angle /* degrees */);
```

### `Lyx\Geom` namespace `Size` class

```PHP
Lyx\Geom
 └─ Size                 // Class for manipulating width, height
    ├─ $width;
    ├─ $height;
    ├─ __construct(...);
    ├─ resetSize();
    ├─ setSize();
    ├─ setWidth($width);
    ├─ getWidth();
    ├─ setHeight($height);
    ├─ getHeight();
    ├─ getSize();
    └─ toString();
```

### `Lyx\Graphics` namespace `Color` class

```PHP
Lyx\Graphics
 └─ Color                 // Class for manipulating colors
    ├─ $r;
    ├─ $g;
    ├─ $b;
    ├─ $a;
    ├─ __construct(...);
    ├─ __toString();
    ├─ getRed();
    ├─ setRed($value);
    ├─ getGreen();
    ├─ setGreen($value);
    ├─ getBlue();
    ├─ setBlue($value);
    ├─ getAlpha();
    ├─ setAlpha($value);
    ├─ setRgb($r, $g, $b);
    ├─ getPhpAlpha();
    ├─ hasAlpha();
    ├─ isTransparent();
    ├─ resetAlpha();
    ├─ getOpacity();
    ├─ setOpacity($opacity);
    ├─ resetColor();
    ├─ toBinary($forcealpha = false);
    ├─ toHex($removealpha = true);
    ├─ toHexa($forcealpha = false);
    ├─ toCssHex($removealpha = true);
    ├─ toCssHexa($forcealpha = false);
    ├─ toCssRgb($removealpha = true);
    ├─ toCssRgba($forcealpha = false);
    ├─ toCssHsl($removealpha = true);
    ├─ toCssHsla($forcealpha = false);
    ├─ toCssName($ifnotfound = self::CSS_HEX);
    ├─ toArray($keys = '' /* could be 'r,g,b' */);
    ├─ toRgbArray($keys = '');
    ├─ getRgbArray($keys = '', $round = false);
    ├─ toHslArray($keys = '', $round = true);
    ├─ cloneColor();
    ├─ toRgbString();
    ├─ toHslString($round = true);
    ├─ ::isColorName($name);
    ├─ ::nameToHex($color_name);
    ├─ ::isNameColor($hexcolor);
    ├─ ::hexToName($hexcolor);
    ├─ ::detectColor(
    │    $color,
    │    $type = self::RGB,
    │    $forcealpha = false,
    │    $round = true
    │  );
    ├─ ::rgbaToType(
    │    $r,
    │    $g,
    │    $b,
    │    $a = NULL,
    │    $type = self::RGB,
    │    $round = true
    │  );
    ├─ ::rgbaArrayToType($ca, $type = self::RGB, $round = true);
    ├─ ::colorToArray($color, $forcealpha = false, $round = true);
    ├─ ::colorToObject($color, $forcealpha = false, $round = true);
    ├─ ::colorToBinary($color, $forcealpha = false);
    ├─ ::colorToRgb($color, $round = true);
    ├─ ::colorToRgba($color, $forcealpha = false, $round = true);
    ├─ ::colorToHex($color);
    ├─ ::colorToHexa($color, $forcealpha = false);
    ├─ ::colorToCssHex($color);
    ├─ ::colorToCssHexa($color, $forcealpha = false);
    ├─ ::colorToCssRgb($color);
    ├─ ::colorToCssRgba($color, $forcealpha = false);
    ├─ ::colorToCssHsl($color);
    ├─ ::colorToCssHsla($color, $forcealpha = false);
    ├─ ::colorToCssName($color);
    ├─ ::rgbToBinary($r, $g = 0, $b = 0);
    ├─ ::rgbaToBinary($r, $g = 0, $b = 0, $a = null);
    ├─ ::rgbToObject($r, $g, $b, $keys = '', $round = false);
    ├─ ::rgbaToObject($r, $g, $b, $a = null, $keys = '', $round = false);
    ├─ ::rgbToArray($r, $g, $b, $keys = '', $round = false);
    ├─ ::rgbaToArray($r, $g, $b, $a = null, $keys = '', $round = false);
    ├─ ::rgbToHex($r, $g = 0, $b = 0);
    ├─ ::rgbaToHexa($r, $g = 0, $b = 0, $a = null);
    ├─ ::rgbToCssHex($r, $g = 0, $b = 0);
    ├─ ::rgbaToCssHexa($r, $g = 0, $b = 0, $a = null);
    ├─ ::rgbToCssRgb($r, $g = 0, $b = 0);
    ├─ ::rgbaToCssRgba($r, $g = 0, $b = 0, $a = null);
    ├─ ::rgbToCssHsl($r, $g = 0, $b = 0);
    ├─ ::rgbaToCssHsla($r, $g = 0, $b = 0, $a = null);
    ├─ ::rgbToCssName($r, $g = 0, $b = 0, $a = null, $ifnotfound = self::CSS_HEX);
    ├─ ::hslToArray($h, $s, $l, $keys = '', $round = false);
    ├─ ::hslaToArray($h, $s, $l, $a = null, $keys = '', $round = false);
    ├─ ::hslToCssHsl($h, $s = 0, $l = 0);
    ├─ ::hslaToCssHsla($h, $s = 0, $l = 0, $a = null);
    ├─ ::cssToRgb($color);
    ├─ ::cssToRgba($color);
    ├─ ::isCssTransparent($color);
    ├─ ::isHex($color);
    ├─ ::isCssRgb($color, &$out = null);
    ├─ ::isCssRgba($color, &$out = null);
    ├─ ::parseCssRgb($r, $g, $b);
    ├─ ::parseCssRgba($r, $g, $b, $a = null);
    ├─ ::isCssHsl($color, &$out = null);
    ├─ ::isCssHsla($color, &$out = null);
    ├─ ::parseCssHsl($h, $s, $l);
    ├─ ::parseCssHsla($h, $s, $l, $a = null);
    ├─ ::rgbToHsl($r, $g = 0, $b = 0, $a = null);
    ├─ ::rgbaToHsla($r, $g = 0, $b = 0, $a = null);
    ├─ ::hslToRgb($h, $s = 0, $l = 0, $a = null);
    ├─ ::rgbfToHslf($r, $g, $b);
    ├─ ::hslfToRgbf($h, $s, $l);
    ├─ ::hue_to_rgb($v1, $v2, $vh);
    ├─ ::binaryToRgb($color);
    ├─ ::binaryToRgba($color);
    ├─ ::hexToRgb($hexcolor);
    ├─ ::hexaToRgba($hexcolor);
    ├─ ::hexColorExpand($hexcolor);
    ├─ ::hexColorClearSign($hexcolor);
    ├─ ::alphaToOpacity($alpha);
    ├─ ::opacityToAlpha($opacity);
    ├─ ::alphaToPhpAlpha($alpha);
    ├─ ::phpAlphaToAlpha($phpalpha);
    └─ ::between($value, $min, $max);
```

### `Lyx\Graphics\Core` namespace

```PHP
Lyx\Graphics\Core
 └─ imagecopymerge_alpha   // Function to copy/merge images with alpha channel
```

### `Lyx\Graphics` namespace `Gradient` class

```PHP
Lyx\Graphics
 └─ Gradient               // Class for creating gradient graphics
    ├─ $colors;
    ├─ $color_start;
    ├─ $color_end;
    ├─ $width;
    ├─ $height;
    ├─ $axis;
    ├─ $rotation;
    ├─ $im;
    ├─ __construct(...);
    ├─ init();
    ├─ resetColors();
    ├─ getWidth();
    ├─ setWidth($width);
    ├─ getHeight();
    ├─ setHeight($height);
    ├─ getAxis();
    ├─ setAxis($axis);
    ├─ getRotation();
    ├─ setRotation($rotation);
    ├─ validRotation($rotation);
    ├─ hasRotation();
    ├─ addColor($color, $position = '');
    ├─ addColors(...);
    ├─ getAllColors();
    ├─ generate();
    ├─ ::linearagradientfill(
    │    $width,
    │    $height,
    │    startColor,
    │    $endColor,
    │    $axis = 'x'
    └  );
```

### `Lyx\Graphics` namespace `Image` class

```PHP
Lyx\Graphics
 └─ Image               // Class for manipulating images
    ├─ $src;
    ├─ $image_type;
    ├─ $mime;
    ├─ $bits;
    ├─ $width;
    ├─ $height;
    ├─ $antialias;
    ├─ $transparent;
    ├─ __construct(...);
    ├─ setImage($src);
    ├─ openImage(...);
    ├─ fromResource($resource);
    ├─ hasImageData();
    ├─ getWidth();
    ├─ getHeight();
    ├─ createImage($width, $height, $transparent = true);
    ├─ fullFill($r, $g = 0, $b = 0);
    ├─ setTransparent($value = true);
    ├─ getTransparent();
    ├─ setAntialiasing($value = true);
    ├─ getAntialiasing();
    ├─ fillTransparent(
    │    $r = Color::RGB_MAX,
    │    $g = Color::RGB_MAX,
    │    $b = Color::RGB_MAX
    │  );
    ├─ resetData();
    ├─ clear();
    ├─ bitCopyMergeAlpha(
    │    $src_im,
    │    $dst_x,
    │    $dst_y,
    │    $src_x,
    │    $src_y,
    │    $src_w,
    │    $src_h,
    │    $pct = 100
    │  );
    ├─ bitCopyMerge(
    │    $src_im,
    │    $dst_x,
    │    $dst_y,
    │    $src_x,
    │    $src_y,
    │    $src_w,
    │    $src_h,
    │    $pct = 100
    │  );
    ├─ bitCopy(
    │    $src_im,
    │    $dst_x,
    │    $dst_y,
    │    $src_x,
    │    $src_y,
    │    $src_w,
    │    $src_h
    │  );
    ├─ writeFile($file, $type = null, $a1 = null, $a2 = null);
    ├─ write($type = null, $a1 = null, $a2 = null);
    ├─ write_image($file = null, $type = null, $a1 = null, $a2 = null);
    └─ this_or_image($im);
```

### `Lyx\Graphics` namespace `ImageTypes` class

```PHP
Lyx\Graphics
 └─ ImageTypes               // Class for manipulating images
    └─ ::typeToMimeType($type);
```

### `Lyx\Math` namespace `BCMath` class

```PHP
Lyx\Math
 └─ BCMath                  // Class with some BC math functions
    ├─ ::bcceil($number);
    ├─ ::bcfloor($number);
    └─ ::bcround($number, $precision = 0);
```

### `Lyx\Math` namespace `Math` class

```PHP
Lyx\Math
 └─ Math                    // Class with some math functions
    └─ ::getProgress($min, $max, $value, $retObject = true);
```

### `Lyx\Strings` namespace `Str` class

```PHP
Lyx\Strings
 └─ Str                    // Class for manipulating strings
    ├─ ::_format($text, $params);
    ├─ ::format(
    │    $text,
    │    $params = [],
    │    $options = false
    │  );
    ├─ ::formatNumber(
    │    $number,
    │    $format,
    │    $dec_point = self::DEFAULT_DEC_POINT,
    │    $thousands_sep = self::DEFAULT_THOUSANDS_SEP
    │  );
    ├─ ::removeContainers($text, $containers = "'");
    ├─ ::parseFunctionParameters($expression, $params = []);
    ├─ ::replace($text, $arg1, $arg2 = null);
    ├─ ::hidePassword($pwd, $doubleRep = true, $passwordChar = '*');
    ├─ ::singleQuote($str);
    ├─ ::doubleQuote($str);
    ├─ ::sandwich($str, $starts = null, $ends = null, $case = true);
    ├─ ::startsWith($haystack, $needle, $case = true, &$rest = null);
    ├─ ::endsWith($haystack, $needle, $case = true, &$rest = null);
    ├─ ::unicodeDecode($string);
    ├─ ::correctEncoding($string);
    ├─ ::shortText($text, $chars = 100, $append = '...');
    ├─ ::utf8ToHtmlEntities($string);
    ├─ ::untone($string);
    ├─ ::removeAccent($string, $overrides = null);
    ├─ ::postSlug($string);
    ├─ ::pad(
    │    $string,
    │    $pad_length,
    │    $pad_char = ' ',
    │    $pad_option = self::PADSTR_RIGHT
    │  );
    ├─ ::padRight($string, $pad_length, $pad_char = ' ');
    ├─ ::padLeft($string, $pad_length, $pad_char = ' ');
    ├─ ::random($length = 16);
    ├─ ::quickRandom($length = 16);
    └─ ::matchWithWildcard($source, $pattern);
```

### `Lyx\Strings\DateTime\Locales` namespace `Greek` class

```PHP
Lyx\Strings\DateTime\Locales
 └─ Greek                    // Class for returning Greek form days/months
    ├─ format($format);
    ├─ setTimestamp($unixtimestamp);
    └─ getTimestamp();
```

### `Lyx\System` namespace `Daemon` class

```PHP
Lyx\System
 └─ Daemon                    // Class for returning Greek form days/months
    ├─ log($message, $level);
    ├─ logText($text, $add_eol = true);
    ├─ ::getLogLevelText($log_level);
    ├─ isClosing();
    ├─ parseCmdArgs();
    ├─ getArg($name, $default = '');
    ├─ arg($name, $default = '');
    ├─ writeInitD($log = true);
    ├─ start();
    ├─ startIfDaemon();
    ├─ stop();
    └─ iterate($sleepSeconds = 0);
```

### `Lyx\System` namespace `Fork` class

```PHP
Lyx\System
 └─ Fork                    // Class for forking PHP processes
    ├─ __construct($arg = null);
    ├─ attach();
    ├─ autoName();
    ├─ setRunnable($runnable);
    ├─ getRunnable();
    ├─ runnableOk($runnable);
    ├─ getPid();
    ├─ getName();
    ├─ setName($name);
    ├─ isAlive();
    ├─ start(...);
    ├─ addSignalHandler($signo, $handler);
    ├─ stop($signal = SIGTERM, $wait = false);
    ├─ kill($signal = SIGKILL, $wait = false);
    ├─ getError($code);
    └─ signalHandler($signal);
```

### `Lyx\System` namespace `FS` class

```PHP
Lyx\System
 └─ FS                    // Class containing static filesystem functions
    ├─ ::slash($path, $slash_char = DIRECTORY_SEPARATOR);
    ├─ ::removeSlash($path, $ds = DIRECTORY_SEPARATOR);
    ├─ ::fileExists($path);
    ├─ ::dirExists($path);
    ├─ ::linkExists($path);
    ├─ ::directoryTree($dir, $user_options = [
    │    'only_dirs' => false,
    │    'dot_dirs' => true,
    │    'hidden_dirs' => true,
    │    'hidden_files' => true,
    │    'recursive' => true,
    │    'flat_model' => false,
    │    'fs_include_path' => true,
    │    'flat_model_root' => '',
    │    'sort' => 'asc'
    │  ]);
    ├─ ::countFiles($path, $user_options = [
    │    'dot_dirs' => false,
    │    'recursive' => true
    │  ]);
    ├─ ::replaceLine($filename, $search_pattern, $new_line, $append = false);
    ├─ ::removeLine($filename, $search_pattern);
    ├─ ::killTree($dir, $user_options = [
    │    'delete_root' => true
    │  ]);
```

### `Lyx\System` namespace `Path` class

```PHP
Lyx\System
 └─ Path                    // Class containing static path functions
    ├─ ::documentRoot($addition = '', $slashed = false);
    ├─ ::siteRoot($path);
    ├─ ::homeDir($append = '');
    ├─ ::compose(...);
    └─ ::real(...);
```

### `Lyx\System` namespace `Semaphore` class

```PHP
Lyx\System
 └─ Semaphore                    // Class using semaphore shared memory
    ├─ __construct($pathname, $proj);
    ├─ hasValidKey();
    ├─ set($name, $value);
    ├─ setop($n, $op, $val = null);
    ├─ get($name, $default = '');
    ├─ uset($name);
    ├─ getVariables();
    └─ putVariables();
```

### `Lyx\System` namespace `System` class

```PHP
Lyx\System
 └─ System                    // Class containing system functions
    ├─ ::processExists($pid, &$info = null);
    ├─ ::findProcessByPid($pid, &$info = null);
    ├─ ::findProcessByName($pname, &$infos = null);
    ├─ ::findProcess($name, $value, &$infos)
    ├─ ::command($cmd, $args_title = null);
    ├─ ::getServices();
    └─ ::serviceExists($name);
```

### `Lyx\System` namespace `Terminal` class

```PHP
Lyx\System
 └─ Terminal                  // Class for using terminal colors and utilities
    ├─ ::print($str);
    ├─ ::println($str);
    ├─ ::printRaw($str);
    ├─ ::printlnRaw($str);
    ├─ ::printTmpl($str, $tmpl);
    ├─ ::buildEscapeSequence(
    │    $pre_props,
    │    $text = null,
    │    $post_props = []
    │  );
    ├─ ::getCXY();
    ├─ ::beep();
    ├─ ::lineTitle($title, $fill = '_');
    ├─ ::underlineTitle($title, $fill = '-');
    └─ ::uolineTitle($title, $fill = '-');
```

### `Lyx\Utils\Conversions` namespace `Base64` class

```PHP
Lyx\Utils\Conversions
 └─ Base64                  // Class for coding/decoding base64 urls
    ├─ ::base64UrlEncode($input);
    └─ ::base64UrlDecode($input);
```

### `Lyx\Utils` namespace `BenchTime` class

```PHP
Lyx\Utils
 └─ BenchTime                    // Class measuring tasks time
    ├─ __construct($doStart = false);
    ├─ __toString();
    ├─ reset();
    ├─ start();
    ├─ stop();
    ├─ checkpoint($name);
    ├─ resetCheckpoints();
    ├─ elapsed();
    └─ toString($format = null);
```

### `Lyx\Utils` namespace `Config` class

```PHP
Lyx\Utils
 └─ Config                    // Class for storing configuration and data
    ├> implements \ArrayAccess, \Iterator
    ├─ __construct($params = null);
    ├─ __set($name, $value);
    ├─ &__get($name);
    ├─ __unset($name);
    ├─ __isset($name);
    ├─ override($params);
    ├─ merge($params);
    ├─ get($name = null, $default = null);
    ├─ set($name, $value);
    ├─ setParamsByRef(&$params);
    ├─ offsetSet($offset, $value);
    ├─ uset($name);
    ├─ has($name);
    ├─ getJSON($name = null, $default = null);
    ├─ getConfig($name, $default = []);
    ├─ isEmpty($name);
    ├─ offsetExists($offset);
    ├─ offsetUnset($offset);
    ├─ offsetUnset($offset);
    ├─ offsetGet($offset);
    ├─ rewind();
    ├─ current();
    ├─ key();
    ├─ next();
    ├─ valid();
    ├─ setShortcut();
    ├─ resolveName($name);
    ├─ clearShortcut();
    ├─ getParameters();
    ├─ toJSON();
    ├─ setParameters($params);
    ├─ updateSysConfig();
    └─ isSystemConfig();
```

### `Lyx\Utils` namespace `ConfigUnmethotable` class

```PHP
Lyx\Utils
 └─ ConfigUnmethotable         // Class for storing configuration and data
    ├> extends Lyx\Utils\Config
    ├─ __construct($params = null);
```

### `Lyx\Utils` namespace `EncryptedJsonResult` class

```PHP
Lyx\Utils
 └─ EncryptedJsonResult        // Class for storing configuration and data
    ├> extends Lyx\Utils\JsonResult
    ├─ __construct($key = '');
    ├─ Supported();
    └─ exit();
```

### `Lyx\Utils` namespace `MCrypter` class

```PHP
Lyx\Utils
 └─ MCrypter                  // Class for encryption using mcrypt functions
    ├─ $cypher = 'rijndael-256';
    ├─ $mode = 'cfb';
    ├─ $key = null;
    ├─ ::Supported();
    ├─ init();
    ├─ encrypt($plaintext);
    ├─ decrypt($crypttext);
    ├─ verifyModuleOpen($td);
    └─ verifyGenericInit($init)
```

### `Lyx\Utils` namespace `OpenSSLEncrypter` class

```PHP
Lyx\Utils
 └─ OpenSSLEncrypter         // Class for encryption using openssl functions
    ├─ DefaultCypher = 'aes-256-cbc'
    ├─ $cypher;
    ├─ $iv = '';
    ├─ $key = null;
    ├─ $base64 = true;
    ├─ $withIV = true;
    ├─ __construct($key = '', $cypher = self::DefaultCypher);
    ├─ ::Supported();
    ├─ encrypt($plainText);
    └─ decrypt($cryptText);
```

### `Lyx\Utils` namespace `Quota` class

```PHP
Lyx\Utils
 └─ Quota                    // Class calculating quotas
    ├─ PERCENT = 1;
    ├─ PERMILLE = 2;
    ├─ PERTENTHOUSAND = 3;
    ├─ FRACTION = 4;
    ├─ DEFAULT_TYPE = self::PERCENT;
    ├─ PERCENT_SIGN = '&#37;';
    ├─ PERMILLE_SIGN = '&#8240;';
    ├─ PERTENTHOUSAND_SIGN = '&#8241;';
    ├─ PERCENT_MIN = 0;
    ├─ PERCENT_MAX = 100;
    ├─ PERMILLE_MIN = 0;
    ├─ PERMILLE_MAX = 1000;
    ├─ PERTENTHOUSAND_MIN = 0;
    ├─ PERTENTHOUSAND_MAX = 10000;
    ├─ FRACTION_MIN = 0.0;
    ├─ FRACTION_MAX = 1.0;
    ├─ BOUNDS_FAIL = 1; // returns FALSE if quota is out of bounds
    ├─ BOUNDS_IN = 2; // limits the bounds to MIN if < MIN and MAX if > MAX
    ├─ BOUNDS_OUT = 3; // bounds are free ex.: PERCENT can have an 120% quota
    ├─ DEFAULT_BOUNDS = self::BOUNDS_IN;
    ├─ $source;
    ├─ $value;
    ├─ $type;
    ├─ $bounds;
    ├─ __construct(...);
    ├─ init();
    ├─ resetQuota();
    ├─ setQuota($value, $type = false, $bounds = false);
    ├─ getQuota();
    ├─ toQuota($format = '');
    ├─ toString();
    ├─ getTypeName();
    ├─ getBoundName();
    ├─ setValue($value);
    ├─ getValue();
    ├─ setType($type);
    ├─ getType();
    ├─ setBounds($bounds);
    ├─ getBounds();
    ├─ setSource($value);
    ├─ getSource();
    ├─ translate($quotafrom);
    ├─ ::scanQuotaType($value, $bounds = self::DEFAULT_BOUNDS);
    ├─ ::isValidQuota(
    │    $value,
    │    $type = self::DEFAULT_TYPE,
    │    $bounds = self::DEFAULT_BOUNDS
    │  );
    ├─ ::getQuotaValue(
    │    $value,
    │    $type = self::DEFAULT_TYPE,
    │    $bounds = self::DEFAULT_BOUNDS
    │  );
    ├─ ::isValidPercent($value, $bounds = self::BOUNDS_IN);
    ├─ ::isValidPermille($value, $bounds = self::BOUNDS_IN);
    ├─ ::isValidPertenthousand($value, $bounds = self::DEFAULT_BOUNDS);
    ├─ ::isValidFraction($value, $bounds = self::BOUNDS_IN);
    ├─ ::getPercentValue($value, $bounds = self::BOUNDS_IN);
    ├─ ::getPermilleValue($value, $bounds = self::BOUNDS_IN);
    ├─ ::getPertenthousandValue($value, $bounds = self::BOUNDS_IN);
    ├─ ::getFractionValue($value, $bounds = self::DEFAULT_BOUNDS);
    ├─ ::getTypeSign($type);
    ├─ ::hasSign($value, $type = self::DEFAULT_TYPE);
    ├─ ::isFraction($value);
    ├─ ::signToType($value);
    ├─ ::isTypeSignend($type);
    ├─ ::getTypeMin($type);
    ├─ ::getTypeMax($type);
    ├─ ::quotaToString($value, $type, $format = '');
    ├─ ::boundValue($value, $min, $max, $bounds);
    ├─ ::translateQuota($value, $quotafrom, $type, $bounds);
    ├─ ::quotaOrNumber(
    │    $value,
    │    $quotafrom,
    │    $default,
    │    $type = null,
    │    $bounds = null
    │  );
    ├─ ::signOrNumber(
    │    $value,
    │    $quotafrom,
    │    $default,
    │    $type = null,
    │    $bounds = null
    │  );
    ├─ ::fractionOrSignOrNumber(
    │    $value,
    │    $quotafrom,
    │    $default,
    │    $type = null,
    │    $bounds = null
    │  );
    ├─ ::typeName($type);
    └─ ::boundsName($bound);
```

### `Lyx\Utils` namespace `Session` class

```PHP
Lyx\Utils
 └─ Session               // Class for handling session data
    ├─ __construct($start = true);
    ├─ has($name);
    ├─ get($name, $default = null);
    ├─ set($name, $value);
    └─ remove($name);
```

### `Lyx\Utils` namespace `Types` class

```PHP
Lyx\Utils
 └─ Types           // Class containing static functions for manipulating types
    ├─ ::is_digit($digit);
    ├─ ::array_to_object($array, &$object = null);
    ├─ ::arrayToObject($array);
    ├─ ::array_ikeys($key, $array);
    ├─ ::array_ikey_get($key, $array);
    ├─ ::array_ikey_exists($key, $array);
    ├─ ::isAssocArray($array);
    ├─ ::&setByPath(
    │    $path,
    │    $value,
    │    &$array,
    │    $link_enable = true
    │  );
    ├─ ::&setLinkByPath($path, $link_path, &$array);
    ├─ ::isLinkValue($value, $link_regex = '');
    ├─ ::getLinkValue(
    │    $value,
    │    $link_extract_regex = '', // ^(@@|##)(.*)
    │    &$link_type = null
    │  );
    ├─ ::isNamePath($name, $char_separator = '.');
    ├─ ::issetByPath($path, $array);
    ├─ ::unsetByPath($path, $array);
    ├─ ::getByPath(
    │    $path,
    │    &$array,
    │    $default=null,
    │    $exists_mode=false,
    │    $unset=false,
    │    $link_enable=true,
    │    $do_set = false,
    │    $set_value = null
    │  );
    ├─ ::getLinkByPath(
    │    $path,
    │    &$array,
    │    $default = null,
    │    $exists_mode = false,
    │    $unset = false,
    │    $link_enable = true,
    │    $do_set = false,
    │    $set_value = null
    │  );
    ├─ ::getByPathCase($path, $array, $default = null);
    ├─ ::hasFlag();
    ├─ ::translateBool($val);
    ├─ ::toBool($val);
    ├─ ::toBoolStr($val, $true = 'true', $false = 'false');
    ├─ ::mergesort(&$array, $cmp_function = 'strcmp');
    └─ ::arrayOverride(array $base, array $override, array $params = []);
```

### `Lyx\Utils` namespace `Updater` class

```PHP
Lyx\Utils
 └─ Updater               // Class for updating based on time
    ├─ __construct($fnUpdate = null);
    ├─ start();
    ├─ stop();
    ├─ every($every);
    ├─ minutes();
    ├─ seconds();
    ├─ milliseconds();
    ├─ needsUpdate();
    ├─ finish(...);
    ├─ checkUpdate(...);
    └─ update(...);
```

### `Lyx\Web` namespace `Browser` class

```PHP
Lyx\Web
 └─ Browser           // Class containing static browser functions
    ├─ ::acceptedLanguages();
    └─ ::selectClientLanguage($priorities = ['en'], $default = 'en');
```

### `Lyx\Web` namespace `Dom` class

```PHP
Lyx\Web
 └─ Dom               // Class for manupulatying DOMDocument
    ├─ $document;
    ├─ $result;
    ├─ __construct(&$document_or_html = null);
    ├─ loadHTML($html);
    ├─ htmlToElements($html);
    ├─ importNode($node, $clone = false, $deep = true);
    ├─ append($what, $where);
    ├─ prepend($what, $where);
    ├─ insertBefore($what, $where);
    ├─ insertAfter($what, $where);
    ├─ remove($selector);
    ├─ exists($selector, \DOMNode $contextnode = null);
    ├─ find($selector, \DOMNode $contextnode = null);
    ├─ q($selector, \DOMNode $contextnode = null);
    ├─ getElementsByClassName($tagName, $className, &$parentNode = null);
    ├─ xpath($xpath_expression, \DOMNode $contextnode = null);
    ├─ writeHtml();
    ├─ injectJs(
    │    $js,
    │    $xpathAppendTo = '//body',
    │    $type = 'text/javascript',
    │    $id = null
    │  );
    ├─ injectJsTemplate($tmpl_html, $id, $xpathAppendTo = '//body')
    ├─ injectJsFile($js_file, $xpathAppendTo = '//body');
    ├─ injectJsFiles($js_files);
    ├─ injectCss($css, $xpathAppendTo = '//head');
    ├─ injectCssFile($css_file, $xpathAppendTo = '//head');
    ├─ injectCssFiles($css_files);
    └─ cssToXpath($path);
```

### `Lyx\Web` namespace `File` class

```PHP
Lyx\Web
 └─ File            // Class containing static remote/http file functions
    ├─ ::webFileExists($url);
    ├─ ::exists($url);
    ├─ ::urlFetch($cfg = [
    │    'getHeader' => false,
    │    'timeout' => 30
    │  ]);
    └─ ::getUrlDomain($url);
```

### `Lyx\Web` namespace `Html` class

```PHP
Lyx\Web
 └─ Html      // Class containing static html regex manipulation functions
    ├─ ::prependBaseTag($html, $baseUrl);
    ├─ ::prependHead($html, $prepend);
    ├─ ::appendHead($html, $append);
    ├─ ::appendCssFile($html, $css_file);
    └─ ::appendJsFile($html, $js_file);
```

### `Lyx\Web` namespace `HtmlTag` class

```PHP
Lyx\Web
 └─ HtmlTag    // Class containing static html tags generation functions
    ├─ ::tag($tag_name, $content, $attributes = []);
    ├─ ::strong($content, $attributes = []);
    └─ ::i($content, $attributes = []);
```

### `Lyx\Web` namespace `Http` class

```PHP
Lyx\Web
 └─ Http              // Class for fetching http urls and data
    ├─ __construct($url, $params = [], $postParams = []);
    ├─ reset($url, $params = [], $postParams = []);
    ├─ setFollowLocation($value = true);
    ├─ getFollowLocation();
    ├─ addHeaders($headers);
    ├─ addHeader($name, $value);
    ├─ removeHeader($name);
    ├─ setPostParams($postParams);
    ├─ setPostParamsRaw($postParamsRaw);
    ├─ addPostParam($name, $value);
    ├─ addParams($params);
    ├─ addParam($name, $value);
    ├─ removeParam($name);
    ├─ getResponse();
    ├─ getResponseBody();
    ├─ getResponseHeaders();
    ├─ getResponseHeadersAll();
    ├─ hasResponseHeader($name);
    ├─ getResponseHeader($name);
    ├─ getCookies();
    ├─ setPreserveCookies($value);
    ├─ setCookies($value);
    ├─ getPreserveCookies();
    ├─ getHttpStatusCode();
    ├─ hasLocation();
    ├─ getLocation();
    ├─ setCurlOptions($curlOptions);
    ├─ setCurlOption($option, $value);
    ├─ removeCurlOption($name);
    ├─ supportsCurlFollowLocation();
    ├─ get();
    ├─ post();
    └─ ::scanHeaderGlue($response, &$firstLine = null);
```

### `Lyx\Web` namespace `HttpAsync` class

```PHP
Lyx\Web
 └─ HttpAsync   // Class for keeping an http connection open and sending data
    ├─ __construct($doInit = true);
    ├─ __get($name);
    ├─ __set($name, $value);
    ├─ init();
    ├─ write($content);
    ├─ writeln($content);
    ├─ writebr($content);
    ├─ flushJs();
    ├─ js($js, $type = 'js');
    ├─ elementText($elSelector, $text);
    ├─ elementHTML($elSelector, $html);
    ├─ elementValue($elSelector, $value);
    ├─ console(...);
    └─ jq($jq);
```

### `Lyx\Web` namespace `Url` class

```PHP
Lyx\Web
 └─ Url      // Class containing static handy url related functions
    ├─ URL_SEPARATOR = '/';
    ├─ ::compose();
    └─ ::uriQueryStringHasSeoUrl(&$param = null, $as = '');
```

### `Lyx\Web` namespace `View` class

```PHP
Lyx\Web
 └─ View     // Class for loading template view from files
    ├─ $base_path;
    ├─ $html;
    ├─ $utf8_to_html_entities = true;
    ├─ $view_file;
    ├─ __construct($view_file = null);
    ├─ setBasePath($path);
    ├─ setConvertUTF8ToHtmlEntities($value);
    ├─ load($view_file_or_html, $params = []);
    ├─ ::loadView(
    │    $view_file_or_html, $params = [],
    │    $utf8_to_html_entities = true
    └  );
```
