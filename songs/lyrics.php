<?php

if(isset($_REQUEST['FontSize'])) {
  $FONT_SIZE = $_REQUEST['FontSize'];
} else {
  $FONT_SIZE = 20;
}
$LINE_HEIGHT = $FONT_SIZE + 2;
?>

<!DOCTYPE html>
<html>
<head>
<title>Songs</title>
<script type="text/javascript" src="common/jquery-1.7.2.min.js"></script>
<script type="text/javascript">
<?php

$longestLine = FALSE;
$longestLineLength = 0;

function ValidSongName($scriptName)
{
  return eregi('^[a-zA-Z][-a-zA-Z0-9_ ]*$', $scriptName);
}
function printLyrics() {
  if(!isset($_REQUEST['Song'])) return 'Missing the Song request variable';

  $song = $_REQUEST['Song'];
  if(!ValidSongName($song)) return '"'.$song.'" contains invalid characters';

  $songfile = 'lyrics/'.$song.'.txt';
  if(!file_exists($songfile)) return '"'.$songfile.'" does not exist';

  $fh = fopen($songfile, "r");
  if(!$fh) return 'fopen("'.$songfile.'") failed';

  global $longestLine, $longestLineLength;
  $atFirst = TRUE;
  echo 'var lyrics = [';  	  
  while(!feof($fh)) {

    $line = rtrim(fgets($fh));

    $lineLength = strlen($line);
    if($lineLength > $longestLineLength) {
      $longestLine = $line;
      $longestLineLength = $lineLength;
    }

    $lineHtml = str_replace('"', "'", str_replace(' ', '&nbsp;', $line));

    if($atFirst) { $atFirst = FALSE; } else { echo ','; }
    echo '"'.$lineHtml.'"';
  }
  echo '];';

  fclose($fh);
  return FALSE;
}

$error = printLyrics();

?>


var lineHeight = <?php echo $LINE_HEIGHT; ?>;

var totalLines;
var totalPixelHeight;

var longestLineWidth;
var columnWidth;

$(document).ready(function() {
  songLyricsDiv = document.getElementById('SongLyricsDiv');

  totalLines = lyrics.length;
  totalPixelHeight = totalLines * lineHeight; // Note: totalPixelHeight should also equal songLyricsDiv.clientHeight  
  longestLineWidth = document.getElementById('LongestLine').clientWidth;
  columnWidth = longestLineWidth + 30;
  
  columnize();

  $(window).resize(columnize);
});

function columnize() {
  var container = document.getElementById('ColumnizedLyricsDiv');

  linesPerColumn = Math.floor(container.clientHeight / lineHeight);

  var column = 0;
  var lineIndex = 0;
  var html = '';
  while(lineIndex < totalLines) {
    left = column * columnWidth + 5;
    html += '<div class="SongLyricsColumn" style=\"position:absolute;width:' + columnWidth +
            'px;left:' + left + 'px;top:0;bottom:0;">';

    var linesLeft = totalLines - lineIndex;
    var lineLimit = (linesPerColumn < linesLeft) ? lineIndex + linesPerColumn : totalLines;
    while(lineIndex < lineLimit) {
      html += '<div>' + lyrics[lineIndex] + '</div>';
      lineIndex++;
    }      

    html += '</div>';
    column++;
  }

  container.innerHTML = html;    
}

</script>
<style type="text/css">
*{margin:0;padding:0;}
html, body {height:100%;}
body {
  font-family:monospace;
  font-size:<?php echo $FONT_SIZE; ?>px;
}
.SongLyricsColumn div {
  height:<?php echo $LINE_HEIGHT; ?>px;
}

</style>
</head>
<body>
<?php
  if($error !== FALSE) {
    echo '<h1>'.$error.'</h1>';
  } else {
    echo '<div id="ColumnizedLyricsDiv" style="position:absolute;top:0;left:0;right:0;bottom:0;"></div>';
    echo '<div id="LongestLine" style="position:absolute;visibility:hidden;height:auto;width:auto;" >'.$longestLine.'</span>';
  }
?>
</body>
</html>