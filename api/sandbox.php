<html>
<head>
<title>API Sandbox</title>
<style>
body {
	font-size: 1.2em;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
a {
	margin:5px 0;
	cursor:pointer;
	color: #007b7b;
	display:block;
	width:200px;
	font-size:1.1em;
	text-decoration:none;
}
a + div {
	display:none;
}
div {
    border-radius: 10px;
    background-color: #f2f2f2;
    padding: 10px;
    margin-bottom: 10px;
}
div div {
    border-radius: 10px;
    background-color: #e2e2e2;
    padding: 10px;
	margin-top:5px;
    margin-bottom: 10px;
	font-size:0.8em;
}
textarea, input[type="text"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 10px;
    box-sizing: border-box;
    background-color: #007B81;
    color: white;
}
input[type="text"] {
	width:200px;
	margin: 2px 0;
}
pre {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 10px;
    box-sizing: border-box;
    background-color: #222;
    color: white;
	min-height:25px;
}
button {
    background-color: #555555;
    border: none;
    color: white;
    padding: 10px;
    border-radius: 10px;
    text-decoration: none;
    margin: 4px 2px;
    cursor: pointer;
}
button:hover {
    background-color: white;
    color: #555555;
}
label {
	display:inline-block;
	width:80px;
}
.hide {
	display:none !important;
}

</style>
<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script>
$(function() {
	$('a[href="#"]').on('click', function(e){
		e.preventDefault();
		$(this).next('div').slideToggle(250);
	});

	$('label').on('click', function(){
		$(this).next('input[type="text"]').focus();
	});

	$('input[type="text"]').on('input propertychange paste', function(){
		query = "/api";
		$(this).parent().children('input[type="text"]').each(function(index, element) {
			query += $(element).attr('name')+'='+encodeURIComponent($(element).val())+'&';
		});
		$(this).siblings('textarea').val(query.slice(0, -1));
	});

	$('button').on('click', function(){
		$that = $(this);
		$that.parent().next('.output').children('pre').text('Loading data...');
		$.post($(this).siblings('textarea').val())
		.done(function(data) {
			if(data){
				if(data.charAt(0) == '{' || data.charAt(0) == '[') {
					data = $.parseJSON(data);
					data = JSON.stringify(data, null, 4);
				}
				$that.parent().next('.output').children('pre').text(data);
			} else {
				$that.parent().next('.output').children('pre').text('No data received.');
			}
		});
	});
});
</script>
</head>
<body>
<?php
error_reporting(E_ERROR | E_PARSE);


$libs = glob("*.php");

if(($key = array_search('sandbox.php', $libs)) !== false) {
    unset($libs[$key]);
}
foreach ($libs as $lib) {
	$doc .= file_get_contents($lib);
}
	//echo "<pre>$doc</pre>";

preg_match_all("|\/\*{2}([\s\S]+?)\*\/|", $doc, $content);
$content = $content[1];
?>
<h2>API Sandbox</h2>
<?php
//unset($content[0]);

foreach($content as $k) {
	preg_match("|Name: (.*)|", $k, $name);
	$name = trim($name[1]);
	preg_match("|Description: (.*)|", $k, $desc);
	$desc = trim($desc[1]);
	preg_match("|Example: (.*)|", $k, $example);
	$example = trim($example[1]);
	preg_match("|@param (.*)|", $k, $param);
	$param = trim($param[1]);
	preg_match("|@return (.*)|", $k, $ret);
	$ret = trim($ret[1]);
	parse_str(substr($example,4), $querystring);
	$in = explode(' ',$param);
	$out = explode(' ',$ret);
	?>
	<a href="#"><?php echo $name;?></a>
	<div>
	<em><?php echo $desc;?></em>
		<div class="input">
			<strong>Input:</strong> <em>(<?php echo $in[0];?>)</em> <?php echo theRest($in,2);?><br>
			<?php foreach ($querystring as $q => $v) {
				$hide='';if($q == '/?action') {$hide='class="hide"';}?>
				<label <?=$hide?>><?php echo str_replace('/?','',$q);?>:</label> <input <?=$hide?> type="text" name="<?php echo $q;?>" value="<?php echo htmlspecialchars($v);?>"><br <?=$hide?>>
			<?php }?>
			<textarea><?php echo htmlspecialchars($example);?></textarea>
			<button>Query</button>
		</div>
		<div class="output">
			<strong>Output:</strong> <em>(<?php echo $out[0];?>)</em> <?php echo theRest($out,1);?><br>
			<pre></pre>
		</div>
	</div>
	<?php
}

function theRest($arr, $start) {
	foreach($arr as $k => $v) {
		if($k >= $start) {
			$ret .= $v.' ';
		}
	}

	return trim($ret);
}
?>
</body>
</html>
