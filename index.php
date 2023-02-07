<?php
	$search = false;
	$base = "";
	$alg = "";
	$query = "";
	if (isset($_GET["query"])) {
		$search = true;
		$base = $_GET["base"];
		$alg = $_GET["alg"];
		$query = $_GET["query"];
	}

	if (strpos($base, "..") !== false) {
		die("puto maricón, que haces");
	}
	
	function getObjectsIter($path) {
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		return $objects;
	}
	
	function getObjectsLaura($path) {
		$result = array();

		$dir2 = glob($path."/*",GLOB_ONLYDIR);	
		$dir = glob($path."/*"); // creo que esto coge los dir tambien

		while (count($dir2) != 0) {
			for ($i = 0; $i < count($dir2); $i++){
				$folder = glob($dir2[$i]);
				
			}
		}

		$result = $dir;
		return $result;
	}
	
	function getObjectsMe($path) {
		if ($path[strlen($path) - 1] != '/') $path = $path.'/';
		//echo $path."<br>";
		$objects = array_slice(scandir($path), 2);		// 35
		for ($i = 0; $i < count($objects); $i++) {
			$objects[$i] = $path.$objects[$i];
		}
		
		foreach ($objects as $object) {
			if(strpos($object, ".") || strpos($object, "..")) continue;
			if (is_dir($object)) {
				$objects = array_merge($objects, getObjectsMe($object));
			}
		}
		
		return $objects;
	}
	
?>

<!DOCTYPE html>
<html>
    <head>
		<meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="/style.css">
        <title>ARFNET</title>
		<style>
			.title {
				font-size: 36px;
			}
			
			header *{
				display: inline-block;
			}
			
			*{
				vertical-align: middle;
				max-width: 100%;
			}
			
			video {
				width: 72%;
			}
			
			.form {
				margin: auto;
				text-align: center;
				margin-bottom: 50px;
			}
			
			.searchbox {
				width: 300px;
			}
			
			.searchbar {
				height: 25px;
			}
			
			.stats {
				font-size: 20px;
				margin-bottom: 20px;
				margin-left: 15px;
			}
			
			.result {
				margin-bottom: 20px;
				margin-left: 15px;
			}
			
			.name {
				font-size: 20px;	
			}
			
			.address {
				font-size: 14px;
			}
			
			
		</style>
    </head>

    <body>
		<header>
			<img src="/arfnet_logo.png" width="64">
			<span class="title"><strong>ARFNET</strong></span>
		</header>
		<hr>
		<a class="home" href="/">Home</a><br>
		<h2>ARFNET Search Engine</h2>
		<br>
		<div class="form">
			<form action="/search/index.php" method="GET">
				<select class="searchbar" name="base">
					<option <?php if ($base == "FTPServer/") echo "selected"; 	?> value="FTPServer/">FTPServer</option>
					<option <?php if ($base == "source/repos/") echo "selected"; 		?> value="source/">source</option>
				</select>
				<select class="searchbar" name="alg">
					<option <?php if ($alg == "str_contains") echo "selected"; 	?> value="str_contains">str_contains</option>
					<option <?php if ($alg == "regex") echo "selected"; 		?> value="regex">regex</option>
				</select>
				<input class="searchbar searchbox" type="text" name="query" <?php if ($search) echo 'value="'.$query.'"'; ?> >
				<input class="searchbar" type="submit" value="Search">
			</form>
		</div>
		<hr>
		<?php
			if ($search) {
				if ($query == "") die("empty query");
				if (strpos($query, "<") === false) { } else { die("No <> allowed, fuck you."); }
				
				$time_start = microtime(true); 
				
				$path = "/d/".$base;
				//echo $path;
				$objects = getObjectsMe($path);
				$results1 = array();
				$results2 = array();
				
				//echo "<pre>";
				//print_r($objects);
				//echo "</pre>";
				
				$tok = strtok($query, ' ');
				$tokens = array();
				
				while ($tok !== false) {
					array_push($tokens, $tok);
					$tok = strtok(' ');
				}
				
				foreach($objects as $name) {
					//echo $name."<br>";
					$relevance1 = true;
					$relevance2 = false;
					foreach($tokens as $token) {
						if (strpos(strtolower($name), strtolower($token))) {
							$relevance1 = $relevance1 && true;
							$relevance2 = $relevance2 || true;
						} else {
							$relevance1 = $relevance1 && false;
							$relevance2 = $relevance2 || false;
						}
					}
					
					if 		($relevance1 && $relevance2)
						array_push($results1, $name);
					else if (!$relevance1 && $relevance2)
						array_push($results2, $name);
				}
				
				$time_end = microtime(true);
				$execution_time = ($time_end - $time_start);
				
				echo '<div class="stats"><span><strong>'.(count($results1) + count($results2)).' results in '.round($execution_time, 3).' seconds</strong></span></div>';
				
				echo '<div class="stats"><span><strong>Results including all tokens [';
				foreach ($tokens as $token) echo $token.', ';
				echo ']</strong></span></div>';
				foreach ($results1 as $result) {
					echo '<div class="result">'.
					'<a class="name" href="'.substr($result, 2).'">'.basename($result).'</a><br>'.
					'<span class="address">'.substr($result, 2).'</span>'.
					'</div>';
				}
				
				echo '<div class="stats"><span><strong>Results including single token [';
				foreach ($tokens as $token) echo $token.', ';
				echo ']</strong></span></div>';
				foreach ($results2 as $result) {
					echo '<div class="result">'.
					'<a class="name" href="'.substr($result, 2).'">'.basename($result).'</a><br>'.
					'<span class="address">'.substr($result, 2).'</span>'.
					'</div>';
				}
			}
		?>
	</body>
</html>
