<div class="row">
    <div class="col-lg-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">New Shop Onboarding</h4>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('saas/onboarding/save') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Shop Name <span class="text-danger">*</span></label>
                                <input type="text" name="proposed_shop_name" class="form-control" value="<?= esc((string) old('proposed_shop_name')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Owner Name <span class="text-danger">*</span></label>
                                <input type="text" name="owner_name" class="form-control" value="<?= esc((string) old('owner_name')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Owner Email <span class="text-danger">*</span></label>
                                <input type="email" name="owner_email" class="form-control" value="<?= esc((string) old('owner_email')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Owner Mobile</label>
                                <input type="text" name="owner_mobile" class="form-control" value="<?= esc((string) old('owner_mobile')) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>City</label>
                                <input type="text" name="city" class="form-control" value="<?= esc((string) old('city')) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>State</label>
                                <input type="text" name="state_name" class="form-control" value="<?= esc((string) old('state_name')) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Country</label>
                                <input type="text" name="country" class="form-control" value="<?= esc((string) old('country')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>GSTIN</label>
                                <input type="text" name="gstin" class="form-control" value="<?= esc((string) old('gstin')) ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Notes</label>
                                <textarea name="onboarding_notes" rows="3" class="form-control"><?= esc((string) old('onboarding_notes')) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Onboarding</button>
                    <a href="<?= site_url('saas/onboarding') ?>" class="btn btn-light">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
