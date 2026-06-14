<?php
/**
 * Edit Product View
 */
?>

<form class="cmxform" id="editProduct" method="POST" action="<?= site_url('product/save/' . $productInfo->product_id) ?>" enctype="multipart/form-data">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Product</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_id">Product ID</label>
                                <input id="product_id" class="form-control" type="text" value="<?= esc((string) $productInfo->product_id) ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name">Product Name <span class="text-danger">*</span></label>
                                <input id="product_name" class="form-control" name="product_name" type="text" required value="<?= esc($productInfo->product_name) ?>" placeholder="Enter Product Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-select rounded-0" id="category" name="category">
                                    <option value="">Select Category</option>
                                    <?php foreach (($categories ?? []) as $categoryValue => $categoryLabel): ?>
                                        <option value="<?= esc($categoryValue) ?>" <?= ($productInfo->category ?? '') === $categoryValue ? 'selected' : '' ?>><?= esc($categoryLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="current_stock">Current Stock <span class="text-danger">*</span></label>
                                <input id="current_stock" class="form-control" name="current_stock" type="number" step="0.001" min="0" required readonly value="<?= esc(number_format((float) ($productInfo->current_stock ?? 0), 3, '.', '')) ?>" placeholder="Enter Current Stock">
                                <small class="text-muted">Stock can be adjusted from Stock History / Adjust Stock.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock_unit">Stock Unit <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="stock_unit" name="stock_unit" required>
                                    <option value="">Select Stock Unit</option>
                                    <?php foreach (($stockUnits ?? []) as $unit): ?>
                                        <?php
                                            $code = strtolower(trim((string) ($unit['unit_code'] ?? '')));
                                            if ($code === '') {
                                                continue;
                                            }
                                            $symbol = trim((string) ($unit['unit_symbol'] ?? ''));
                                            $label = trim((string) ($unit['unit_name'] ?? $code));
                                            $display = $symbol !== '' ? $symbol . ' - ' . $label : $label;
                                            $selected = strtolower(trim((string) ($productInfo->stock_unit ?? ''))) === $code;
                                        ?>
                                        <option value="<?= esc($code) ?>" data-factor="<?= esc(number_format((float) ($unit['factor_to_base'] ?? 0), 8, '.', '')) ?>" data-unit-type="<?= esc((string) ($unit['unit_type'] ?? '')) ?>" <?= $selected ? 'selected' : '' ?>><?= esc($display) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reorder_level">Reorder Level <span class="text-danger">*</span></label>
                                <input id="reorder_level" class="form-control" name="reorder_level" type="number" step="0.001" min="0" required value="<?= esc(number_format((float) ($productInfo->reorder_level ?? 100), 3, '.', '')) ?>" placeholder="Enter Reorder Level">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_image">Product Image</label>
                                <input id="product_image" class="form-control" name="product_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
                                <?php if (!empty($productInfo->product_image)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url(ltrim($productInfo->product_image, '/')) ?>" target="_blank">View current product image</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0" <?= !$productInfo->is_active ? 'selected' : '' ?>>Inactive</option>
                                    <option value="1" <?= $productInfo->is_active ? 'selected' : '' ?>>Active</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="created_at">Created At</label>
                                <input id="created_at" class="form-control" type="text" value="<?= !empty($productInfo->created_at) ? esc(date('Y-m-d H:i:s', strtotime($productInfo->created_at))) : '-' ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Update" id="submitBtn">
                        <a href="<?= base_url('product') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
