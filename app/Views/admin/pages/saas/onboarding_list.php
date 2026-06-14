<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Shop Onboarding</h4>
        <a href="<?= site_url('saas/onboarding/add') ?>" class="btn btn-primary">New Request</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Shop</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row): ?>
                        <tr>
                            <td><?= esc((string) ($row['reference_code'] ?? '')) ?></td>
                            <td>
                                <div><?= esc((string) ($row['proposed_shop_name'] ?? '')) ?></div>
                                <small class="text-muted"><?= esc((string) ($row['city'] ?? '')) ?> <?= esc((string) ($row['state_name'] ?? '')) ?></small>
                            </td>
                            <td><?= esc((string) ($row['owner_name'] ?? '')) ?></td>
                            <td><?= esc((string) ($row['owner_email'] ?? '')) ?></td>
                            <td>
                                <?php $status = (string) ($row['status'] ?? 'pending'); ?>
                                <?php if ($status === 'approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php elseif ($status === 'rejected'): ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc((string) ($row['created_at'] ?? '-')) ?></td>
                            <td>
                                <?php if ($status === 'pending'): ?>
                                    <form method="post" action="<?= site_url('saas/onboarding/approve/' . $row['reference_code']) ?>" style="display:inline-block;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve and create shop?');">Approve</button>
                                    </form>
                                    <form method="post" action="<?= site_url('saas/onboarding/reject/' . $row['reference_code']) ?>" style="display:inline-block;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="rejection_reason" value="Rejected by onboarding admin">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this onboarding request?');">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No onboarding records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
