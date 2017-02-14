#!/usr/bin/php
<?php
//=============================================================================
//  Usage:
//
//      lang.php xlt.{php,js} src.{php,js}...
//
//  Scans given PHP or JS src files and the xlt file for localizable strings.
//  Merges all strings together and outputs to stdout in the xlt format.
//
//  Localizable string format in src.{php,js}:
//
//      _("[ctx] Hello World!") /* comment */
//
//  * There must be no space between _ and ("
//  * You cannot use single-quote ' instead of double-quote " to quote
//    your localizable string.
//  * You cannot have any \" inside the double-quotes.  Instead,
//    use &ldquo; and &rdquo; as appropriate.
//  * The localizable string must all be one line, no line breaks.
//    Including \n inside string is ok.
//  * [ctx] is optional.  This may be used to disambiguate original string
//    and/or give additional context to translator; it does not appear in
//    translated string when translation is not provided.
//    E.g., _("[button] Home") vs _("[menu] Home")
//  * /* comment */ is optional.  If provided, it will appear in the output.
//    It must immediately follow the _(), white-space in between is ok.
//    The intent for the comment is to provide additional info to tranlator.
//
//  File format for xlt.{php,js}:
//
//      // location...
//      "[ctx] Hello World!" => "[note] Hallo Welt!",
//      /* comment */
//
//  * location is the original location(s) of the localizable string in the
//    src file.
//  * "[note] Hallo Welt!" is text that translator enters.  It must be
//    all one line, original and translated text.
//  * [note] is optional and it should not really be used,
//    except for when translator really, really needs to include some
//    important info that should not appear in the translated text.
//  * /* comment */ is the original comment from src files.  It should not
//    be edited.
//  * NOTE: Any and all changes outside translated text "[note] Hallo Welt!"
//    in the xlt file will be lost.
//  * Use &laquo;/&raquo;, &bdquo;/&rdquo;, &ldquo;/$rdquo; as appropriate.
//  * php files use => and js files use :.
//
//  Translation strings from the xlt file are merged with/into
//  strings from the src files.  Any strings that exist in the xlt file
//  that have no corresponding original string in the src files are marked
//  OBSOLETE.  Review those and remove them as appropriate.  Note that if
//  the original localizable string changes, the corresponding translation
//  will be marked OBSOLETE, while the new string will appear with empty
//  translation.  In that case, move/modify the old translation for the
//  new localizable string and remove the OBSOLETE entry.
//
//  Keyed Translation
//  ~~~~~~~~~~~~~~~~~
//
//  Keyed tranlations are used to deal with language inflections such as
//  different text for singular vs plural cases.
//  In src.{php,js} files the key argument is then provided as the second
//  argument to _():
//
//      _("%d item(s)",count)
//
//  The corresponding line in xlt.php for English (en) then becomes:
//
//      // location...
//     "%d item(s)"=>array('default'=>"%d items", 0=>"no items", 1=>"one item")
//
//  or xlt.js:
//
//      // location...
//     "%d item(s)": {'default': "%d items", 0: "no items", 1: "one item"},
//
//  NOTE: The whole translation line must be one line, there may be no line
//  breaks!
//
//  To Do
//  ~~~~~
//
//  * Ability to capture tranlator comments.
//  * Improve parsing to allow for multiple lines.
//
//=============================================================================

function x_strings($file,&$result) { // Parse src $file and merge into $result
  //echo "FILE: {$file}\n";
  $content=file_get_contents($file);
  // assert(strlen($content));
  if(!strlen($content)) {
    echo "// WARNING: empty {$file}\n";
    return;
  }

  $ok=preg_match_all('@[^a-zA-Z0-9_$]_+\("([^"]*)"(,\s*([^\)]*))?\)\s*(/\*[^*]*\*/)?@',
                      $content,$match,PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
  if(!$ok) return;
  assert($ok);

  $default_val='""';
  foreach($match as $m) {
    //print_r($m);
    $key=$m[1][0]; // addslashes..., str_replace('$','\$',$key)
    $xk= isset($m[3]) ? $m[3][0] : null;
    $comment= isset($m[4]) ? $m[4][0] : null;
    $line=substr_count($content, "\n", 0, $m[1][1])+1;
    $location="{$file}:{$line}";
    if($xk) $location.="({$xk})";
    $tr=isset($result[$key]) ? $result[$key] : null;
    if(!$tr) {
      $tr=array("key" => $key,
		"val" => $default_val,
		"location" => $location,
		"comment" => $comment,
		"xk" => $xk );
    } else {
      assert(!$tr['val'] || $tr['val']==$default_val);
      //if($tr['val'] && $tr['val']!=$val) $tr['val'].=' <=> '.$val;
      //else $tr['val']=$val;
      if($tr['location'] && $tr['location']!=$location)
	$tr['location'].=', '.$location;
      else $tr['location']=$location;
      if($tr['comment'] && $tr['comment']!=$comment)
	$tr['comment'].="\n".$comment;
      else $tr['comment']=$comment;
    }
    $result[$key]=$tr;
  }
}


function x_js($arr) { // Output $arr in .js format
  echo "var XLT={\n";
  $i=count($arr)-1;
  foreach($arr as $x) {
    echo "\n";
    echo "// {$x['location']}\n";
    if(isset($x['xk']) && $x['xk'] && $x['val']=='""') // special case for empty default
      echo "\"{$x['key']}\" : {'default': \"\"}";
    else
      echo "\"{$x['key']}\" : {$x['val']}";
    echo ($i?',':'')."\n";
    if(isset ($x['comment']) && $x['comment']) echo "{$x['comment']}\n";
    $i--;
  }
  echo "\n};\n";
  echo "window['XLT']=XLT;\n";
}


function x_php($arr) { // Output $arr in .php format
  echo "<?php \$XLT=array(\n";
  foreach($arr as $x) {
    echo "\n";
    echo "// {$x['location']}\n";
    if(isset($x['xk']) && $x['xk'] && $x['val']=='""') // special case for empty default
      echo "\"{$x['key']}\" => array('default' => \"\"),\n";
    else
      echo "\"{$x['key']}\" => {$x['val']},\n";
    if(isset($x['comment']) && $x['comment']) echo "{$x['comment']}\n";
  }
  echo "\n); ?>\n";
}


function x_txt($arr) { // Output in plan .txt format, useful for automated translation
  $re='/^\[[^\[]*\] */';
  foreach($arr as $x) {
    $key=$x['key'];
    $key=preg_replace($re,'',$key);
    echo "{$key}\n";
    $val=$x['val'];
    if($val && $val!='""') echo "{$val}\n";
    echo "\n";
  }
}

function x_parse($file,&$result) { // Parse xls $file and merge into $result
  if(!file_exists($file)) return;
  $handle = @fopen($file, "r");
  assert($handle);
  if(!$handle) die();

  if(!$result) $result=array();

  while(($line=fgets($handle,4096))) {
    //echo "LINE: {$line}";
    if(!preg_match('@^\s*"([^"]*)"\s(:|=>)\s(.*)@',$line,$match))
      continue;
    //print_r($match);
    $key=$match[1];
    $val=$match[3];
    $val=preg_replace('/,$/','',$val);
    $tr= isset($result[$key]) ? $result[$key] : null;
    if(!$tr) {
      $tr=array("key" => $key, "val" => $val, "location" => "OBSOLETE");
    } else {
      $tr['key']=$key;
      $tr['val']=$val;
    }
    $result[$key]=$tr;
  }
  //print_r($result);
}


// Command-line processing and main
//print_r($argv);
if(count($argv)<2) {
  echo "Usage: {$argv[0]} xlt.{js,php} src.{js.php}...\n";
  die();
}
array_shift($argv);
$src=array_shift($argv);

$result=array();
foreach($argv as $file)
  x_strings($file,$result);
//print_r($result);
x_parse($src,$result);
//print_r($result);

$ext=pathinfo($src, PATHINFO_EXTENSION);
if($ext=='js') x_js($result);
else if($ext=='php') x_php($result);
else if($ext=='txt') x_txt($result);
else assert(FALSE);

?>
