<?php
class BaseController
{
	protected $app;
	protected $data;
	public function __construct()
	{
		$this->app = \Slim\Slim::getInstance();
		$this->data = array();
		/** default title */
		$this->data['title'] = 'Student Recruiter System';
		/** meta tag and information */
		$this->data['meta'] = array();
		/** queued css files */
		$this->data['css'] = array(
			'internal'  => array(),
			'external'  => array()
		);
		/** queued js files */
		$this->data['js'] = array(
			'internal'  => array(),
			'external'  => array()
		);
		/** prepared message info */
		$this->data['message'] = array(
			'error'    => array(),
			'info'    => array(),
			'debug'    => array(),
		);
		/** base dir for asset file */
		$this->data['baseUrl'] = $this->baseUrl();
		$this->data['assetUrl'] = $this->baseUrl() . '/assets/';
		/** user object **/

		if (isset($_SESSION['user'])) {
			$this->user = $_SESSION['user'];
			$this->data['user'] = $_SESSION['user'];
		}

		$this->data['app'] = $this->app;
		$this->loadBaseCss();
		$this->loadBaseJs();
	}
	/**
	 * enqueue css asset to be loaded
	 * @param  [string] $css     [css file to be loaded relative to base_asset_dir]
	 * @param  [array]  $options [location=internal|external, position=first|last|after:file|before:file]
	 */
	protected function loadCss($css, $options=array())
	{
		$location = (isset($options['location'])) ? $options['location']:'internal';
		//after:file, before:file, first, last
		$position = (isset($options['position'])) ? $options['position']:'last';
		if(!in_array($css,$this->data['css'][$location])){
			if($position=='first' || $position=='last'){
				$key = $position;
				$file='';
			}else{
				list($key,$file) =  explode(':',$position);
			}
			switch($key){
				case 'first':
					array_unshift($this->data['css'][$location],$css);
				break;
				case 'last':
					$this->data['css'][$location][]=$css;
				break;
				case 'before':
				case 'after':
					$varkey = array_keys($this->data['css'][$location],$file);
					if($varkey){
						$nextkey = ($key=='after') ? $varkey[0]+1 : $varkey[0];
						array_splice($this->data['css'][$location],$nextkey,0,$css);
					}else{
						$this->data['css'][$location][]=$css;
					}
				break;
			}
		}
	}

	/**
	 * enqueue js asset to be loaded
	 * @param  [string] $js      [js file to be loaded relative to base_asset_dir]
	 * @param  [array]  $options [location=internal|external, position=first|last|after:file|before:file]
	 */
	protected function loadJs($js, $options=array())
	{
		$location = (isset($options['location'])) ? $options['location']:'internal';
		//after:file, before:file, first, last
		$position = (isset($options['position'])) ? $options['position']:'last';
		if(!in_array($js,$this->data['js'][$location])){
			if($position=='first' || $position=='last'){
				$key = $position;
				$file='';
			}else{
				list($key,$file) =  explode(':',$position);
			}
			switch($key){
				case 'first':
					array_unshift($this->data['js'][$location],$js);
				break;
				case 'last':
					$this->data['js'][$location][]=$js;
				break;
				case 'before':
				case 'after':
					$varkey = array_keys($this->data['js'][$location],$file);
					if($varkey){
						$nextkey = ($key=='after') ? $varkey[0]+1 : $varkey[0];
						array_splice($this->data['js'][$location],$nextkey,0,$js);
					}else{
						$this->data['js'][$location][]=$js;
					}
				break;
			}
		}
	}
	/**
	 * clear enqueued css asset
	 */
	protected function resetCss()
	{
		$this->data['css']         = array(
			'internal'  => array(),
			'external'  => array()
		);
	}
	/**
	 * clear enqueued js asset
	 */
	protected function resetJs()
	{
		$this->data['js']         = array(
			'internal'  => array(),
			'external'  => array()
		);
	}
	/**
	 * remove individual css file from queue list
	 * @param  [string] $css [css file to be removed]
	 */
	protected function removeCss($css)
	{
		$key=array_keys($this->data['css']['internal'],$css);
		if($key){
			array_splice($this->data['css']['internal'],$key[0],1);
		}
		$key=array_keys($this->data['css']['external'],$css);
		if($key){
			array_splice($this->data['css']['external'],$key[0],1);
		}
	}
	/**
	 * remove individual js file from queue list
	 * @param  [string] $js [js file to be removed]
	 */
	protected function removeJs($js)
	{
		$key=array_keys($this->data['js']['internal'],$js);
		if($key){
			array_splice($this->data['js']['internal'],$key[0],1);
		}
		$key=array_keys($this->data['js']['external'],$js);
		if($key){
			array_splice($this->data['js']['external'],$key[0],1);
		}
	}
	/**
	 * addMessage to be viewd in the view file
	 */
	protected function message($message, $type='info')
	{
		$this->data['message'][$type] = $message;
	}
	/**
	 * add custom meta tags to the page
	 */
	protected function meta($name, $content)
	{
		$this->data['meta'][$name] = $content;
	}
	/**
	 * load base css for the template
	 */
	protected function loadBaseCss()
	{
		$this->loadCss("//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css", array('location' => 'external'));
		$this->loadCss("bootstrap.min.css");
		$this->loadCss("bootstrap-datepicker3.min.css");
		$this->loadCss("application.css");
	}
	/**
	 * load base js for the template
	 */
	protected function loadBaseJs()
	{
		$this->loadJs("jquery-1.11.2.min.js");
		$this->loadJs("bootstrap.min.js");
		$this->loadJs("bootstrap-datepicker.min.js");
	}
	/**
	 * generate base URL
	 */
	protected function baseUrl()
	{
		$baseUrl = dirname($_SERVER['SCRIPT_NAME']);
		$baseUrl = rtrim($baseUrl, '/');
		return $baseUrl;
	}

	/**
	 * convert UK date to MySQL date
	 */
	protected function convertDate($date){
		$date = DateTime::createFromFormat('d M Y', $date);
		if($date){
			return $date->format('Y-m-d');
		}
	}

	/**
	 * get POST params and convert empty string to null
	 */
	protected function getParams(){
		$params = $this->app->request->post();
		foreach ($params as $key => $value) {
			if ($value === '') {
				$params[$key] = null;
			}
		}
		return $params;
	}

	/**
	 * Load css and js for the map
	 */
	protected function loadMap() {
		$this->loadCss("//api.tiles.mapbox.com/mapbox.js/v2.1.8/mapbox.css", array('location' => 'external'));
		$this->loadJs("//api.tiles.mapbox.com/mapbox.js/v2.1.8/mapbox.js", array('location' => 'external'));
	}

	/**
	 * Load dropzone
	 */
	protected function loadDropzone() {
		$this->loadCss("dropzone.min.css", array('position' => 'before:application.css'));
		$this->loadJs("dropzone.min.js");
	}
	
	/**
	 * Load selectize
	 */
	protected function loadSelectize() {
		$this->loadCss("selectize.css", array('position' => 'before:application.css'));
		$this->loadJs("selectize.min.js");
	}

	protected function requireValidStudent() {
		if (isset($this->user) && $this->user->isStudent()) return;
		$this->app->flash('warning', 'You must register or login to continue.');
		$this->app->redirect($this->app->urlFor('student_register'));
	}

	protected function requireValidTeacher() {
		if (isset($this->user) && $this->user->isTeacher()) return;
		throw new Exception('Forbidden Area.');
	}

	protected function requireValidStaff() {
		if (isset($this->user) && $this->user->isStaff()) return;
		throw new Exception('Forbidden Area.');
	}

	protected function requireValidAdmin() {
		if (isset($this->user) && $this->user->isAdmin()) return;
		throw new Exception('Forbidden Area.');
	}

}
