<?php
namespace App\Controllers;
use App\Models\Contact;
use System\BaseController;


class Contacts extends BaseController {

    public function index()
    {
        $contacts = new Contact();
        $records = $contacts->getContacts();

        return $this->view->render('contacts/index', compact('records'));
        
    }
}