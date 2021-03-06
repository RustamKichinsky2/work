<?php


namespace dvijok\core;
class Helper {

	public static function strReplaceFirst($search, $replace, $subject) {

		$pos = strpos($subject, $search);

		if ($pos !== false) {

			return substr_replace($subject, $replace, $pos, strlen($search));

		}

		return $subject;

	}
	
	public static function paginate($page, $limit, $table, $c, $whereQuery = '', $temp2 = array(), $select = '*') {
		
		if(!$page)
		{
			$page = 1;
		}
		$temp = array();
		if($whereQuery)
		{
			$whereQuery = ' '.$whereQuery;
		}
		if($temp2)
		{
			$temp = array_merge($temp, $temp2);
		}
		//$temp[] = $table;
		
		$query = "SELECT COUNT(*) AS `total` FROM `".$table."`".$whereQuery;
		
		$total = $c->db->query($query, $temp)->fetch();
		$total = $total['total'];
		$pages = ceil($total / $limit);
		$offset = ($page - 1)  * $limit;
		$limitQuery = '';
		$temp = array();
		if($temp2)
		{
			$temp = array_merge($temp, $temp2);
		}
		//$temp[] = $table;
		if($page)
		{
			$limitQuery = ' LIMIT ?, ?';
			$temp[] = $offset;
			$temp[] = $limit;
		}
		
		$query = "SELECT ".$select." FROM `".$table."`".$whereQuery.$limitQuery;
		$rows = $c->db->query($query, $temp)->fetchAll();
		// Some information to display to the user
		$start = $offset + 1;
		$end = min(($offset + $limit), $total);

		// The "back" link
		$queryString = '';
		$get = $c->input->get();
		unset($get['path']);
		if($get)
		{
			
			//$queryString = '&'.implode('&', $get);
			unset($get['page']);
			$queryString = '&'.http_build_query($get, '', '&amp;');
		}
		$prevlink = ($page > 1) ? '<a href="?page=1'.$queryString.'" title="First page">«</a> <a href="?page=' . ($page - 1).$queryString.'" title="Previous page">Предыдущая</a>' : '<span class="disabled">«</span> <span class="disabled">Предыдущая</span>';

		// The "forward" link
		$nextlink = ($page < $pages) ? '<a href="?page='.($page + 1).$queryString.'" title="Next page">Следующая</a> <a href="?page=' . $pages .$queryString.'" title="Last page">»</a>' : '<span class="disabled">Следующая</span> <span class="disabled">»</span>';
		$nextprevlinks = '<div id="paging"><p>'. $prevlink. ' Страница '. $page. ' из '. $pages.' '. $nextlink. ' </p></div>';
		$data = array();

		if(!$rows)
		{
			$nextprevlinks = '';
		}
		$data['links'] = $nextprevlinks;
		$data['rows'] = $rows;
		return $data;
		
	}

	public static function isCurrentUrl($url, $str) {

		if(Core::$mainUri == $url)
		{
			return $str;
		}

		return false;
	}
	
	public static function uploadMultiple($files, $path, $index) {
		
		
		Helper::clearFolder($path);
		$c = $files[$index]['name'];

		$total = count($files[$index]['name']);
		$paths = array();
		// Loop through each file
		for( $i=0 ; $i < $total ; $i++ ) {

		  //Get the temp file path
		  $tmpFilePath = $files[$index]['tmp_name'][$i];
			//$path = $files[$index]['name'];
			$ext = pathinfo($files[$index]['name'][$i], PATHINFO_EXTENSION);
						$new_name = ($i + 1).'.'.$ext;
		  //Make sure we have a file path
		  if ($tmpFilePath != ""){
			//Setup our new file path
			$newpath = $path;
			echo $newpath;
			if(!is_dir($newpath))
			{
				mkdir($newpath);
				
			}
			$newFilePath = $newpath . $new_name;
			$paths[] = $new_name;
			//Upload the file into the temp dir
			if(move_uploaded_file($tmpFilePath, $newFilePath)) {

			  //Handle other code here

			}
		  }
		}
		
		return $paths;
	}
	
	public static function clearFolder($path, $removeFolder = false) {
		
		$files2 = glob($path.'*');
		
		foreach($files2 as $file2){
		
		  if(is_file($file2))
			unlink($file2);
		}
		//$path = trim($path, './');
		if($removeFolder)
		{
			
			if(is_dir($path))
			{
				rmdir($path);
				
			}
			
		}
	}
	
	public static function siteURL()
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$domainName = $_SERVER['HTTP_HOST'].'/';
		return $protocol.$domainName;
	}
	
	public static function redirect($url = false) {
		
		global $baseUrl;
		//$url = trim($url, '/');

		if(!$url)
		{
			header('Location: '.Helper::siteURL());
		}
		else
		{
			header('Location: '.$url);
		}
	}
	
	public static function truncate($string,$length=100) {

		$foo = mb_substr($string,0,$length, "utf-8");
		if(strlen($string) > $length)
		{
			$foo .= '...';
		}
		
		return $foo;
	}
	
	public static function getFiles($path) {
		
		$files = array();
		
		if(is_dir($path))
		{
			$files = scandir($path);
			$files = array_diff(scandir($path), array('.', '..'));
			$files = array_values($files);
		}
		
		return $files;
	}
	
	
	public static function checkPath($scriptUri) {
		
		$scriptUri = mb_strtolower($scriptUri, 'UTF-8');
		$obj = new \stdClass();
		$obj->redirect = false;
		$obj->scriptUri = $scriptUri;
		$tempUri = $scriptUri;
		$temp = explode('/', $tempUri);
		$newUri = '';
		$obj->scriptUri = '';
		foreach(\dvijok\core\Routes::$remaps as $key => $r)
		{
			$key = trim($key, '/');
			$t = explode('/:param', $key);
			$t[0] = mb_strtolower($t[0], 'UTF-8');
			$s = $scriptUri.'/';
			$s2 = $t[0].'/';
			//if($t[0] == 'article')
			//{
				$p = stripos($s2, ':param');
				$d = substr($s2, $p);
				$d = str_replace("/", "\/", $d);
				
				$s3 = preg_replace('/'.$d.'/i', '', $s);
				
				$d2 = strripos($s, $s3).'<br />';
				$d2 = intval($d2);
				//$s = substr($s, 0, $d2);
				//echo $s3.'<br />';
				
			//}
			//echo $s2;
			//$s3 = '/'.$s3;
			//echo strripos($s, $s3).' - '.$d2.'<br />';
			//echo $s.' - '.$s2.$s3.'<br />';

			if (stripos($s, $s2.$s3) === 0 && strripos($s, $s3) - $d2 === 0) {
				
				
				$p = explode($s2, $s, 2);

				//print_r($key);
				unset($p[0]);
				$p = implode('', $p);
				
				$tempUri = $r['path'].'/'.$p;
				//echo $tempUri.'<br />';
				
				if(isset($r['lock']))
				{
					$obj->redirect = true;
				}
					$obj->scriptUri = $tempUri;
					$obj->url = $key;
				break;
			}
		}

		/*
		if(isset(\dvijok\core\Config::$remaps[$scriptUri]))
		{
			$temp = Config::$remaps[$scriptUri];
			
			$tempUri = $temp['path'];
			
		}
		
		foreach(Config::$remaps as $key => $r)
		{
			if($r['path'] == $scriptUri && $r['lock'])
			{
				$obj->redirect = true;
				$obj->url = $key;
			}
		}
		*/
		$obj->scriptUri = $tempUri;

		return $obj;
	}
	
	public static function scandir($path) {
		
		$files = array_diff(scandir($path), array('.', '..'));
		$files = array_values($files);
		return $files;
		
	}
	
	public static function deleteDirectory($dir) {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}

		}

		return rmdir($dir);
	}
	
	
	public static function buildTree(array $elements, $parentId = 0) {
		$branch = array();

		foreach ($elements as $element) {
			if ($element['parent_id'] == $parentId) {
				$children = Helper::buildTree($elements, $element['id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[$element['id']] = $element;
				unset($elements[$element['id']]);
			}
		}
		return $branch;
	}
	

	
	public static function importTree($elements, $db) {
		

		foreach ($elements as &$element) {
			
			$t = array();
			$t[] = $element->id;
			$t[] = $element->name;
			$t[] = $element->parent_id;
			$db->query("INSERT INTO `branch`(`id`, `name`, `parent_id`) VALUES(?, ?, ?)", $t);
			if($element->branches)
			{
				Helper::importTree($element->branches, $db);
			}
			if($element->users)
			{
				foreach($element->users as $u)
				{
					$t = array();
					$t[] = $u->id;
					$t[] = $u->name;
					$t[] = $u->branch_id;
					$db->query("INSERT INTO `users`(`id`, `name`, `branch_id`) VALUES(?, ?, ?)", $t);
				}
			}
		}
		return true;
	}
	
	public static function buildTreeD(array &$elements, $parentId = 0, $articles = array(), $isFirst = true) {
		$branch = array();
		$parent = null;
		foreach ($elements as &$element) {
			
				if($isFirst && $element['id'] == $parentId)
				{
					$parent = $element;
				}
			if ($element['parent_id'] == $parentId) {
				

				$users = array();
				$branches = array();
				foreach($articles as $a)
				{
					if($a['branch_id'] == $element['id'])
					{
						$users[] = $a;
						//echo \dvijok\core\Config::$relative_url.' == '.$href.'<br />';
					}
				}
				$element['users'] = $users;
				$element['branches'] = $branches;
				$children = Helper::buildTreeD($elements, $element['id'], $articles, false);

				foreach($children as $ch)
				{
						if($ch['parent_id'] == $element['id'])
						{
							
							$element['branches'] = $children;
						}
					
				}
				if ($children) {
					//$element['children'] = $children;
				}
				$branch[] = $element;
				//unset($elements[$element['id']]);
			}
		}
		if($isFirst) {
			$parent['branches'] = $branch;
			$branch = [];
			
			$branch[] = $parent;
		}
		if(isset($branch[0]) && !$branch[0]['branches'])
		{
			$obj = new \stdClass();
			$obj->success = false;
			
			return $obj;
		}
		return $branch;
	}
	

	
	public static function buildTreeC(array &$elements, $parentId = 0, $articles = array()) {
		$branch = array();

		foreach ($elements as &$element) {
			if ($element['parent_id'] == $parentId) {
				
				$c = 0;
				$ids = array();
				$active = 0;
				$href = '/category/'.Helper::transliterate($element['title']).'-'.$element['id'];
				
				foreach($articles as $a)
				{
					if($a['category_id'] == $element['id'])
					{
						$c++;
						$ids[] = $element['id'];
						//echo \dvijok\core\Config::$relative_url.' == '.$href.'<br />';
					}
						if(\dvijok\core\Config::$relative_url == $href)
						{
							$active = 1;
						}
				}
				$element['article_count'] = $c;
				$element['ids'] = $ids;
				$element['active'] = $active;
				$children = Helper::buildTreeC($elements, $element['id'], $articles);
				foreach($children as $ch)
				{
						$element['article_count'] += $ch['article_count'];
						if(is_array($ch['ids']))
						{
							$element['ids'] = array_merge($element['ids'], $ch['ids']);
						}
						$element['active'] += $ch['active'];
					
				}
				if ($children) {
					//$element['children'] = $children;
				}
				$branch[] = $element;
				//unset($elements[$element['id']]);
			}
		}
		return $branch;
	}
	
	public static function buildTreeHTML(array $elements, $parentId = 0, $level = 1, $articles = array()) {
		$branch = array();
		$display = '';
		if(!Helper::buildTree($elements, $parentId))
		{
			$display = 'style="display: none;"';
		}
		$html = '<ul class="level-'.($level).' list-group list-group-root well" '.$display.' >';
		$str = '';
		if($parentId == 0)
		{
			$str = 'first-level ';
		}
		foreach ($elements as $element) {
			if ($element['parent_id'] == $parentId) {
				if(isset($element['url']) && $element['url'])
				{
					$href = $element['url'];
				}
				else
				{
					$href = '/category/'.Helper::transliterate($element['title']).'-'.$element['id'];
				}
				
				$children = Helper::buildTreeHTML($elements, $element['id'], $level + 1, $articles);
				
				if(Helper::buildTree($elements, $element['id']))
				{
					$glyphicon = '<i class="glyphicon glyphicon-chevron-right"></i>';
				}
				else
				{
					$glyphicon = '';
				}
				$active = '';
				if($element['active'])
				{
					$active = 'active';
				}
				$html .= '<li class="menu-item '.$active.' '.$str.'list-group-item">'.$glyphicon.'<a href="'.$href.'"><span class="menu-item-text">'.$element['title'].'</span></a> ('.$element['article_count'].')';
				if ($children) {
					$html .= $children;
				}
				$html .= '</li>';
				
				unset($elements[$element['id']]);
			}
		}
		$html .= '</ul>';
		return $html;
	}
	
	public static function buildLeftTreeHTML(array &$elements, $parentId = 0, $level = 1) {
		$branch = array();
		$html = '<ul class="level-'.($level).'">';
		$str = '';
		if($parentId == 0)
		{
			$str = 'first-level';
		}
		foreach ($elements as $element) {
			if ($element['parent_id'] == $parentId) {
				if($element['url'])
				{
					$href = $element['url'];
				}
				else
				{
					$href = '/c/'.Helper::transliterate($element['title']).'-'.$element['id'].'/leftmenu';
				}
				$html .= '<li class="menu-item '.$str.'"><a href="'.$href.'"><span class="menu-item-text">'.$element['title'].'</span></a>';
				$children = Helper::buildTreeHTML($elements, $element['id'], $level + 1);
				if ($children) {
					$html .= $children;
				}
				$html .= '</li><li class="delimiter"></li>';
				
				unset($elements[$element['id']]);
			}
		}
		$html .= '</ul>';
		return $html;
	}
	
	public static function http_check($url) {
		$return = $url;
		if ((!(substr($url, 0, 7) == 'http://')) && (!(substr($url, 0, 8) == 'https://'))) {
			return false;
		}
		return true;
	} 
	
	public static function buildTreeHTMLAdmin(array &$elements, $parentId = 0, $level = 1, $menu = 'menu') {
		$branch = array();
		$html = '<ul class="level-'.($level).'">';
		$str = '';
		if($parentId == 0)
		{
			$str = 'first-level';
		}
		foreach ($elements as $element) {
			if ($element['parent_id'] == $parentId) {
				if($element['url'])
				{
					$href = $element['url'];
				}
				else
				{
					$href = '/category/'.Helper::transliterate($element['title']).'-'.$element['id'];
				}
				$html .= '<li class="menu-item '.$str.'"><a href="'.$href.'"><span class="menu-item-text">'.$element['title'].'</span></a> - <a class="edit btn btn-outline-warning  btn-sm" href="/index/admin/'.$menu.'/'.$element['id'].'">Редактироавать</a> <a class="edit btn btn-outline-danger  btn-sm" href="/index/admin/'.$menu.'/delete/'.$element['id'].'">Удалить</a> - <a href="/admin/articles/category?category_id='.$element['id'].'">обявления с этой категории<a>';
				$children = Helper::buildTreeHTMLAdmin($elements, $element['id'], $level + 1, $menu);
				if ($children) {
					$html .= $children;
				}
				$html .= '</li>';
				
				unset($elements[$element['id']]);
			}
		}
		$html .= '</ul>';
		return $html;
	}
	
	public static function clean($text) {
		$utf8 = array(
			'/[áàâãªä]/u'   =>   'a',
			'/[ÁÀÂÃÄ]/u'    =>   'A',
			'/[ÍÌÎÏ]/u'     =>   'I',
			'/[íìîï]/u'     =>   'i',
			'/[éèêë]/u'     =>   'e',
			'/[ÉÈÊË]/u'     =>   'E',
			'/[óòôõºö]/u'   =>   'o',
			'/[ÓÒÔÕÖ]/u'    =>   'O',
			'/[úùûü]/u'     =>   'u',
			'/[ÚÙÛÜ]/u'     =>   'U',
			'/ç/'           =>   'c',
			'/Ç/'           =>   'C',
			'/ñ/'           =>   'n',
			'/Ñ/'           =>   'N',
			'/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
			'/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
			'/[“”«»„]/u'    =>   ' ', // Double quote
			'/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
		);
		return preg_replace(array_keys($utf8), array_values($utf8), $text);
	}
	
	public static function transliterate($textcyr = null, $textlat = null) {
		$ret = '';
		$cyr = array(
		'ы','я','ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь',
		 'Ы', 'Я', 'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', ' ');
		$lat = array(
		'yi', 'ya', 'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'i',
		 'YI', 'YA','Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Y', 'I', '-');
		if($textcyr) $ret = str_replace($cyr, $lat, $textcyr);
		else if($textlat) $ret = str_replace($lat, $cyr, $textlat);
		$ret = mb_strtolower($ret, 'UTF-8');
		return $ret;
	}
}
