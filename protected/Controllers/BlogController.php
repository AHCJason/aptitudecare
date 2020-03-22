<?php

class BlogController extends MainPageController {

	public $page = 'blog';
	public $template = 'website';
	// access to pages is blocked by default
	// if access is needed it will have to be allowed inside the method
	public $allow_access = false;


	public function index() {
		$this->allow_access = true;
		$posts = $this->loadModel('BlogPost')->fetchRecentPosts();

		smarty()->assign('posts', $posts);
	}

	public function post() {
		$this->allow_access = true;
		if (isset (input()->id) && input()->id !== null) {
			$post = $this->loadModel('BlogPost', input()->id);
		} else {
			session()->setFlash('We are sorry. We could not find the blog post you are looking for', 'alert-danger');
			$this->redirect(SITE_URL);
		}

		smarty()->assign('post', $post);
	}


	public function manage() {
		$this->template = 'main';
		$posts = $this->loadModel('BlogPost')->fetchAll();

		smarty()->assign('blogPosts', $posts);
	}

	public function edit() {
		if (!auth()->getRecord()) {
			$this->redirect(SITE_URL);
		}

		$this->template = 'main';


		// if there is an id in the url then we are editing an existing post
		if (isset(input()->id) && input()->id !== null) {
			$post = $this->loadModel('BlogPost', input()->id);
		} else {
			$post = $this->loadModel('BlogPost');
		}

		// assign the object to smarty to use in the view page
		smarty()->assign('post', $post);

	}

	public function save() {
		if (isset (input()->id)) {
			$post = $this->loadModel('BlogPost', input()->id);
		} else {
			$post = $this->loadModel('BlogPost');
		}
		
		if (input()->title == null) {
			session()->setFlash('Please enter a post title', 'alert-danger');
			$this->redirect(input()->current_url);
		} else {
			$post->title = input()->title;
		}

		$post->content = input()->content;
		$post->user_id = auth()->getRecord()->id;

		if (input()->published == 1) {
			$post->date_published = mysql_date();
		} else {
			$post->date_published = null;
		}

		if ($post->save()) {
			session()->setFlash('The post was saved', 'alert-success');
			$this->redirect(SITE_URL . DS . 'blog/manage');
		} else {
			session()->setFlash('The post was not saved', 'alert-danger');
			$this->redirect(input()->current_url);
		}

	}

	protected function delete_post() {
		if (isset (input()->id) && input()->id !== null) {
			$post = $this->loadModel('BlogPost', input()->id);

			if ($post->delete()) {
				session()->setFlash('The blog post was deleted', 'alert-success');
				return true;
			} else {
				session()->setFlash('Could not delete the post', 'alert-danger');
				return false;
			}
		} else {
			session()->setFlash('Could not delete the post', 'alert-danger');
			return false;
		}


	}
}
