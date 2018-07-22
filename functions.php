<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

include 'useradmin/Processor.php';
include 'useradmin/Controller.php';
include 'useradmin/View.php';

$processor = new \UserAdministrator\Processor();
$controller = new \UserAdministrator\Controller($processor);
$view = new \UserAdministrator\View($processor);

$controller->setSection(return_page_slug());

if($controller->input->post->action || $controller->input->get->action) {
    ($controller->input->post->action) ? $controller->{$controller->input->post->action}() :
        $controller->{$controller->input->get->action}();
}