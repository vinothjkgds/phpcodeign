<?php
/**
 * Product List View
 */
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Product List</h4>
            <div>
                <a href="<?= base_url('product/stock-history') ?>" class="btn btn-info me-2">Stock History</a>
                <a href="<?= base_url('product/add') ?>" class="btn btn-primary">Add New Product</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="productTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
