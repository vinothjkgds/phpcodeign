<form class="cmxform" id="editCategory" method="POST" action="<?= site_url('category/save/' . $categoryInfo->reference_code) ?>">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Category</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_name">Category Name <span class="text-danger">*</span></label>
                                <input id="category_name" class="form-control" name="category_name" type="text" required maxlength="100" value="<?= esc((string) $categoryInfo->category_name) ?>" placeholder="Enter Category Name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0" <?= !$categoryInfo->is_active ? 'selected' : '' ?>>Inactive</option>
                                    <option value="1" <?= $categoryInfo->is_active ? 'selected' : '' ?>>Active</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="created_at">Created At</label>
                                <input id="created_at" class="form-control" type="text" value="<?= !empty($categoryInfo->created_at) ? esc(date('Y-m-d H:i:s', strtotime((string) $categoryInfo->created_at))) : '-' ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Update" id="submitBtn">
                        <a href="<?= base_url('category') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
