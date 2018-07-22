<?php namespace UserAdministrator;
/**
 * Class Processor
 * @package UserAdministrator
 *
 * Processors purpose is to process data into storage or
 * seek and prepare data to be passed along to the other
 * components.
 *
 */
class Processor
{
    /**
     * @var array - Prepared data for the View
     */
    public $data = array();

    /**
     * @var array - Messages to display in View
     */
    public $messages = array();

    /**
     * @var null - Current section
     */
    public $section = null;

    /**
     * @var object ItemManager|null
     */
    public $imanager = null;

    /**
     * @var object ItemMapper|null
     */
    private $itemMapper = null;

    /**
     * @var object Category|null
     */
    private $usersCategory = null;

    /**
     * @var object SimpleItem|null
     */
    public $currentUser = null;

    /**
     * This is a limit on the number of possible new user registrations.
     * We don't want to have our hard drive crammed with an unnecessarily
     * large number of accounts.
     *
     * @var int
     */
    protected $maxUsers = 1000;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->imanager = imanager();

        if(isset($_SESSION['messages'])) {
            $this->messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
        }
        if(isset($_SESSION['user'])) {
            $this->init();
            $this->currentUser = unserialize($_SESSION['user']);
        }
    }

    /**
     * Provides a lazy init method to build the ItemManager's
     * class instancess we intended to use locally.
     */
    private function init()
    {
        $this->usersCategory = $this->imanager->getCategoryMapper()->getCategory('name=Users');
        $this->itemMapper = $this->imanager->getItemMapper();
    }

    /**
     * Tries to execute a method to prepare the data for the View.
     *
     * @param $name
     * @param $whitelist
     */
    public function prepareSection($name, $whitelist)
    {
        $this->section = $this->imanager->sanitizer->pageName($name);

        $method = 'section'.ucfirst($this->section);
        if(method_exists($this, $method)) {
            $this->data = $this->$method($whitelist);
        }
    }

    /**
     * Prepares data for the 'Login' form
     */
    private function sectionLogin($whitelist)
    {
        // If the user is already logged in, redirect him to the user area
        if($this->currentUser && $this->currentUser->id) {
            \Util::redirect('../user/');
            exit;
        }
        return array(
            'legend' => 'Login',
            'value_username' => $whitelist->username,
            'register_text' => 'You are not registered yet,',
            'register_link' => 'create your account',
            'submit_text' => 'Login',
            'placaholder_username' => 'Username or email',
            'placaholder_password' => 'Password',
        );
    }

    /**
     * Prepares data for the 'Registration' form
     */
    private function sectionRegistration($whitelist)
    {
        // If the user is already logged in, redirect him to the user area
        if($this->currentUser && $this->currentUser->id) {
            \Util::redirect('../user/');
            exit;
        }
        return array(
            'legend' => 'Signing up',
            'value_username' => $whitelist->username,
            'placaholder_username' => 'Username',
            'value_email' => $whitelist->email,
            'placaholder_email' => 'Email',
            'login_text' => 'Already registered,',
            'login_link' => 'log in now',
            'submit_text' => 'Sign Up',
            'placaholder_password' => 'Password',
            'placaholder_confirm' => 'Confirm',
            'terms_and_conditions_text' => 'By clicking Sign Up, you agree to our Terms and that you have read our ',
            'terms_and_conditions_link' => 'Data Use Policy'
        );
    }

    /**
     * Here no output is performed, we just directly execute
     * the logout method and redirect the user to the login page.
     */
    private function sectionLogout() { $this->actionLogout(); }

    /**
     * Checks the user input for correctness and completeness
     * and then attempts the login process.
     *
     * @param $whitelist
     */
    public function actionLogin($whitelist)
    {
        if(!$whitelist->username || !$whitelist->password) {
            $this->messages[] = array('type' => 'danger',
                'text' => 'Please fill in all fields!'
            );
            $this->prepareSection('login', $whitelist);
            return;
        }

        // It looks like the data was transmitted correctly, so let's
        // initialize all the required instances.
        $this->init();

        $this->itemMapper->alloc($this->usersCategory->id);
        $item = $this->itemMapper->getSimpleItem('name='.
            $this->imanager->sanitizer->pageName($whitelist->username));
        if(!$item) {
            $item = $this->itemMapper->getSimpleItem('email='.
                $this->imanager->sanitizer->email($whitelist->username));
            if(!$item) {
                $this->messages[] = array('type' => 'danger',
                    'text' => 'The data you entered is not correct!'
                );
                $this->prepareSection('login', $whitelist);
                return;
            }
        }
        // The account is
        if(!$item->active) {
            $this->messages[] = array('type' => 'danger',
                'text' => 'Your account is not activated, all accounts need to be activated '.
                    'by an activation link that arrives via email to the address you provided.'
            );
            $this->prepareSection('login', $whitelist);
            return;
        }

        // Verifies that password matches
        if(password_verify($whitelist->password, $item->password)) {
            $this->currentUser = $item;

            if($this->userLogin()) {
                \Util::redirect('../user/');
                exit;
            }

            $this->messages[] = array('type' => 'danger',
                'text' => 'Error while logging in!'
            );
            return;
        }

        $this->messages[] = array('type' => 'danger',
            'text' => 'The data you entered is not correct!'
        );
        $this->prepareSection('login', $whitelist);
        return;
    }

    /**
     * User will be logged in. Sessions are created.
     *
     * @return bool
     */
    private function userLogin()
    {
        if(!$this->currentUser) return false;

        // Empty password and salt, because it is no longer needed
        unset($this->currentUser->salt);
        unset($this->currentUser->password);

        $_SESSION['user'] = serialize($this->currentUser);
        $_SESSION['messages'][] = array('type' => 'success',
            'text' => 'You have been successfully logged in.'
        );
        return true;
    }

    /**
     * Logs the user out of the system.
     *
     * @return void
     */
    public function actionLogout()
    {
        if(!$this->currentUser) return;

        unset($this->currentUser);
        if(isset($_SESSION['user'])) { unset($_SESSION['user']); }

        $_SESSION['messages'][] = array('type' => 'success',
            'text' => 'You successfully logged out.'
        );

        \Util::redirect('../login/');
        exit;
    }

    /**
     * This method attempts to register a new user in the system
     *
     * @param $whitelist
     */
    public function actionRegistration($whitelist)
    {
        // Don't allow empty fields
        if(!$whitelist->username || !$whitelist->email ||
            !$whitelist->password || !$whitelist->confirm) {
            $this->messages[] = array('type' => 'danger',
                'text' => 'Please fill in all fields!'
            );
            $this->prepareSection('registration', $whitelist);
            return;
        }

        // It looks like the data was transmitted correctly, so let's
        // initialize all the required instances.
        $this->init();

        $this->itemMapper->alloc($this->usersCategory->id);
        $item = $this->itemMapper->getSimpleItem('name='.$whitelist->username);
        // The username already exists
        if($item) {
            $this->messages[] = array('type' => 'danger',
                'text' => 'This username is already assigned!'
            );
            $this->prepareSection('registration', $whitelist);
            return;
        }

        $item = $this->itemMapper->getSimpleItem('email='.$whitelist->email);
        // The email already exists
        if($item) {
            $this->messages[] = array('type' => 'danger',
                'text' => 'This email address is already assigned!'
            );
            $this->prepareSection('registration', $whitelist);
            return;
        }

        // The maximum number of new users has been reached
        if(count($this->itemMapper->simpleItems) >= $this->maxUsers) {
            $this->messages[] = array('type' => 'danger',
                'text' => 'The maximum number of new users has been reached, currently no registration is possible!'
            );
            $this->prepareSection('registration', $whitelist);
            return;
        }

        // Create new user
        $user = new \Item($this->usersCategory->id);

        $user->name = $whitelist->username;
        $user->setFieldValue('email', $whitelist->email);
        // Salt is no longer necessary
        $inputPass = new \InputPassword($user->fields->password);
        $newSalt = $inputPass->randomString();

        $inputPass->salt = $newSalt;
        $inputPass->confirm = $whitelist->confirm;
        $result = $inputPass->prepareInput($whitelist->password);
        if(!$result || is_int($result)) {
            switch($result) {
                case 2:
                    $this->messages[] = array('type' => 'danger',
                        'text' => 'The password should consist of at least '.
                            $user->fields->password->minimum.' characters!'
                    );
                    $this->prepareSection('registration', $whitelist);
                    return;
                case 3:
                    $this->messages[] = array('type' => 'danger',
                        'text' => 'The password should consist of a maximum of '.
                            $user->fields->password->maximum.' characters!'
                    );
                    $this->prepareSection('registration', $whitelist);
                    return;
                case 7:
                    $this->messages[] = array('type' => 'danger',
                        'text' => 'The password is not equal to password confirm in comparison!'
                    );
                    $this->prepareSection('registration', $whitelist);
                    return;
                default:
                    $this->messages[] = array('type' => 'danger',
                        'text' => 'Error setting the password value!'
                    );
                    $this->prepareSection('registration', $whitelist);
                    return;
            }
        }

        $user->fields->password->salt = $result->salt;
        $user->fields->password->value = $result->value;

        // By default all new users are guests
        $user->setFieldValue('role', 'Guest');

        if($user->save() && $this->saveSimpleItem($user)) {

            $recipient = $this->itemMapper->simpleItems[$user->id];

            if(!$this->dropConfirmation($recipient)) {
                $this->messages[] = array('type' => 'danger',
                    'text' => 'Email could not be sent. Check your email configuration!'
                );
                $this->prepareSection('registration', $whitelist);
                return;
            }

            $this->messages[] = array('type' => 'success',
                'text' => 'Thank you for registering on our site. We will send you a '.
                    'confirmation email containing your activation link.'
            );
            return;
        }

        $this->messages[] = array('type' => 'danger',
            'text' => 'Error saving user data!'
        );
        $this->prepareSection('registration', $whitelist);
        return;
    }

    /**
     * The method saves SimpleItem object permanently.
     *
     * @return bool
     */
    protected function saveSimpleItem($curitem)
    {
        $this->itemMapper->simplify($curitem);
        return ($this->itemMapper->save() !== false) ? true : false;
    }

    /**
     * Validates the confirmation data submitted
     * and activates the user account.
     *
     * @param $whitelist
     */
    public function actionConfirmation($whitelist)
    {
        if(!$whitelist->user || !$whitelist->key) {
            $_SESSION['messages'][] = array('type' => 'danger',
                'text' => 'The data is not complete, please contact our support.'
            );
            \Util::redirect('../login/');
            exit;
        }

        $this->init();

        $this->itemMapper->alloc($this->usersCategory->id);
        $item = $this->itemMapper->getSimpleItem('name='.$whitelist->user);

        if(!$item) {
            $_SESSION['messages'][] = array('type' => 'danger',
                'text' => 'This account no longer exists.'
            );
            \Util::redirect('../login/');
            exit;
        }

        // Check the key sent
        if(strcmp($whitelist->key, md5($item->password.$item->salt)) !== 0) {
            $_SESSION['messages'][] = array('type' => 'danger',
                'text' => 'The passed key is invalid, please contact our support.'
            );
            \Util::redirect('../login/');
            exit;
        }

        $user = null;
        $this->itemMapper->limitedInit($item->categoryid, $item->id);
        if(isset($this->itemMapper->items[$item->id])) {
            $user = $this->itemMapper->items[$item->id];
        }

        if($user) {
            $user->active = 1;
            if($user->save() && $this->saveSimpleItem($user)) {
                $_SESSION['messages'][] = array('type' => 'success',
                    'text' => 'Thank you, your account has just been activated. You can log in now.'
                );
                \Util::redirect('../login/');
                exit;
            }
        }

        $_SESSION['messages'][] = array('type' => 'danger',
            'text' => 'Error saving user data!'
        );
        \Util::redirect('../login/');
        exit;
    }

    /**
     * Sends an e-mail confirmation.
     * We don't use extra library for sending our email,
     * we'll simple use mail() function for sending mail.
     *
     * @param $recipient
     *
     * @return bool
     */
    private function dropConfirmation($recipient)
    {
        // Build confirmation link
        $link = IM_SITE_URL.'confirmation/?action=confirmation&key='.
            rawurlencode(md5($recipient->password.$recipient->salt)).'&user='.$recipient->name;

        // Prepare message data and sent the confirmation
        $subject = 'Email Activation Message';
        $email = "Hi $recipient->name, \r\n\r\nthanks for registering at our website.\r\n".
            "To activate your account click the link below!\r\n\r\nActivation Link: $link";

        $from = 'mypage@email.com';
        $reply = 'mypage@email.com';

        // Send the confirmatin email
        $header = "From: $from\r\n" .
            "Reply-To: $reply\r\n" .
            "X-Mailer: PHP/".phpversion();

        if(!mail($recipient->email, $subject, $email, $header)){ return false; }
        return true;
    }
}
