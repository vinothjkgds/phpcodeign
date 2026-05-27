<?php
/**
 * Add Employee View
 */
?>

<form class="cmxform" id="addEmployee" method="POST" action="<?= site_url('employee/save') ?>" enctype="multipart/form-data">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Employee</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Employee Name <span class="text-danger">*</span></label>
                                <input id="name" class="form-control" name="name" type="text" required placeholder="Enter Employee Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input id="email" class="form-control" name="email" type="email" required placeholder="Enter Email Address">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mobileno">Mobile Number <span class="text-danger">*</span></label>
                                <input id="mobileno" class="form-control" name="mobileno" type="text" required placeholder="Enter Mobile Number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_type">Role <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="user_type" name="user_type" required>
                                    <option value="">Select Role</option>
                                    <option value="owner">Owner</option>
                                    <option value="manager">Manager</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="profile_image">Profile Image</label>
                                <input id="profile_image" class="form-control" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_type">ID Proof Type</label>
                                <select class="form-select rounded-0" id="id_proof_type" name="id_proof_type">
                                    <option value="">Select ID Proof Type</option>
                                    <option value="aadhaar">Aadhaar</option>
                                    <option value="pan">PAN</option>
                                    <option value="voter_id">Voter ID</option>
                                    <option value="driving_license">Driving License</option>
                                    <option value="passport">Passport</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_number">ID Proof Number</label>
                                <input id="id_proof_number" class="form-control" name="id_proof_number" type="text" placeholder="Enter ID Proof Number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_front_image">ID Proof Front Image</label>
                                <input id="id_proof_front_image" class="form-control" name="id_proof_front_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_proof_back_image">ID Proof Back Image</label>
                                <input id="id_proof_back_image" class="form-control" name="id_proof_back_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input id="password" class="form-control" name="password" type="password" required minlength="6" placeholder="Enter Password">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                <input id="confirm_password" class="form-control" name="confirm_password" type="password" required minlength="6" placeholder="Confirm Password">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0">Inactive</option>
                                    <option value="1" selected>Active</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Submit" id="submitBtn">
                        <a href="<?= base_url('employee') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>