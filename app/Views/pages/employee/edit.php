<?php
/**
 * Edit Employee View
 */
?>

<form class="cmxform" id="editEmployee" method="POST" action="<?= site_url('employee/save/' . $employeeInfo->reference_code) ?>" enctype="multipart/form-data">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Employee</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference_code">Reference Code</label>
                                <input id="reference_code" class="form-control" type="text" value="<?= esc($employeeInfo->reference_code) ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_type">Role <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="user_type" name="user_type" required>
                                    <option value="">Select Role</option>
                                    <option value="owner" <?= $employeeInfo->user_type === 'owner' ? 'selected' : '' ?>>Owner</option>
                                    <option value="manager" <?= $employeeInfo->user_type === 'manager' ? 'selected' : '' ?>>Manager</option>
                                    <option value="staff" <?= $employeeInfo->user_type === 'staff' ? 'selected' : '' ?>>Staff</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Employee Name <span class="text-danger">*</span></label>
                                <input id="name" class="form-control" name="name" type="text" required value="<?= esc($employeeInfo->name) ?>" placeholder="Enter Employee Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input id="email" class="form-control" name="email" type="email" required value="<?= esc($employeeInfo->email) ?>" placeholder="Enter Email Address">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mobileno">Mobile Number <span class="text-danger">*</span></label>
                                <input id="mobileno" class="form-control" name="mobileno" type="text" required value="<?= esc($employeeInfo->mobileno ?? '') ?>" placeholder="Enter Mobile Number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="profile_image">Profile Image</label>
                                <input id="profile_image" class="form-control" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                                <?php if (!empty($employeeInfo->profile_image)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url(ltrim($employeeInfo->profile_image, '/')) ?>" target="_blank">View current profile image</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_type">ID Proof Type</label>
                                <select class="form-select rounded-0" id="id_proof_type" name="id_proof_type">
                                    <option value="">Select ID Proof Type</option>
                                    <option value="aadhaar" <?= ($employeeInfo->id_proof_type ?? '') === 'aadhaar' ? 'selected' : '' ?>>Aadhaar</option>
                                    <option value="pan" <?= ($employeeInfo->id_proof_type ?? '') === 'pan' ? 'selected' : '' ?>>PAN</option>
                                    <option value="voter_id" <?= ($employeeInfo->id_proof_type ?? '') === 'voter_id' ? 'selected' : '' ?>>Voter ID</option>
                                    <option value="driving_license" <?= ($employeeInfo->id_proof_type ?? '') === 'driving_license' ? 'selected' : '' ?>>Driving License</option>
                                    <option value="passport" <?= ($employeeInfo->id_proof_type ?? '') === 'passport' ? 'selected' : '' ?>>Passport</option>
                                    <option value="other" <?= ($employeeInfo->id_proof_type ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_number">ID Proof Number</label>
                                <input id="id_proof_number" class="form-control" name="id_proof_number" type="text" value="<?= esc($employeeInfo->id_proof_number ?? '') ?>" placeholder="Enter ID Proof Number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_front_image">ID Proof Front Image</label>
                                <input id="id_proof_front_image" class="form-control" name="id_proof_front_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                                <?php if (!empty($employeeInfo->id_proof_front_image)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url(ltrim($employeeInfo->id_proof_front_image, '/')) ?>" target="_blank">View current ID proof front image</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_back_image">ID Proof Back Image</label>
                                <input id="id_proof_back_image" class="form-control" name="id_proof_back_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                                <?php if (!empty($employeeInfo->id_proof_back_image)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url(ltrim($employeeInfo->id_proof_back_image, '/')) ?>" target="_blank">View current ID proof back image</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0" <?= !$employeeInfo->is_active ? 'selected' : '' ?>>Inactive</option>
                                    <option value="1" <?= $employeeInfo->is_active ? 'selected' : '' ?>>Active</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input id="password" class="form-control" name="password" type="password" minlength="6" placeholder="Leave blank to keep current password">
                                <small class="text-muted">Leave blank to keep the existing password.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input id="confirm_password" class="form-control" name="confirm_password" type="password" minlength="6" placeholder="Confirm New Password">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_login_at">Last Login</label>
                                <input id="last_login_at" class="form-control" type="text" value="<?= !empty($employeeInfo->last_login_at) ? esc(date('Y-m-d H:i:s', strtotime($employeeInfo->last_login_at))) : '-' ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="created_at">Created At</label>
                                <input id="created_at" class="form-control" type="text" value="<?= !empty($employeeInfo->created_at) ? esc(date('Y-m-d H:i:s', strtotime($employeeInfo->created_at))) : '-' ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Update" id="submitBtn">
                        <a href="<?= base_url('employee') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>