<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Stock Unit List</h4>
            <a href="<?= base_url('stockunit/add') ?>" class="btn btn-primary">Add New Stock Unit</a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="stockUnitTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Unit Name</th>
                                <th>Code</th>
                                <th>Symbol</th>
                                <th>Type</th>
                                <th>Factor to Base</th>
                                <th>Base</th>
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
