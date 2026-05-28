<?php

namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\Controller;

/**
 * Class Auth
 *
 * Handles user authentication actions such as login, logout,
 * and access to the dashboard. It interacts with the AuthModel
 * for database operations and manages session state.
 *
 * @package App\Controllers
 * @author  Vinothkumar J
 * @version 1.0
 */

class Auth extends Controller
{
    /**
     * Auth model instance.
     *
     * @var AuthModel
     */
    protected $authModel;

    /**
     * Constructor.
     *
     * Initializes the AuthModel and loads form helper.
     */
    public function __construct()
    {
        $this->authModel = new AuthModel();
        helper(['form']);
    }

    /**
     * Handles user login process.
     *
     * - Displays login page on GET requests.
     * - Validates credentials and returns JSON response on POST.
     * - Sets session data on successful login.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|string Redirect or view or JSON response
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        // Initialize response structure
        $data = [
            'status'  => false,
            'message' => '',
        ];
        // Handle POST (form submission)
        if (strtolower($this->request->getMethod()) === 'post') {
            // Validation rules
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]|max_length[255]',
            ];

            // Validate form inputs
            if (! $this->validate($rules)) {
                $data['validation'] = $this->validator;
                $data['error']      = 'Form Validation Error';
            } else {
                // Fetch user by email
                $auth = $this->authModel->getAuthByEmail($this->request->getVar('email'));

                // Verify password
                if ($auth && password_verify($this->request->getVar('password'), $auth['password_hash'])) {
                    // Prepare session data
                    $sessionData = [
                        'auth_reference' => $auth['reference_code'],
                        'auth_name'      => $auth['name'],
                        'auth_email'     => $auth['email'],
                        'auth_shop_id'   => $auth['shop_id'] ?? null,
                        'auth_usertype'  => $auth['user_type'],
                        'isLoggedIn'     => true,
                    ];
                    // Set session variables
                    session()->set($sessionData);
                    // Success response
                    $data = [
                        'status'   => true,
                        'message'  => 'Login Successful',
                        'redirect' => site_url('dashboard'),
                    ];
                } else {
                    // Invalid credentials
                    $data['error'] = 'Invalid email or password';
                }
            }

            // Return JSON response
            return $this->response->setStatusCode(200)->setJSON($data);
        }

        // If GET request, load login view
        return view('pages/login', $data);
    }

    /**
     * Handles user logout.
     *
     * Destroys the current session and redirects to login page.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    /**
     * Displays the dashboard page.
     *
     * @return string
     */
    public function dashboard()
    {
        $shopId = (int) (session()->get('auth_shop_id') ?? 0);
        $totalOwnerPayableToMerchants = 0.0;
        $totalOwnerReceivableFromMerchants = 0.0;
        $totalActiveMerchants = 0;
        $totalActiveEmployees = 0;

        if ($shopId > 0) {
            $db = db_connect();

            $merchantBuilder = $db->table('merchants');
            $merchantBuilder->where('shop_id', $shopId);
            $merchantBuilder->where('is_active', 1);
            $totalActiveMerchants = (int) $merchantBuilder->countAllResults();

            $employeeBuilder = $db->table('users');
            $employeeBuilder->where('shop_id', $shopId);
            $employeeBuilder->where('is_active', 1);
            $employeeBuilder->whereIn('user_type', ['manager', 'staff']);
            $totalActiveEmployees = (int) $employeeBuilder->countAllResults();

            $builder = $db->table('merchant_ledger');
            $builder->select('merchant_id, COALESCE(SUM(receivable_delta), 0) AS net_balance', false);
            $builder->where('shop_id', $shopId);
            $builder->groupBy('merchant_id');

            $rows = $builder->get()->getResult();
            foreach ($rows as $row) {
                $netBalance = (float) ($row->net_balance ?? 0);
                if ($netBalance < 0) {
                    $totalOwnerPayableToMerchants += abs($netBalance);
                }
                if ($netBalance > 0) {
                    $totalOwnerReceivableFromMerchants += $netBalance;
                }
            }
        }

        $data = [
            'body_content' => 'dashboard',
            'totalNoOfBusiness' => 0,
            'totalOwnerPayableToMerchants' => $totalOwnerPayableToMerchants,
            'totalOwnerReceivableFromMerchants' => $totalOwnerReceivableFromMerchants,
            'totalActiveMerchants' => $totalActiveMerchants,
            'totalActiveEmployees' => $totalActiveEmployees,
        ];

        return view('index', $data);
    }
}
