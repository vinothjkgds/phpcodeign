<?php
/**
 * Shop Profile View
 */
$shop = $shopInfo ?? null;
?>

<form class="cmxform" id="editShop" method="POST" action="<?= site_url('shop/save') ?>" enctype="multipart/form-data">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Shop Profile</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shop_name">Shop Name <span class="text-danger">*</span></label>
                                <input id="shop_name" class="form-control" name="shop_name" type="text" required value="<?= esc((string) ($shop->shop_name ?? '')) ?>" placeholder="Enter Shop Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="owner_name">Owner Name</label>
                                <input id="owner_name" class="form-control" name="owner_name" type="text" value="<?= esc((string) ($shop->owner_name ?? '')) ?>" placeholder="Enter Owner Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mobile_no">Mobile Number</label>
                                <input id="mobile_no" class="form-control" name="mobile_no" type="text" value="<?= esc((string) ($shop->mobile_no ?? '')) ?>" placeholder="Enter Mobile Number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" class="form-control" name="email" type="email" value="<?= esc((string) ($shop->email ?? '')) ?>" placeholder="Enter Email">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street">Street</label>
                                <input id="street" class="form-control" name="street" type="text" value="<?= esc((string) ($shop->street ?? '')) ?>" placeholder="Enter Street">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input id="city" class="form-control" name="city" type="text" value="<?= esc((string) ($shop->city ?? '')) ?>" placeholder="Enter City">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="state_code">State Code</label>
                                <input id="state_code" class="form-control" name="state_code" type="text" value="<?= esc((string) ($shop->state_code ?? '')) ?>" placeholder="Enter State Code">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="state_name">State Name</label>
                                <input id="state_name" class="form-control" name="state_name" type="text" value="<?= esc((string) ($shop->state_name ?? '')) ?>" placeholder="Enter State Name">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input id="country" class="form-control" name="country" type="text" value="<?= esc((string) ($shop->country ?? '')) ?>" placeholder="Enter Country">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pincode">Pincode</label>
                                <input id="pincode" class="form-control" name="pincode" type="text" value="<?= esc((string) ($shop->pincode ?? '')) ?>" placeholder="Enter Pincode">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gstin">GSTIN</label>
                                <input id="gstin" class="form-control" name="gstin" type="text" value="<?= esc((string) ($shop->gstin ?? '')) ?>" placeholder="Enter GSTIN">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" class="form-control" name="address" rows="3" placeholder="Enter Full Address"><?= esc((string) ($shop->address ?? '')) ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="logo">Shop Logo</label>
                                <input id="logo" class="form-control" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                                <?php if (!empty($shop->logo)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url(ltrim((string) $shop->logo, '/')) ?>" target="_blank">View current logo</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner">Shop Banner</label>
                                <input id="banner" class="form-control" name="banner" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                                <?php if (!empty($shop->banner)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url(ltrim((string) $shop->banner, '/')) ?>" target="_blank">View current banner</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0" <?= empty($shop->is_active) ? 'selected' : '' ?>>Inactive</option>
                                    <option value="1" <?= !empty($shop->is_active) ? 'selected' : '' ?>>Active</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Update" id="submitBtn">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
