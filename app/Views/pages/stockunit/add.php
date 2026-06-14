<?php $isFirstUnitSetup = empty($hasBaseUnit); ?>
<form class="cmxform" id="addStockUnit" method="POST" action="<?= site_url('stockunit/save') ?>" data-force-base="<?= $isFirstUnitSetup ? '1' : '0' ?>">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Stock Unit</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_name">Unit Name <span class="text-danger">*</span></label>
                                <input id="unit_name" class="form-control" name="unit_name" type="text" required maxlength="100" placeholder="Ex: Gram">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_code">Unit Code <span class="text-danger">*</span></label>
                                <input id="unit_code" class="form-control" name="unit_code" type="text" required maxlength="50" placeholder="Ex: gram">
                                <small class="text-muted">Lowercase letters, numbers, and underscore only.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_symbol">Unit Symbol</label>
                                <input id="unit_symbol" class="form-control" name="unit_symbol" type="text" maxlength="20" placeholder="Ex: gm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_type">Unit Type <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="unit_type" name="unit_type" required>
                                    <option value="mass" selected>Mass</option>
                                    <option value="volume">Volume</option>
                                    <option value="count">Count</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="factor_to_base">Conversion Factor to Base Unit <span class="text-danger">*</span></label>
                                <?php if ($isFirstUnitSetup): ?>
                                    <input id="factor_to_base_display" class="form-control" type="text" value="1.00000000" readonly>
                                    <input id="factor_to_base" type="hidden" name="factor_to_base" value="1.00000000">
                                <?php else: ?>
                                    <input id="factor_to_base" class="form-control" name="factor_to_base" type="text" inputmode="decimal" required value="1.00000000" placeholder="Ex: 1.00000000">
                                <?php endif; ?>
                                <small class="text-muted">Example: If base is gram, kilogram factor is 1000.<?= $isFirstUnitSetup ? ' First unit is auto-set as base with factor 1.' : '' ?></small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="is_base">Base Unit</label>
                                <select class="form-select rounded-0" id="is_base" name="is_base" <?= $isFirstUnitSetup ? 'disabled' : '' ?>>
                                    <option value="0" <?= $isFirstUnitSetup ? '' : 'selected' ?>>No</option>
                                    <option value="1" <?= $isFirstUnitSetup ? 'selected' : '' ?>>Yes</option>
                                </select>
                                <?php if ($isFirstUnitSetup): ?>
                                    <input type="hidden" name="is_base" value="1">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-3">
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
                        <a href="<?= base_url('stockunit') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
