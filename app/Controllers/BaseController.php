<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');

        $locale = (string) (session()->get('app_locale') ?? 'en');
        if (!in_array($locale, ['en', 'ta'], true)) {
            $locale = 'en';
        }
        service('request')->setLocale($locale);
    }

    /**
     * Resolve current authenticated user's shop id.
     * Ensures the session user exists, is active, and belongs to an active shop.
     */
    protected function resolveAuthenticatedShopId(): ?int
    {
        if (!session()->get('isLoggedIn')) {
            return null;
        }

        $referenceCode = (string) (session()->get('auth_reference') ?? '');
        if ($referenceCode === '') {
            return null;
        }

        $row = db_connect()->table('users u')
            ->select('u.user_id, u.reference_code, u.name, u.email, u.profile_image, u.user_type, u.shop_id')
            ->join('shops s', 's.shop_id = u.shop_id', 'inner')
            ->where('u.reference_code', $referenceCode)
            ->where('u.is_active', 1)
            ->where('s.is_active', 1)
            ->get()
            ->getRowArray();

        if (empty($row) || empty($row['shop_id'])) {
            session()->destroy();
            return null;
        }

        session()->set([
            'auth_id' => (int) $row['user_id'],
            'auth_reference' => (string) $row['reference_code'],
            'auth_name' => (string) ($row['name'] ?? ''),
            'auth_email' => (string) ($row['email'] ?? ''),
            'auth_profile_image' => $row['profile_image'] ?? null,
            'auth_shop_id' => (int) $row['shop_id'],
            'auth_usertype' => (string) ($row['user_type'] ?? 'staff'),
            'isLoggedIn' => true,
        ]);

        return (int) $row['shop_id'];
    }
}
