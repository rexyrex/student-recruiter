<?php

class EventController extends BaseController
{

	public function index()
	{
		$this->data['events'] = Event::getEventList();
		$this->app->render('event_list.php', $this->data);
	}
	
	# GET /event/1
	public function view($id)
	{
		$this->data['event'] = Event::getEventById($id);
		$this->data['location'] = $this->data['event']->getLocation();
		if ($this->data['location']) $this->loadMap();
		$this->app->render('event_details.php', $this->data);
	}

	# GET /event/create
	public function create()
	{
        $this->data['rooms'] = Room::getRoomList();
		$this->app->render('create_event.php', $this->data);
	}

	# GET /event/pending
	public function pending()
	{
		$this->data['pending_events'] = Event::getPendingEventList();
		$this->app->render('pending_events.php', $this->data);
	}

	# GET /event/:id/approve
	public function approve($id)
	{
		$this->data['event'] = Event::getEventById($id);
		$this->app->render('approve_event.php', $this->data);
	}

	# GET /event/:id/reject
	public function reject($id)
	{
		$this->data['event'] = Event::getEventById($id);
		$this->app->render('reject_event.php', $this->data);
	}

	# GET /event/:id/cancel
	public function cancel($id)
	{
		$this->data['event'] = Event::getEventById($id);
		$this->app->render('cancel_event.php', $this->data);
	}

	# GET /event/:id/classbook
	public function classBook($id)
	{
		$this->data['event'] = Event::getEventById($id);
		$this->data['students'] = Student::getStudentListByTeacherId($this->user->id);
		$this->data['schoolID'] = Student::getSchool($this->user->id);
		$this->app->render('class_book_event.php', $this->data);
	}

	# POST /event
	public function store()
	{
		$app = $this->app;
		$params = $this->getParams();

		$event = new Event(true);
		$event->title = $params['title'];
		$event->description = $params['description'];
		$event->type = $params['type'];
		$event->tags = $params['tags'];
		$event->room_id = $params['room_id'];
		$event->start_time = $this->convertDate($params['date']) . ' ' . $params['start_time'] . ':00';
		$event->end_time = $this->convertDate($params['date']) . ' ' . $params['end_time'] . ':00';
		$event->proposed_at = $params['proposed_at'];
		$event->proposed_by = $this->user->id; 
		$event->approved_at = $params['approved_at'];
		$event->approved_by = $params['approved_by'];
		$event->status = 'pending'; 
		$event->twitter_link = $params['twitter_link'];

		if($event->save()){
			$app->flash('info', 'Request sent.');
			$app->redirect($app->urlFor('home'));
		} else {
			$app->flash('warning', 'There was an error with the request');
			$app->redirect($app->urlFor('home'));
		}
	}

	public function storeApproval($eventId)
	{
		$app = $this->app;
		
		$event = Event::getEventById($eventId);
		$event->status = "approved";
		$event->approved_at = date("Y-m-d h:i:s", time());
		$event->approved_by = $approved_by;

		if($event->save()){
			$app->flash('info', 'Event approved');
			$app->redirect($app->urlFor('pending_events'));
		} else {
			$app->flash('warning', 'There was a problem');
			$app->redirect($app->urlFor('pending_events'));
		}
	}


	#POST event/:id/classbook
	public function storeClassBook($id) {
        $params = $this->getParams();
		$students = $params['list'];
		$event = Event::getEventById($id);

		foreach($students as $student){
			$result = $event->bookEvent($student);
		}

		if ($result == 1) {
			$this->app->flash('info', 'Event successfully booked');
			$this->app->redirect($this->app->urlFor('events_list'));
		} else {
			$this->app->flash('error', 'Event booking was not succesful');
			$this->app->redirect($this->app->urlFor('events_list'));
		}
	}

	public function book($id)
	{
		$e = Event::getEventById($id);
		$this->requireValidStudent();
		$result = $e->bookEvent($this->data['user']->id);
		if ($result == 1) {
			if ($this->app->request->isXhr()) {
				$ret = array('id' => $id);
				$this->app->response->headers->set('Content-Type', 'application/json');
				echo json_encode($ret);
				return;
			}
			$this->app->flash('info', 'Event successfully booked');
			$this->app->redirect($this->app->urlFor('events_list'));
		} else {
			if ($this->app->request->isXhr()) {
				$this->app->response->setStatus(400);
				$this->app->response->headers->set('Content-Type', 'application/json');
				return;
			}
			$this->app->flash('error', 'Event booking was not succesful');
			$this->app->redirect($this->app->urlFor('events_list'));
		}
	}
	
	public function unbook($id)
	{
		$e = Event::getEventById($id);
		$this->requireValidStudent();
		$result = $e->unbookEvent($this->data['user']->id);
		if ($result == 1) {
			if ($this->app->request->isXhr()) {
				$ret = array('id' => $id);
				$this->app->response->headers->set('Content-Type', 'application/json');
				echo json_encode($ret);
				return;
			}
			$this->app->flash('info', 'Booking successfully cancelled');
			$this->app->redirect($this->app->urlFor('events_list'));
		} else {
			if ($this->app->request->isXhr()) {
				$this->app->response->setStatus(400);
				$this->app->response->headers->set('Content-Type', 'application/json');
				return;
			}
			$this->app->flash('error', 'Booking was not cancelled');
			$this->app->redirect($this->app->urlFor('events_list'));
		}
	}

	public function classUnbook(){
	//TODO
	}
	# GET /event/search
	public function search() {
		$this->app->response->headers->set('Content-Type', 'application/json');
		$params = $this->app->request->get();
		$events = 
			Event::getFilteredEventList(array(
				'query' => $params['query'],
				'type'  => $params['type'],
				'tags'  => $params['tag']
			));
		$ret = array(
			'id' => array()
		);
		foreach ($events as $e) {
			$ret['id'][] = $e->id;
		}
		echo json_encode($ret);
	}

	# GET /event/:id/tweet
	public function tweet($id) {
		$this->app->response->headers->set('Content-Type', 'application/json');
		
		$e = Event::getEventById($id);
		if ($e->twitter_link) {
			$this->app->response->setStatus(400); // Bad request
			$ret = array(
				'error' => 'Already tweeted.'
			);
			echo json_encode($ret);
		} else {
			$url = $this->app->request->getUrl() . $this->app->urlFor('view_event', array('id' => $id));
			$title = substr($e->title, 0, 50);
			$msg = 'We are hosting event "'.$title . '". More detail: '. $url;
			$ret = TwitterHelper::tweet($msg);
			$message = json_decode($ret, true);
			if (isset($message['id_str'])) {
				$id_str = $message['id_str'];
				$twitter_link = "https://twitter.com/StdntRecruiter/status/$id_str";
				$e->twitter_link = $twitter_link;
				$e->save();
			}
			echo $ret;
		}
	}

}
