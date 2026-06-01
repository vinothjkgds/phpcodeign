<?php

namespace App\Controllers;

use App\Models\AuthModel;

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

class Auth extends BaseController
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
                        'auth_profile_image' => $auth['profile_image'] ?? null,
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

    public function setLanguage(string $locale)
    {
        $allowedLocales = ['en', 'ta'];
        $selectedLocale = in_array($locale, $allowedLocales, true) ? $locale : 'en';

        session()->set('app_locale', $selectedLocale);
        service('request')->setLocale($selectedLocale);

        return redirect()->back();
    }

    /**
     * Displays the dashboard page.
     *
     * @return string
     */
    public function dashboard()
    {
        $shopId = (int) (session()->get('auth_shop_id') ?? 0);
        $totalOwnerPayableToMerchants        = 0.0;
        $totalOwnerReceivableFromMerchants   = 0.0;
        $totalActiveMerchants                = 0;
        $totalActiveEmployees                = 0;
        $todaysSalesAmount                   = 0.0;
        $todaysCollectionsAmount             = 0.0;
        $netPosition                         = 0.0;
        $monthlyTrendLabels                  = [];
        $monthlyTrendSales                   = [];
        $monthlyTrendPurchases               = [];
        $topMerchants                        = [];
        $recentTransactions                  = [];
        $categoryChartLabels                 = [];
        $categoryChartData                   = [];
        $stockHistoryChartLabels             = [];
        $stockHistoryChartData               = [];

        if ($shopId > 0) {
            $db = db_connect();

            // --- Active merchants & employees
            $totalActiveMerchants = (int) $db->table('merchants')
                ->where('shop_id', $shopId)->where('is_active', 1)->countAllResults();

            $totalActiveEmployees = (int) $db->table('users')
                ->where('shop_id', $shopId)->where('is_active', 1)
                ->whereIn('user_type', ['manager', 'staff'])->countAllResults();

            // --- Receivable / Payable totals
            $rows = $db->table('merchant_ledger')
                ->select('merchant_id, COALESCE(SUM(receivable_delta), 0) AS net_balance', false)
                ->where('shop_id', $shopId)
                ->groupBy('merchant_id')
                ->get()->getResult();

            foreach ($rows as $row) {
                $netBalance = (float) ($row->net_balance ?? 0);
                if ($netBalance < 0) {
                    $totalOwnerPayableToMerchants += abs($netBalance);
                }
                if ($netBalance > 0) {
                    $totalOwnerReceivableFromMerchants += $netBalance;
                }
            }
            $netPosition = $totalOwnerReceivableFromMerchants - $totalOwnerPayableToMerchants;

            $today = date('Y-m-d');

            // --- Today's sales
            $todaysSalesRow = $db->table('merchant_ledger')
                ->selectSum('amount')
                ->where('shop_id', $shopId)
                ->where('entry_type', 'sale')
                ->where('DATE(entry_date)', $today)
                ->get()->getRow();
            $todaysSalesAmount = (float) ($todaysSalesRow->amount ?? 0);

            // --- Today's collections
            $todaysCollRow = $db->table('merchant_ledger')
                ->selectSum('amount')
                ->where('shop_id', $shopId)
                ->where('entry_type', 'payment_received')
                ->where('DATE(entry_date)', $today)
                ->get()->getRow();
            $todaysCollectionsAmount = (float) ($todaysCollRow->amount ?? 0);

            // --- Monthly trend: last 6 months sales & purchases
            $monthlyRows = $db->query("
                SELECT
                    DATE_FORMAT(entry_date, '%b %Y') AS month_label,
                    DATE_FORMAT(entry_date, '%Y-%m')  AS month_key,
                    SUM(CASE WHEN entry_type = 'sale'     THEN amount ELSE 0 END) AS sales_total,
                    SUM(CASE WHEN entry_type = 'purchase' THEN amount ELSE 0 END) AS purchases_total
                FROM merchant_ledger
                WHERE shop_id = ? AND entry_type IN ('sale','purchase')
                  AND entry_date >= DATE_SUB(LAST_DAY(NOW()), INTERVAL 5 MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC
                LIMIT 6
            ", [$shopId])->getResult();

            foreach ($monthlyRows as $mr) {
                $monthlyTrendLabels[]    = $mr->month_label;
                $monthlyTrendSales[]     = (float) $mr->sales_total;
                $monthlyTrendPurchases[] = (float) $mr->purchases_total;
            }

            // --- Top 5 merchants by total business value
            $topMerchants = $db->query("
                SELECT m.merchant_name, m.merchant_type,
                    SUM(CASE WHEN l.entry_type = 'sale'     THEN l.amount ELSE 0 END) AS sales_total,
                    SUM(CASE WHEN l.entry_type = 'purchase' THEN l.amount ELSE 0 END) AS purchases_total,
                    SUM(l.amount) AS grand_total
                FROM merchant_ledger l
                JOIN merchants m ON m.merchant_id = l.merchant_id
                WHERE l.shop_id = ? AND l.entry_type IN ('sale','purchase')
                GROUP BY m.merchant_id, m.merchant_name, m.merchant_type
                ORDER BY grand_total DESC
                LIMIT 5
            ", [$shopId])->getResult();

            // --- Recent 8 transactions
            $recentTransactions = $db->query("
                SELECT l.ledger_id, l.entry_date, l.entry_type, l.txn_ref, l.amount,
                    l.receivable_delta, l.payable_delta, l.weight, l.weight_unit, l.purity,
                    m.merchant_name, p.product_name
                FROM merchant_ledger l
                JOIN merchants m ON m.merchant_id = l.merchant_id
                LEFT JOIN products p ON p.product_id = l.product_id
                WHERE l.shop_id = ?
                ORDER BY l.entry_date DESC, l.ledger_id DESC
                LIMIT 8
            ", [$shopId])->getResult();

            // --- Product category split (current month)
            $catRows = $db->query("
                SELECT
                    COALESCE(p.category, 'other') AS category,
                    SUM(l.amount) AS total_amount
                FROM merchant_ledger l
                LEFT JOIN products p ON p.product_id = l.product_id
                WHERE l.shop_id = ? AND l.entry_type = 'sale'
                  AND MONTH(l.entry_date) = MONTH(NOW())
                  AND YEAR(l.entry_date)  = YEAR(NOW())
                GROUP BY category
                ORDER BY total_amount DESC
            ", [$shopId])->getResult();

            foreach ($catRows as $cr) {
                $categoryChartLabels[] = ucfirst((string) $cr->category);
                $categoryChartData[]   = (float) $cr->total_amount;
            }

            // --- Stock history trend (last 7 days entries count)
            $stockHistoryRows = $db->query(" 
                SELECT
                    DATE(created_at) AS day_key,
                    COUNT(*) AS total_entries
                FROM product_stock_history
                WHERE shop_id = ?
                  AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY day_key
                ORDER BY day_key ASC
            ", [$shopId])->getResult();

            foreach ($stockHistoryRows as $sr) {
                $ts = strtotime((string) ($sr->day_key ?? ''));
                $stockHistoryChartLabels[] = $ts ? date('d M', $ts) : '-';
                $stockHistoryChartData[]   = (int) ($sr->total_entries ?? 0);
            }
        }

        $data = [
            'body_content'                        => 'dashboard',
            'totalNoOfBusiness'                   => 0,
            'totalOwnerPayableToMerchants'         => $totalOwnerPayableToMerchants,
            'totalOwnerReceivableFromMerchants'    => $totalOwnerReceivableFromMerchants,
            'totalActiveMerchants'                 => $totalActiveMerchants,
            'totalActiveEmployees'                 => $totalActiveEmployees,
            'todaysSalesAmount'                    => $todaysSalesAmount,
            'todaysCollectionsAmount'              => $todaysCollectionsAmount,
            'netPosition'                          => $netPosition,
            'monthlyTrendLabels'                   => $monthlyTrendLabels,
            'monthlyTrendSales'                    => $monthlyTrendSales,
            'monthlyTrendPurchases'                => $monthlyTrendPurchases,
            'topMerchants'                         => $topMerchants,
            'recentTransactions'                   => $recentTransactions,
            'categoryChartLabels'                  => $categoryChartLabels,
            'categoryChartData'                    => $categoryChartData,
            'stockHistoryChartLabels'              => $stockHistoryChartLabels,
            'stockHistoryChartData'                => $stockHistoryChartData,
        ];

        return view('index', $data);
    }
}
