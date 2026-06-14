<?php

namespace App\Controllers;

use App\Models\SaasUserModel;

class SaasAuth extends BaseController
{
    protected $saasUserModel;

    public function __construct()
    {
        $this->saasUserModel = new SaasUserModel();
        helper(['form']);
    }

    public function login()
    {
        if (session()->get('saas_is_logged_in')) {
            if ($this->request->isAJAX() || strtolower($this->request->getMethod()) === 'post') {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Already logged in',
                    'redirect' => site_url('saas/dashboard'),
                ]);
            }

            return redirect()->to('/saas/dashboard');
        }

        $data = [
            'status' => false,
            'message' => 'Login failed',
        ];

        if (strtolower($this->request->getMethod()) === 'post') {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required|min_length[6]|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
                $data['error'] = 'Form Validation Error';
                $data['message'] = 'Form Validation Error';
            } else {
                $auth = $this->saasUserModel->getAuthByEmail((string) $this->request->getVar('email'));

                if ($auth && password_verify((string) $this->request->getVar('password'), (string) $auth['password_hash'])) {
                    session()->regenerate();

                    session()->set([
                        'saas_is_logged_in' => true,
                        'saas_user_id' => (int) $auth['saas_user_id'],
                        'saas_reference' => (string) $auth['reference_code'],
                        'saas_role' => (string) $auth['role'],
                        'auth_name' => (string) $auth['name'],
                        'auth_email' => (string) $auth['email'],
                        'auth_usertype' => 'saas',
                    ]);

                    $this->saasUserModel->update((int) $auth['saas_user_id'], ['last_login_at' => date('Y-m-d H:i:s')]);

                    return $this->response->setJSON([
                        'status' => true,
                        'message' => 'Login Successful',
                        'redirect' => site_url('saas/dashboard'),
                    ]);
                }

                $data['error'] = 'Invalid email or password';
                $data['message'] = 'Invalid email or password';
            }

            return $this->response->setStatusCode(200)->setJSON($data);
        }

        return view('pages/saas_login', $data);
    }

    public function logout()
    {
        session()->remove([
            'saas_is_logged_in',
            'saas_user_id',
            'saas_reference',
            'saas_role',
            'auth_name',
            'auth_email',
            'auth_usertype',
        ]);

        return redirect()->to('/saas/login');
    }

    public function dashboard()
    {
        $db = db_connect();

        $totalShops = (int) $db->table('shops')->countAllResults();
        $activeShops = (int) $db->table('shops')->where('is_active', 1)->countAllResults();
        $pendingOnboarding = (int) $db->table('shop_onboarding')->where('status', 'pending')->countAllResults();

        return view('index', [
            'body_content' => 'saas/dashboard',
            'totalShops' => $totalShops,
            'activeShops' => $activeShops,
            'pendingOnboarding' => $pendingOnboarding,
        ]);
    }
}
