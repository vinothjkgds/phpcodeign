<?php
/**
 * Add Sale/Purchase Entry View
 */
?>

<form class="cmxform" id="addSalePurchase" method="POST" action="<?= site_url('salepurchase/save') ?>">
<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Sale / Purchase Entry</h4>

                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="entry_date">Entry Date <span class="text-danger">*</span></label>
                                <input id="entry_date" class="form-control" name="entry_date" type="datetime-local" required value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="entry_type">Entry Type <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="entry_type" name="entry_type" required>
                                    <option value="">Select Type</option>
                                    <option value="opening">Opening Balance</option>
                                    <option value="sale">Sale</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="payment_received">Payment Received</option>
                                    <option value="payment_paid">Payment Paid</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" id="openingBalanceTypeGroup" style="display:none;">
                            <div class="form-group">
                                <label for="opening_balance_type">Opening Balance Type <span class="text-danger" id="openingBalanceRequiredMark">*</span></label>
                                <select class="form-select rounded-0" id="opening_balance_type" name="opening_balance_type">
                                    <option value="">Select Balance Type</option>
                                    <option value="receivable">Merchant Owes Shop (Receivable)</option>
                                    <option value="payable">Shop Owes Merchant (Payable)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="merchant_id">Merchant <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" id="merchant_id" name="merchant_id" required>
                                    <option value="">Select Merchant</option>
                                    <?php foreach (($merchants ?? []) as $merchant): ?>
                                        <option value="<?= (int) $merchant->merchant_id ?>"><?= esc($merchant->merchant_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" id="productGroup">
                            <div class="form-group">
                                <label for="product_id">Product</label>
                                <select class="form-select rounded-0" id="product_id" name="product_id">
                                    <option value="">Select Product</option>
                                    <?php foreach (($products ?? []) as $product): ?>
                                        <option value="<?= (int) $product->product_id ?>">
                                            <?= esc($product->product_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4" id="weightGroup">
                            <div class="form-group">
                                <label for="weight">Weight <span class="text-danger" id="weightRequiredMark">*</span></label>
                                <input id="weight" class="form-control" name="weight" type="number" min="0.001" step="0.001" placeholder="Enter weight">
                            </div>
                        </div>

                        <div class="col-md-4" id="weightUnitGroup">
                            <div class="form-group">
                                <label for="weight_unit">Weight Unit <span class="text-danger" id="weightUnitRequiredMark">*</span></label>
                                <select class="form-select rounded-0" id="weight_unit" name="weight_unit">
                                    <option value="">Select Unit</option>
                                    <option value="gram">Gram</option>
                                    <option value="kilogram">Kilogram</option>
                                    <option value="milligram">Milligram</option>
                                    <option value="tola">Tola</option>
                                    <option value="ounce">Ounce</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4" id="purityGroup">
                            <div class="form-group">
                                <label for="purity">Purity</label>
                                <input id="purity" class="form-control" name="purity" type="text" placeholder="e.g. 999, 916">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Amount (Rs) <span class="text-danger">*</span></label>
                                <input id="amount" class="form-control" name="amount" type="number" min="0.01" step="0.01" required placeholder="Enter amount">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txn_ref">Reference No</label>
                                <input id="txn_ref" class="form-control" name="txn_ref" type="text" placeholder="Optional reference number">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" class="form-control" name="description" rows="3" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input class="btn btn-primary" type="submit" value="Submit" id="submitBtn">
                        <a href="<?= base_url('salepurchase') ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
</form>
