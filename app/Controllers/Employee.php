<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class Employee extends BaseController
{
    protected $employeeModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        helper(['form', 'url']);
    }

    public function index(): string
    {
        return view('index', ['body_content' => 'employee/list']);
    }

    public function getEmployeeListJson()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON([
                'draw' => (int) ($this->request->getPost('draw') ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'message' => 'Unable to identify current shop.'
            ]);
        }

        $response = $this->employeeModel->getEmployeeListDT($this->request->getPost(), $shopId);
        return $this->response->setJSON($response);
    }

    public function add()
    {
        return view('index', ['body_content' => 'employee/add']);
    }

    public function edit($employeeCode)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $employeeInfo = $this->employeeModel->getEmployeeByRefCode($employeeCode, $shopId);
        if (!$employeeInfo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Employee not found']);
        }

        return view('index', [
            'body_content' => 'employee/edit',
            'employeeInfo' => $employeeInfo,
        ]);
    }

    public function delete($code = null)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)->setBody('Method Not Allowed');
        }

        if (!$code) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setBody('Employee code required');
        }

        if ($code === (string) session()->get('auth_reference')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status' => false,
                'message' => 'You cannot delete the currently logged in employee.',
            ]);
        }

        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $employee = $this->employeeModel->getEmployeeByRefCode($code, $shopId);
        if (!$employee) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON(['status' => false, 'message' => 'Employee not found']);
        }

        $success = $this->employeeModel->where('reference_code', $code)->where('shop_id', $shopId)->delete();

        if ($success) {
            $this->removeEmployeeFileIfExists($employee->profile_image ?? null);
            $this->removeEmployeeFileIfExists($employee->id_proof_front_image ?? null);
            $this->removeEmployeeFileIfExists($employee->id_proof_back_image ?? null);
        }

        return $this->response->setJSON([
            'status' => $success ? true : false,
            'message' => $success ? 'Employee deleted successfully' : 'Failed to delete employee',
            'id' => $code,
        ]);
    }

    public function save($employeeCode = '')
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondEmployeeSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $existingEmployee = null;
            if ($employeeCode !== '') {
                $existingEmployee = $this->employeeModel->getEmployeeByRefCode($employeeCode, $shopId);
                if (!$existingEmployee) {
                    return $this->respondEmployeeSave(false, 'Employee not found.', null, [], 404);
                }
            }

            $validation = \Config\Services::validation();
            $validation->setRules([
                'name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Employee name is required.',
                    ],
                ],
                'email' => [
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => 'Email is required.',
                        'valid_email' => 'Please provide a valid email address.',
                    ],
                ],
                'mobileno' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Mobile number is required.',
                    ],
                ],
                'id_proof_type' => [
                    'rules' => 'permit_empty|in_list[aadhaar,pan,voter_id,driving_license,passport,other]',
                    'errors' => [
                        'in_list' => 'Please choose a valid ID proof type.',
                    ],
                ],
                'user_type' => [
                    'rules' => 'required|in_list[owner,manager,staff]',
                    'errors' => [
                        'required' => 'Role is required.',
                        'in_list' => 'Role must be owner, manager, or staff.',
                    ],
                ],
            ]);

            $postData = $this->request->getPost();
            $errors = [];

            if (!$validation->run($postData)) {
                $errors = $validation->getErrors();
            }

            $email = strtolower(trim((string) $this->request->getPost('email')));
            $mobileNo = trim((string) $this->request->getPost('mobileno'));
            $password = (string) $this->request->getPost('password');
            $confirmPassword = (string) $this->request->getPost('confirm_password');

            if ($this->employeeModel->emailExists($email, $employeeCode !== '' ? $employeeCode : null)) {
                $errors['email'] = 'This email is already registered.';
            }

            if ($this->employeeModel->mobileExists($mobileNo, $employeeCode !== '' ? $employeeCode : null)) {
                $errors['mobileno'] = 'This mobile number is already in use.';
            }

            if ($employeeCode === '' && $password === '') {
                $errors['password'] = 'Password is required.';
            }

            if ($password !== '' && strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters.';
            }

            if (($employeeCode === '' || $password !== '') && $password !== $confirmPassword) {
                $errors['confirm_password'] = 'Confirm password must match password.';
            }

            $profileImageFile = $this->request->getFile('profile_image');
            $idProofFrontFile = $this->request->getFile('id_proof_front_image');
            $idProofBackFile = $this->request->getFile('id_proof_back_image');

            $uploadedFiles = [
                'profile_image' => $profileImageFile,
                'id_proof_front_image' => $idProofFrontFile,
                'id_proof_back_image' => $idProofBackFile,
            ];

            foreach ($uploadedFiles as $field => $file) {
                if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE && !$file->isValid()) {
                    $errors[$field] = 'Invalid file upload.';
                }
            }

            if (!empty($errors)) {
                return $this->respondEmployeeSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $data = [
                'shop_id' => $shopId,
                'name' => trim((string) $this->request->getPost('name')),
                'email' => $email,
                'mobileno' => $mobileNo,
                'id_proof_type' => $this->request->getPost('id_proof_type') ?: null,
                'id_proof_number' => trim((string) ($this->request->getPost('id_proof_number') ?? '')) ?: null,
                'user_type' => (string) $this->request->getPost('user_type'),
                'is_active' => $this->request->getPost('is_active') ? true : false,
            ];

            if ($password !== '') {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($employeeCode !== '') {
                $data['profile_image'] = $existingEmployee->profile_image ?? null;
                $data['id_proof_front_image'] = $existingEmployee->id_proof_front_image ?? null;
                $data['id_proof_back_image'] = $existingEmployee->id_proof_back_image ?? null;
            }

            if ($profileImageFile && $profileImageFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $newProfileImage = $this->storeEmployeeImage($profileImageFile, 'profile');
                if ($employeeCode !== '' && !empty($existingEmployee->profile_image)) {
                    $this->removeEmployeeFileIfExists($existingEmployee->profile_image);
                }
                $data['profile_image'] = $newProfileImage;
            }

            if ($idProofFrontFile && $idProofFrontFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $newIdProofFront = $this->storeEmployeeImage($idProofFrontFile, 'idproof');
                if ($employeeCode !== '' && !empty($existingEmployee->id_proof_front_image)) {
                    $this->removeEmployeeFileIfExists($existingEmployee->id_proof_front_image);
                }
                $data['id_proof_front_image'] = $newIdProofFront;
            }

            if ($idProofBackFile && $idProofBackFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $newIdProofBack = $this->storeEmployeeImage($idProofBackFile, 'idproof');
                if ($employeeCode !== '' && !empty($existingEmployee->id_proof_back_image)) {
                    $this->removeEmployeeFileIfExists($existingEmployee->id_proof_back_image);
                }
                $data['id_proof_back_image'] = $newIdProofBack;
            }

            if ($employeeCode !== '') {
                $this->employeeModel->updateEmployeeByCode($employeeCode, $shopId, $data);
                return $this->respondEmployeeSave(true, 'Employee updated successfully', site_url('employee'));
            }

            $this->employeeModel->addEmployee($data);
            return $this->respondEmployeeSave(true, 'Employee added successfully', site_url('employee'));
        } catch (\Throwable $e) {
            return $this->respondEmployeeSave(false, $e->getMessage(), null, [], 500);
        }
    }

    private function respondEmployeeSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
    {
        if ($this->request->isAJAX()) {
            $payload = [
                'status' => $status,
                'message' => $message,
            ];

            if (!empty($redirect)) {
                $payload['redirect'] = $redirect;
            }

            if (!empty($errors)) {
                $payload['errors'] = $errors;
            }

            return $this->response->setStatusCode($statusCode)->setJSON($payload);
        }

        if ($status) {
            return redirect()->to($redirect ?: site_url('employee'))->with('success', $message);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('errors', $errors);
    }

    private function getCurrentShopId(): ?int
    {
        $shopId = session()->get('auth_shop_id');
        if (!empty($shopId)) {
            return (int) $shopId;
        }

        $referenceCode = (string) session()->get('auth_reference');
        if ($referenceCode === '') {
            return null;
        }

        $currentEmployee = $this->employeeModel->getEmployeeByRefCode($referenceCode);
        if (!$currentEmployee || empty($currentEmployee->shop_id)) {
            return null;
        }

        session()->set('auth_shop_id', (int) $currentEmployee->shop_id);
        return (int) $currentEmployee->shop_id;
    }

    private function storeEmployeeImage(UploadedFile $file, string $directoryType): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower($file->getExtension() ?: $file->getClientExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('Image must be JPG, JPEG, PNG, or WEBP format.');
        }

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \RuntimeException('Only image files are allowed for upload.');
        }

        $relativeDir = 'uploads/business/employee/' . $directoryType;
        $targetDir = FCPATH . $relativeDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to create employee upload directory.');
        }

        $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($targetDir, $newName, true);

        return $relativeDir . '/' . $newName;
    }

    private function removeEmployeeFileIfExists(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $cleanPath = ltrim($relativePath, '/');
        $publicPath = FCPATH . $cleanPath;
        $legacyPath = ROOTPATH . $cleanPath;

        if (is_file($publicPath)) {
            @unlink($publicPath);
            return;
        }

        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }
    }
}