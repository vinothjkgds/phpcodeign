<?php

namespace App\Controllers;

use App\Models\ShopOnboardingModel;

class SaasOnboarding extends BaseController
{
    protected $onboardingModel;

    public function __construct()
    {
        $this->onboardingModel = new ShopOnboardingModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('index', [
            'body_content' => 'saas/onboarding_list',
            'rows' => $this->onboardingModel->getAllWithCreator(),
        ]);
    }

    public function add()
    {
        return view('index', [
            'body_content' => 'saas/onboarding_add',
        ]);
    }

    public function save()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'proposed_shop_name' => 'required|max_length[255]',
            'owner_name' => 'required|max_length[255]',
            'owner_email' => 'required|valid_email|max_length[255]',
            'owner_mobile' => 'required|exact_length[10]|regex_match[/^[6-9][0-9]{9}$/]',
            'city' => 'permit_empty|max_length[100]',
            'state_name' => 'permit_empty|max_length[100]',
            'country' => 'permit_empty|max_length[100]',
            'gstin' => 'permit_empty|max_length[30]',
            'onboarding_notes' => 'permit_empty|max_length[2000]',
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()->withInput()->with('error', implode(' | ', $validation->getErrors()));
        }

        $this->onboardingModel->insert([
            'proposed_shop_name' => trim((string) $this->request->getPost('proposed_shop_name')),
            'owner_name' => trim((string) $this->request->getPost('owner_name')),
            'owner_email' => strtolower(trim((string) $this->request->getPost('owner_email'))),
            'owner_mobile' => trim((string) ($this->request->getPost('owner_mobile') ?? '')) ?: null,
            'city' => trim((string) ($this->request->getPost('city') ?? '')) ?: null,
            'state_name' => trim((string) ($this->request->getPost('state_name') ?? '')) ?: null,
            'country' => trim((string) ($this->request->getPost('country') ?? '')) ?: null,
            'gstin' => trim((string) ($this->request->getPost('gstin') ?? '')) ?: null,
            'onboarding_notes' => trim((string) ($this->request->getPost('onboarding_notes') ?? '')) ?: null,
            'created_by_saas_user' => (int) (session()->get('saas_user_id') ?? 0) ?: null,
            'status' => 'pending',
        ]);

        return redirect()->to(site_url('saas/onboarding'))->with('success', 'Onboarding request created successfully.');
    }

    public function approve($referenceCode)
    {
        $item = $this->onboardingModel->getByReferenceCode((string) $referenceCode);
        if (!$item) {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Onboarding request not found.');
        }

        if (($item['status'] ?? '') !== 'pending') {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Only pending requests can be approved.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $email = strtolower(trim((string) ($item['owner_email'] ?? '')));
            if ($email === '') {
                throw new \RuntimeException('Owner email is required.');
            }

            $existingUser = $db->table('users')
                ->where('email', $email)
                ->countAllResults();
            if ($existingUser > 0) {
                throw new \RuntimeException('Owner email already exists in tenant users.');
            }

            $shopData = [
                'shop_name' => (string) ($item['proposed_shop_name'] ?? ''),
                'owner_name' => (string) ($item['owner_name'] ?? ''),
                'mobile_no' => (string) ($item['owner_mobile'] ?? ''),
                'email' => $email,
                'city' => (string) ($item['city'] ?? ''),
                'state_name' => (string) ($item['state_name'] ?? ''),
                'country' => (string) ($item['country'] ?? ''),
                'gstin' => (string) ($item['gstin'] ?? ''),
                'is_active' => 1,
            ];

            $db->table('shops')->insert($shopData);
            $shopId = (int) $db->insertID();
            if ($shopId <= 0) {
                throw new \RuntimeException('Unable to create shop.');
            }

            $temporaryPassword = 'Welcome@' . random_int(1000, 9999);
            $passwordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);

            $db->table('users')->insert([
                'shop_id' => $shopId,
                'name' => (string) ($item['owner_name'] ?? ''),
                'email' => $email,
                'password_hash' => $passwordHash,
                'mobileno' => (string) ($item['owner_mobile'] ?? ''),
                'user_type' => 'owner',
                'is_active' => 1,
            ]);

            $defaultCategories = ['Gold', 'Silver', 'Diamond', 'Platinum'];
            foreach ($defaultCategories as $categoryName) {
                $db->table('categories')->insert([
                    'shop_id' => $shopId,
                    'category_name' => $categoryName,
                    'is_active' => 1,
                ]);
            }

            $db->table('shop_onboarding')
                ->where('reference_code', (string) $referenceCode)
                ->update([
                    'status' => 'approved',
                    'created_shop_id' => $shopId,
                    'approved_by_saas_user' => (int) (session()->get('saas_user_id') ?? 0) ?: null,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Approval failed.');
            }

            $db->transCommit();

            return redirect()->to(site_url('saas/onboarding'))
                ->with('success', 'Shop onboarded successfully. Temporary owner password: ' . $temporaryPassword);
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to(site_url('saas/onboarding'))->with('error', $e->getMessage());
        }
    }

    public function reject($referenceCode)
    {
        $item = $this->onboardingModel->getByReferenceCode((string) $referenceCode);
        if (!$item) {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Onboarding request not found.');
        }

        if (($item['status'] ?? '') !== 'pending') {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Only pending requests can be rejected.');
        }

        $reason = trim((string) ($this->request->getPost('rejection_reason') ?? ''));

        $this->onboardingModel
            ->where('reference_code', (string) $referenceCode)
            ->set([
                'status' => 'rejected',
                'approved_by_saas_user' => (int) (session()->get('saas_user_id') ?? 0) ?: null,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason !== '' ? $reason : 'Rejected by onboarding admin.',
            ])
            ->update();

        return redirect()->to(site_url('saas/onboarding'))->with('success', 'Onboarding request rejected.');
    }

    public function resetOwnerPassword($referenceCode)
    {
        $item = $this->onboardingModel->getByReferenceCode((string) $referenceCode);
        if (!$item) {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Onboarding request not found.');
        }

        $shopId = (int) ($item['created_shop_id'] ?? 0);
        if ($shopId <= 0 || (string) ($item['status'] ?? '') !== 'approved') {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Password reset is available only for approved onboarded shops.');
        }

        $db = db_connect();
        $owner = $db->table('users')
            ->select('user_id, email')
            ->where('shop_id', $shopId)
            ->where('user_type', 'owner')
            ->orderBy('user_id', 'ASC')
            ->get()
            ->getRowArray();

        if (!$owner) {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Owner account not found for this shop.');
        }

        $temporaryPassword = 'Owner@' . random_int(1000, 9999);
        $passwordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);

        $db->table('users')
            ->where('user_id', (int) $owner['user_id'])
            ->update([
                'password_hash' => $passwordHash,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(site_url('saas/onboarding'))
            ->with('success', 'Owner password reset successfully. Temporary password: ' . $temporaryPassword);
    }

    public function toggleShopStatus($referenceCode)
    {
        $item = $this->onboardingModel->getByReferenceCode((string) $referenceCode);
        if (!$item) {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Onboarding request not found.');
        }

        $shopId = (int) ($item['created_shop_id'] ?? 0);
        if ($shopId <= 0 || (string) ($item['status'] ?? '') !== 'approved') {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Shop status can be changed only for approved onboarded shops.');
        }

        $db = db_connect();
        $shop = $db->table('shops')
            ->select('shop_id, shop_name, is_active')
            ->where('shop_id', $shopId)
            ->get()
            ->getRowArray();

        if (!$shop) {
            return redirect()->to(site_url('saas/onboarding'))->with('error', 'Shop not found.');
        }

        $nextStatus = ((int) ($shop['is_active'] ?? 0) === 1) ? 0 : 1;

        $db->table('shops')
            ->where('shop_id', $shopId)
            ->update([
                'is_active' => $nextStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $statusText = $nextStatus === 1 ? 'activated' : 'inactivated';

        return redirect()->to(site_url('saas/onboarding'))
            ->with('success', 'Shop ' . ((string) ($shop['shop_name'] ?? '')) . ' has been ' . $statusText . '.');
    }
}
