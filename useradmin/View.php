<?php namespace UserAdministrator;

/**
 * Class View
 * @package UserAdministrator
 *
 * The View is the part of the user management script
 * where the HTML is generated and displayed.
 *
 */
class View
{
    /**
     * @var object Processor|null
     */
    private $processor = null;

    /**
     * @var object TemplateEngine|null
     */
    private $tplp = null;

    /**
     * View constructor.
     *
     * @param $processor
     */
    public function __construct($processor) {
        $this->processor = $processor;
        $this->tplp = $this->processor->imanager->getTemplateEngine();
    }

    /**
     * Content rendering function.
     * Depending on the section, a certain page area is rendered
     *
     */
    public function renderContent()
    {
        // Isn't a legale section
        if(!$this->processor->section) return;

        $method = 'gen'.ucfirst($this->processor->section);
        if(method_exists($this, $method)) {
            $section = $this->$method();
            return $this->tplp->render($section, $this->processor->data,
                true, array(), true
            );
        }
    }

    /**
     * This method looks for messages from the processor,
     * if there are any, they will be rendered and returned.
     *
     * @return string|void
     */
    public function renderMessages()
    {
        // No messages available
        if(!$this->processor->messages) return;

        $messages = '';
        foreach($this->processor->messages as $message) {
            $messages .= $this->genMessage($message['type'], $message['text']);
        }
        return $messages;
    }

    /**
     * Generates the markup for login form.
     *
     * @return string
     */
    private function genLogin()
    {
        ob_start(); ?>
        <form name="login" action="./" method="post">
            <fieldset class="uk-fieldset">
                <legend class="uk-legend">[[legend]]</legend>
                <div class="uk-margin">
                    <div class="uk-margin-small-bottom">
                        <div class="uk-inline">
                            <span class="uk-form-icon" uk-icon="icon: user"></span>
                            <input class="uk-input" type="text" name="username" value="[[value_username]]"
                                   placeholder="[[placaholder_username]]">
                        </div>
                    </div>
                    <div class="uk-margin-small-bottom">
                        <div class="uk-inline">
                            <span class="uk-form-icon" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="password" name="password" placeholder="[[placaholder_password]]">
                        </div>
                    </div>
                    <div class="uk-margin-bottom">
                        <input type="hidden" name="action" value="login">
                        <button class="uk-button uk-button-default">[[submit_text]]</button>
                    </div>
                </div>
                <p>[[register_text]] <span uk-icon="icon: link"></span>
                    <a href="../registration/">[[register_link]]</a></p>
            </fieldset>
        </form>
        <?php return ob_get_clean();
    }

    /**
     * Generates the markup for registration form.
     *
     * @return string
     */
    private function genRegistration()
    {
        ob_start(); ?>
        <form name="login" action="./" method="post">
            <fieldset class="uk-fieldset">
                <legend class="uk-legend">[[legend]]</legend>
                <div class="uk-margin">
                    <div class="uk-margin-small-bottom">
                        <div class="uk-inline">
                            <span class="uk-form-icon" uk-icon="icon: user"></span>
                            <input class="uk-input" type="text" name="username" value="[[value_username]]"
                                   placeholder="[[placaholder_username]]">
                        </div>
                    </div>
                    <div class="uk-margin-small-bottom">
                        <div class="uk-inline">
                            <span class="uk-form-icon" uk-icon="icon: mail"></span>
                            <input class="uk-input" type="email" name="email" value="[[value_email]]"
                                   placeholder="[[placaholder_email]]">
                        </div>
                    </div>
                    <div class="uk-margin-small-bottom">
                        <div class="uk-inline">
                            <span class="uk-form-icon" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="password" name="password" placeholder="[[placaholder_password]]">
                        </div>
                    </div>
                    <div class="uk-margin-small-bottom">
                        <div class="uk-inline">
                            <span class="uk-form-icon" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="password" name="confirm" placeholder="[[placaholder_confirm]]">
                        </div>
                    </div>
                    <p>[[terms_and_conditions_text]] <a href="../privacy-policy/">[[terms_and_conditions_link]]</a></p>
                    <div class="uk-margin-bottom">
                        <input type="hidden" name="action" value="registration">
                        <button class="uk-button uk-button-default">[[submit_text]]</button>
                    </div>
                </div>
                <p>[[login_text]] <span uk-icon="icon: link"></span> <a href="../login/">[[login_link]]</a></p>
            </fieldset>
        </form>
        <?php return ob_get_clean();
    }

    /**
     * Private user area
     *
     * @return string
     */
    private function genUser()
    {
        ob_start();

        if($this->processor->currentUser && $this->processor->currentUser->id) : ?>
            <h3>Hello <?php echo $this->processor->currentUser->name; ?>, welcome to your private area.</h3>
            <p>Your user role is <strong><?php echo $this->processor->currentUser->role; ?></strong></p>
            <p><a href="../logout/">Logout</a> <span uk-icon="icon: sign-out"></span></p>
        <?php else: ?>
            <h3>Hello user you are not logged in, restricted access.</h3>
            <p><span uk-icon="icon: sign-in"></span> <a href="../login/">Sign-in</a></p>
        <?php endif;

        return ob_get_clean();
    }

    /**
     * Generates a formatted message depending on the type
     *
     * @param $type
     * @param $text
     *
     * @return string
     */
    private function genMessage($type, $text)
    {
        if($type == 'danger') {
            return '<div uk-alert class="uk-alert-danger">'.$text.'<a class="uk-alert-close" uk-close></a></div>';
        } elseif($type == 'success') {
            return '<div uk-alert class="uk-alert-success">'.$text.'<a class="uk-alert-close" uk-close></a></div>';
        }
    }
}