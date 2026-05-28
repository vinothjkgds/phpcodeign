<?php
/**
 * Add Product View
 */
?>

<form class="cmxform" id="addProduct" method="POST" action="<?= site_url('product/save') ?>" enctype="multipart/form-data">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Product</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name">Product Name <span class="text-danger">*</span></label>
                                <input id="product_name" class="form-control" name="product_name" type="text" required placeholder="Enter Product Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-select rounded-0" id="category" name="category">
                                    <option value="">Select Category</option>
                                    <?php foreach (($categories ?? []) as $categoryValue => $categoryLabel): ?>
                                        <option value="<?= esc($categoryValue) ?>"><?= esc($categoryLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purity">Purity</label>
                                <input id="purity" class="form-control" name="purity" type="text" placeholder="Enter Purity (e.g. 999, 916)">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_image">Product Image</label>
                                <input id="product_image" class="form-control" name="product_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP</small>
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
                        <a href="<?= base_url('product') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
