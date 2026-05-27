<?php

namespace App\Controllers;

class Cms extends BaseController
{
    public function index(): string
    {
        echo password_hash("admin123", PASSWORD_DEFAULT); die;
        $data = [
            "body_content" => "dashboard"
        ];
        return view('index', $data);
    }
    
    public function login(): string
    {
        $data = [
            
        ];
        return view('pages/login', $data);
    }

    public function addTicket(): string
    {
        $data = [
            "body_content" => "tickets/add"
        ];
        return view('index', $data);
    }

    public function myTickets(): string
    {
        $data = [
            "body_content" => "tickets/list"
        ];
        return view('index', $data);
    }
}
