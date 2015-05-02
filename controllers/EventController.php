<?php

class EventController extends BaseController {

	public function index() {
		$this->data['events'] = Event::getEventList();
		$this->app->render('event.php', $this->data);
	}
	
	# GET /event/1
	public function view($id) {
		$this->data['event'] = Event::getEventById($id);
		$this->app->render('event_details.php', $this->data);
	}

	# GET /event/create
	public function create() {
        $this->data['rooms'] = Room::getRoomList();
		$this->app->render('create_event.php', $this->data);
	}

	# GET /event/pending
	public function pending() {
		$this->data['pending_events'] = Event::getPendingEventList();
		$this->app->render('pending_events.php', $this->data);
	}

	# GET /event/:id/approve
	public function approve() {
		$this->app->render('approve_event.php', $this->data);
	}

	# GET /event/:id/reject
	public function reject() {
		$this->app->render('reject_event.php', $this->data);
	}

	# GET /event/:id/remove
	public function remove() {
		$this->app->render('remove_event.php', $this->data);
	}


	# POST /event
	public function store(){
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
		$event->proposed_by = $params['proposed_by'];
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

	public function book($id){
		$e = Event::getEventById($id);
		if (!isset($this->data['user'])) {
			$this->app->flash('error', 'Please login first');
			$this->app->redirect($this->app->urlFor('login'));
		} else if (!$this->data['user']->isStudent()) {
			$this->app->flash('error', 'You must be a student');
			$this->app->redirect($this->app->urlFor('home'));
		}
		$result = $e->bookEvent($this->data['user']->id);
		if ($result == 1) {
			$this->app->flash('info', 'Successfully Booked Event.');
			$this->app->redirect($this->app->urlFor('list_event'));
		} else {
			$this->app->flash('error', 'Booking UNSUCCESSFUL.');
			$this->app->redirect($this->app->urlFor('list_event'));
		}
	}

	public function unbook($id){
		$e = Event::getEventById($id);
		if (!isset($this->data['user'])) {
			$this->app->flash('error', 'Please login first');
			$this->app->redirect($this->app->urlFor('home'));
		} else if (!$this->data['user']->isStudent()) {
			$this->app->flash('error', 'You must be a student');
			$this->app->redirect($this->app->urlFor('home'));
		}
		$result = $e->unbookEvent($this->data['user']->id);
		if ($result == 1) {
			$this->app->flash('info', 'Successfully Cancelled Booking.');
			$this->app->redirect($this->app->urlFor('account'));
		} else {
			$this->app->flash('error', 'Booking Cancel UNSUCCESSFUL.');
			$this->app->redirect($this->app->urlFor('account'));
		}
	}

}
