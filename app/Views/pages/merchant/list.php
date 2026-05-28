<?php
/**
 * Merchant List View
 *
 * Displays all merchants in a DataTables-powered table.
 * Includes action buttons for editing and deleting merchants.
 *
 * @package Views\Merchant
 * @category View
 * @author Vinothkumar
 * @version 1.0
 * @since 2026-05-27
 */
?>

<div class="card">
    <div class="card-body">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Merchant List</h4>
            <!-- Add New Merchant Button -->
            <a href="<?= base_url('merchant/add') ?>" class="btn btn-primary">Add New Merchant</a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <!-- Merchant Table -->
                    <table id="merchantTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Merchant Name</th>
                                <th>Type</th>
                                <th>Logo</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Receivable</th>
                                <th>Payable</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate rows via AJAX -->
                        </tbody>
                    </table>
                    <!-- End Merchant Table -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Notes:
 * - DataTables is initialized via metafooter.php script for server-side processing.
 * - Action column includes Edit and Delete buttons:
 *   - Edit: Opens the merchant edit form.
 *   - Delete: Triggers AJAX call to delete the merchant.
 * - Server-side AJAX endpoint: 'merchant/getListJsonDT'
 */
?>
