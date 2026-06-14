<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Category List</h4>
            <a href="<?= base_url('category/add') ?>" class="btn btn-primary">Add New Category</a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="categoryTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Category Name</th>
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
