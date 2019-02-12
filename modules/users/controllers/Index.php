<?phpnamespace dvijok\modules\users;class Index extends \dvijok\core\Core {	public function __construct($path, $data) {				parent::__construct($path, $data);	}		public function add_post() {				$post = $this->input->post();		$t = [];		$t[] = $post['name'];		$t[] = $post['branch_id'];		$this->db->query("INSERT INTO `users`(`name`, `branch_id`) VALUES(?, ?)", $t);		$id = $this->db->insertId();		$obj = new \stdClass();		if($id)		{			$obj->success = true;			$obj->id = $id;		}		else		{			$obj->success = false;		}		return "<pre>".json_encode($obj, JSON_PRETTY_PRINT)."</pre>";	}		public function get($data) {				$t = [];		$t[] = isset($data['params'][0]) ? intval($data['params'][0]) : 0;		$obj = new \stdClass();		$res = $this->db->query("SELECT * FROM `users` WHERE `id` = ?", $t)->fetch();		if($res)		{			$obj = $res;		}		else		{			$obj->success = false;		}		return "<pre>".json_encode($obj, JSON_PRETTY_PRINT)."</pre>";	}		public function delete_post() {				$post = $this->input->post();		$t = [];		$t[] = $post['id'];		$res = $this->db->query("DELETE FROM `users` WHERE `id` = ?", $t);		$obj = new \stdClass();		if($res->stmt->affected_rows)		{			$obj->success = true;		}		else		{			$obj->success = false;		}		return "<pre>".json_encode($obj, JSON_PRETTY_PRINT)."</pre>";	}		public function edit_post() {				$post = $this->input->post();		$t = [];		$t[] = $post['name'];		$t[] = $post['branch_id'];		$t[] = $post['id'];		$res = $this->db->query("UPDATE `users` SET `name` = ?, `branch_id` = ? WHERE `id` = ?", $t);		$obj = new \stdClass();		if($res->stmt->affected_rows)		{			$obj->success = true;		}		else		{			$obj->success = false;		}		return "<pre>".json_encode($obj, JSON_PRETTY_PRINT)."</pre>";	}}