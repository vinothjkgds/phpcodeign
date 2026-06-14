<form class="cmxform" id="editStockUnit" method="POST" action="<?= site_url('stockunit/save/' . $unitInfo->reference_code) ?>">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Stock Unit</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_name">Unit Name <span class="text-danger">*</span></label>
                                <input id="unit_name" class="form-control" name="unit_name" type="text" required maxlength="100" value="<?= esc((string) $unitInfo->unit_name) ?>" placeholder="Ex: Gram">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_code">Unit Code <span class="text-danger">*</span></label>
                                <input id="unit_code" class="form-control" name="unit_code" type="text" required maxlength="50" value="<?= esc((string) $unitInfo->unit_code) ?>" placeholder="Ex: gram">
                                <small class="text-muted">Lowercase letters, numbers, and underscore only.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_symbol">Unit Symbol</label>
                                <input id="unit_symbol" class="form-control" name="unit_symbol" type="text" maxlength="20" value="<?= esc((string) ($unitInfo->unit_symbol ?? '')) ?>" placeholder="Ex: gm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_type">Unit Type <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="unit_type" name="unit_type" required>
                                    <option value="mass" <?= ($unitInfo->unit_type ?? 'mass') === 'mass' ? 'selected' : '' ?>>Mass</option>
                                    <option value="volume" <?= ($unitInfo->unit_type ?? '') === 'volume' ? 'selected' : '' ?>>Volume</option>
                                    <option value="count" <?= ($unitInfo->unit_type ?? '') === 'count' ? 'selected' : '' ?>>Count</option>
                                    <option value="other" <?= ($unitInfo->unit_type ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="factor_to_base">Conversion Factor to Base Unit <span class="text-danger">*</span></label>
                                <input id="factor_to_base" class="form-control" name="factor_to_base" type="text" inputmode="decimal" required value="<?= esc(number_format((float) ($unitInfo->factor_to_base ?? 1), 8, '.', '')) ?>" placeholder="Ex: 1.00000000">
                                <small class="text-muted">Example: If base is gram, kilogram factor is 1000.</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="is_base">Base Unit</label>
                                <select class="form-select rounded-0" id="is_base" name="is_base">
                                    <option value="0" <?= empty($unitInfo->is_base) ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= !empty($unitInfo->is_base) ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-select rounded-0" id="is_active" name="is_active">
                                    <option value="0" <?= empty($unitInfo->is_active) ? 'selected' : '' ?>>Inactive</option>
                                    <option value="1" <?= !empty($unitInfo->is_active) ? 'selected' : '' ?>>Active</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="created_at">Created At</label>
                                <input id="created_at" class="form-control" type="text" value="<?= !empty($unitInfo->created_at) ? esc(date('Y-m-d H:i:s', strtotime((string) $unitInfo->created_at))) : '-' ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Update" id="submitBtn">
                        <a href="<?= base_url('stockunit') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
