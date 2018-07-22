<?php namespace UserAdministrator;
/**
 * Class Controller
 * @package UserAdministrator
 *
 * The Controller has not really a purpose,
 * its job is to handle data that the user submits
 *
 */
class Controller
{
    /**
     * @var object Processor|null
     */
    private $processor = null;

    /**
     * @var object Input|null - User input
     */
    public $input = null;

    /**
     * @var object Sanitizer|null - IM Sanitizer class instance
     */
    private $sanitizer = null;

    /**
     * Controller constructor.
     *
     * @param $processor
     */
    public function __construct($processor) {
        $this->processor = $processor;
        $this->input = new Input();
        $this->sanitizer = $this->processor->imanager->sanitizer;
    }

    /**
     * @param $name
     */
    public function setSection($name) {
        $this->processor->prepareSection($name, $this->input->whitelist);
    }

    /**
     * Lets the unauthorized calls go to waste
     * instead of causing a fatal error.
     *
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        if(method_exists($this, $name)) {
            $reflection = new \ReflectionMethod($this, $name);
            if(!$reflection->isPublic()) return;
        }
    }

    /**
     * Login action
     *
     * Login form was sent
     */
    public function login()
    {
        $this->input->whitelist->username = $this->sanitizer->text(
            $this->input->post->username, array('maxLength' => 100)
        );
        $this->input->whitelist->password = $this->input->post->password;

        $this->processor->actionLogin($this->input->whitelist);
    }

    /**
     * Registration action
     *
     * Form is sent
     */
    public function registration()
    {
        $this->input->whitelist->username = $this->sanitizer->pageName(
            $this->input->post->username, array('maxLength' => 100)
        );
        $this->input->whitelist->email = $this->sanitizer->email(
            $this->input->post->email
        );
        $this->input->whitelist->password = $this->input->post->password;
        $this->input->whitelist->confirm = $this->input->post->confirm;

        $this->processor->actionRegistration($this->input->whitelist);
    }

    /**
     * Confirmation action
     *
     * Confirmation link clicked
     */
    public function confirmation()
    {
        $this->input->whitelist->key = rawurldecode($this->input->get->key);

        $this->input->whitelist->user = $this->sanitizer->pageName(
            rawurldecode($this->input->get->user), array('maxLength' => 100)
        );

        $this->processor->actionConfirmation($this->input->whitelist);
    }

}

class Input
{
    public $post;
    public $get;
    public $whitelist;

    public function __construct()
    {
        $this->post = new Post();
        $this->get = new Get();
        $this->whitelist = new Whitelist();
        foreach($_POST as $key => $value) { $this->post->{$key} = $value; }
        foreach($_GET as $key => $value) { $this->get->{$key} = $value; }
    }
}

class Post
{
    /**
     *
     * @param string $key
     * @param mixed $value
     * return $this
     *
     */
    public function __set($key, $value) { $this->{$key} = $value;}
    public function __get($name) { return isset($this->{$name}) ? $this->{$name} : null;}
}

class Get
{
    /**
     *
     * @param string $key
     * @param mixed $value
     * return $this
     *
     */
    public function __set($key, $value) { $this->{$key} = $value; }
    public function __get($name) { return isset($this->{$name}) ? $this->{$name} : null; }
}

class Whitelist
{
    /**
     *
     * @param string $key
     * @param mixed $value
     * return $this
     *
     */
    public function __set($key, $value) { $this->{$key} = $value; }
    public function __get($name) { return isset($this->{$name}) ? $this->{$name} : null; }
}