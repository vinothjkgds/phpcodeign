<?php
/**
 * Edit Merchant View
 *
 * Displays the form to edit an existing merchant account.
 * Supports both individual and shop-based merchants.
 *
 * @package Views\Merchant
 * @category View
 * @author Vinothkumar
 * @version 1.0
 * @since 2026-05-27
 */
?>

<!-- Merchant Edit Form -->
<form class="cmxform" id="editMerchant" method="POST" action="<?= site_url('merchant/save/' . $merchantInfo->reference_code) ?>" enctype="multipart/form-data">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Merchant</h4>
                
                <fieldset>
                    <div class="row">
                        <!-- Reference Code (Read-only) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference_code">Reference Code</label>
                                <input id="reference_code" class="form-control" type="text" value="<?= esc($merchantInfo->reference_code) ?>" readonly>
                            </div>
                        </div>

                        <!-- Merchant Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="merchant_type">Merchant Type <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="merchant_type" name="merchant_type" required>
                                    <option value="">Select Type</option>
                                    <option value="individual" <?= $merchantInfo->merchant_type === 'individual' ? 'selected' : '' ?>>Individual</option>
                                    <option value="shop" <?= $merchantInfo->merchant_type === 'shop' ? 'selected' : '' ?>>Shop</option>
                                </select>
                            </div>
                        </div>

                        <!-- Merchant Name -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="merchant_name">Merchant Name <span class="text-danger">*</span></label>
                                <input id="merchant_name" class="form-control" name="merchant_name" type="text" required 
                                       value="<?= esc($merchantInfo->merchant_name) ?>" placeholder="Enter Merchant Name">
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <input id="phone" class="form-control" name="phone" type="text" 
                                       value="<?= esc($merchantInfo->phone ?? '') ?>" required placeholder="Enter Phone Number">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" class="form-control" name="email" type="email" 
                                       value="<?= esc($merchantInfo->email ?? '') ?>" placeholder="Enter Email Address">
                            </div>
                        </div>

                        <!-- Profile Logo (for individual merchants) -->
                        <div class="col-md-6" id="profileLogoGroup" <?= $merchantInfo->merchant_type !== 'individual' ? 'style="display:none;"' : '' ?>>
                            <div class="form-group">
                                <label for="profile_logo">Profile Logo (Optional)</label>
                                <input id="profile_logo" class="form-control" name="profile_logo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <?php if (!empty($merchantInfo->profile_logo)): ?>
                                    <div class="mt-2">
                                        <img src="<?= base_url(ltrim($merchantInfo->profile_logo, '/')) ?>" alt="Profile Logo" style="max-height:50px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Shop Logo (for shop merchants) -->
                        <div class="col-md-6" id="shopLogoGroup" <?= $merchantInfo->merchant_type !== 'shop' ? 'style="display:none;"' : '' ?>>
                            <div class="form-group">
                                <label for="shop_logo">Shop Logo (Optional)</label>
                                <input id="shop_logo" class="form-control" name="shop_logo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <?php if (!empty($merchantInfo->shop_logo)): ?>
                                    <div class="mt-2">
                                        <img src="<?= base_url(ltrim($merchantInfo->shop_logo, '/')) ?>" alt="Shop Logo" style="max-height:50px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Personal Address (for individuals) -->
                        <div class="col-md-12" id="personalAddressGroup" <?= $merchantInfo->merchant_type !== 'individual' ? 'style="display:none;"' : '' ?>>
                            <div class="form-group">
                                <label for="personal_address">Personal Address</label>
                                <textarea id="personal_address" name="personal_address" class="form-control" rows="3" 
                                          placeholder="Enter Personal Address"><?= esc($merchantInfo->personal_address ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Shop Name (for shop merchants) -->
                        <div class="col-md-12" id="shopNameGroup" <?= $merchantInfo->merchant_type !== 'shop' ? 'style="display:none;"' : '' ?>>
                            <div class="form-group">
                                <label for="shop_name">Shop Name</label>
                                <input id="shop_name" class="form-control" name="shop_name" type="text" 
                                       value="<?= esc($merchantInfo->shop_name ?? '') ?>" placeholder="Enter Shop Name">
                            </div>
                        </div>

                        <!-- Shop Address (for shop merchants) -->
                        <div class="col-md-12" id="shopAddressGroup" <?= $merchantInfo->merchant_type !== 'shop' ? 'style="display:none;"' : '' ?>>
                            <div class="form-group">
                                <label for="shop_address">Shop Address</label>
                                <textarea id="shop_address" name="shop_address" class="form-control" rows="3" 
                                          placeholder="Enter Shop Address"><?= esc($merchantInfo->shop_address ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- GSTIN (for shop merchants) -->
                        <div class="col-md-6" id="gstinGroup" <?= $merchantInfo->merchant_type !== 'shop' ? 'style="display:none;"' : '' ?>>
                            <div class="form-group">
                                <label for="gstin">GSTIN</label>
                                <input id="gstin" class="form-control" name="gstin" type="text" 
                                       value="<?= esc($merchantInfo->gstin ?? '') ?>" placeholder="Enter GSTIN">
                            </div>
                        </div>

                        <!-- Commission Percentage -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commission_percent">Commission % <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input id="commission_percent" class="form-control" name="commission_percent" type="number" 
                                           step="0.01" min="0" max="100" value="<?= $merchantInfo->commission_percent ?>" required placeholder="Enter Commission Percentage">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0" <?= !$merchantInfo->is_active ? 'selected' : '' ?>>Inactive</option>
                                    <option value="1" <?= $merchantInfo->is_active ? 'selected' : '' ?>>Active</option>
                                </select>
                            </div>
                        </div>

                        <!-- Created At (Read-only) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="created_at">Created At</label>
                                <input id="created_at" class="form-control" type="text" 
                                       value="<?= date('Y-m-d H:i:s', strtotime($merchantInfo->created_at)) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Back buttons -->
                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Update" id="submitBtn">
                        <a href="<?= base_url('merchant') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
