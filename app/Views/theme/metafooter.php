<script src="<?= base_url() ?>assets/vendors/js/vendor.bundle.base.js"></script>
<script src="<?= base_url() ?>assets/js/off-canvas.js"></script>
<script src="<?= base_url() ?>assets/js/hoverable-collapse.js"></script>
<script src="<?= base_url() ?>assets/js/template.js"></script>
<script src="<?= base_url() ?>assets/js/settings.js"></script>
<script src="<?= base_url() ?>assets/js/todolist.js"></script>

<!-- File Upload JS-->
<!-- <script src="<?= base_url() ?>assets/js/file-upload.js"></script> -->

<!-- Datatable -->
<script src="<?= base_url() ?>assets/vendors/datatables.net/jquery.dataTables.js"></script>
<script src="<?= base_url() ?>assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
<!-- End Datatable -->

<!-- jQuery Validation JS -->
<script src="<?= base_url() ?>assets/vendors/jquery-validation/jquery.validate.min.js"></script>

<script>
if (typeof window.jQuery !== 'undefined' && window.jQuery.validator) {
    (function($){
        window.AppFormValidation = window.AppFormValidation || {
            initCustomMethods: function() {
                if (!$.validator.methods.indianPhone) {
                    $.validator.addMethod('indianPhone', function(value, element) {
                        if (this.optional(element)) {
                            return true;
                        }

                        var normalized = value.replace(/[()\s-]/g, '');
                        normalized = normalized.replace(/^\+91/, '');
                        normalized = normalized.replace(/^91(?=\d{10}$)/, '');

                        var isMobile = /^[6-9]\d{9}$/.test(normalized);
                        var isLandlineWithZero = /^0\d{10,11}$/.test(normalized);
                        var isLandlineWithoutZero = /^[1-5]\d{9,10}$/.test(normalized);

                        return isMobile || isLandlineWithZero || isLandlineWithoutZero;
                    }, 'Please enter a valid Indian mobile or landline number.');
                }

                if (!$.validator.methods.indianGstin) {
                    $.validator.addMethod('indianGstin', function(value, element) {
                        if (this.optional(element)) {
                            return true;
                        }
                        return /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i.test($.trim(value));
                    }, 'Please enter a valid GSTIN.');
                }

                if (!$.validator.methods.imageExtension) {
                    $.validator.addMethod('imageExtension', function(value, element) {
                        if (this.optional(element) || !value) {
                            return true;
                        }

                        return /\.(jpe?g|png|webp)$/i.test(value);
                    }, 'Only JPG, JPEG, PNG, or WEBP files are allowed.');
                }
            },
            defaultErrorPlacement: function(label, element) {
                label.addClass('mt-2 text-danger w-100');
                label.insertAfter(element);
            },
            defaultHighlight: function(element) {
                $(element).parent().addClass('has-danger');
                $(element).addClass('form-control-danger');
            },
            defaultUnhighlight: function(element) {
                $(element).parent().removeClass('has-danger');
                $(element).removeClass('form-control-danger');
            },
            bindAjaxSubmit: function(formSelector, options) {
                if (!$(formSelector).length) {
                    return;
                }

                $(formSelector).validate({
                    rules: options.rules || {},
                    messages: options.messages || {},
                    errorPlacement: this.defaultErrorPlacement,
                    highlight: this.defaultHighlight,
                    unhighlight: this.defaultUnhighlight,
                    submitHandler: function(form) {
                        var $btn = $(options.submitButtonSelector || '#submitBtn');
                        $btn.attr('disabled', true).val(options.loadingText || 'Submitting...');

                        $.ajax({
                            url: $(form).attr('action'),
                            type: 'POST',
                            data: new FormData(form),
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.status && response.redirect) {
                                    alert(response.message);
                                    window.location.href = response.redirect;
                                } else {
                                    alert(response.message || 'Something went wrong!');
                                    $btn.attr('disabled', false).val(options.submitText || 'Submit');
                                }
                            },
                            error: function(xhr) {
                                var resp = xhr.responseJSON;
                                alert(resp?.message || 'Error: ' + xhr.status);
                                $btn.attr('disabled', false).val(options.submitText || 'Submit');
                            }
                        });

                        return false;
                    }
                });
            }
        };
    })(window.jQuery);
}
</script>

<!-- Merchant Module JS -->
<?php if(trim(strtolower(current_controller())) == 'merchant' && (trim(strtolower(current_method())) == 'add' || trim(strtolower(current_method())) == 'edit')): ?> 
<script>
if (typeof window.jQuery !== 'undefined') {
    (function($){
        $(document).ready(function(){
            var isEdit = '<?= trim(strtolower(current_method())) ?>' === 'edit';
            var formSelector = isEdit ? '#editMerchant' : '#addMerchant';
            var submitText = isEdit ? 'Update' : 'Submit';
            var loadingText = isEdit ? 'Updating...' : 'Submitting...';

            if (window.AppFormValidation) {
                window.AppFormValidation.initCustomMethods();
                window.AppFormValidation.bindAjaxSubmit(formSelector, {
                    submitButtonSelector: '#submitBtn',
                    submitText: submitText,
                    loadingText: loadingText,
                    rules: {
                        merchant_name: {
                            required: true
                        },
                        merchant_type: {
                            required: true
                        },
                        phone: {
                            required: true,
                            indianPhone: true
                        },
                        email: {
                            email: true
                        },
                        shop_name: {
                            required: {
                                depends: function() {
                                    return $('#merchant_type').val() === 'shop';
                                }
                            }
                        },
                        shop_address: {
                            required: {
                                depends: function() {
                                    return $('#merchant_type').val() === 'shop';
                                }
                            }
                        },
                        gstin: {
                            indianGstin: true
                        },
                        shop_logo: {
                            imageExtension: true
                        },
                        profile_logo: {
                            imageExtension: true
                        }
                    },
                    messages: {
                        merchant_name: 'Please enter merchant name',
                        merchant_type: 'Please select merchant type',
                        phone: {
                            required: 'Please enter phone number',
                            indianPhone: 'Please enter a valid Indian mobile or landline number'
                        },
                        email: 'Please enter a valid email address',
                        shop_name: {
                            required: 'Please enter shop name'
                        },
                        shop_address: {
                            required: 'Please enter shop address'
                        },
                        gstin: {
                            indianGstin: 'Please enter a valid GSTIN'
                        },
                        shop_logo: {
                            imageExtension: 'Only JPG, JPEG, PNG, or WEBP files are allowed'
                        },
                        profile_logo: {
                            imageExtension: 'Only JPG, JPEG, PNG, or WEBP files are allowed'
                        }
                    }
                });
            }

            // Toggle merchant type fields
            $(document).on('change', '#merchant_type', function() {
                var type = $(this).val();
                if (type === 'individual') {
                    $('#personalAddressGroup').show();
                    $('#profileLogoGroup').show();
                    $('#shopNameGroup').hide();
                    $('#shopAddressGroup').hide();
                    $('#gstinGroup').hide();
                    $('#shopLogoGroup').hide();
                    $('#shop_name, #shop_address').prop('required', false);
                    $('#shop_logo').val('');
                } else if (type === 'shop') {
                    $('#personalAddressGroup').hide();
                    $('#profileLogoGroup').hide();
                    $('#shopNameGroup').show();
                    $('#shopAddressGroup').show();
                    $('#gstinGroup').show();
                    $('#shopLogoGroup').show();
                    $('#shop_name, #shop_address').prop('required', true);
                    $('#profile_logo').val('');
                } else {
                    $('#personalAddressGroup').hide();
                    $('#profileLogoGroup').hide();
                    $('#shopNameGroup').hide();
                    $('#shopAddressGroup').hide();
                    $('#gstinGroup').hide();
                    $('#shopLogoGroup').hide();
                    $('#shop_name, #shop_address').prop('required', false);
                    $('#shop_logo, #profile_logo').val('');
                }
            });

            $('#merchant_type').trigger('change');
        });
    })(window.jQuery);
} else {
    console.error('jQuery is not loaded. Check vendor.bundle.base.js path.');
}
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'merchant' && trim(strtolower(current_method())) == 'index'): ?> 
<script>
var merchantTable;
$(document).ready(function(){
    // Initialize DataTables
    merchantTable = $('#merchantTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('merchant/getMerchantListJson') ?>",
            type: "POST"
        },
        columns: [
            { data: "logo" },
            { data: "merchant_name" },
            { data: "merchant_type" },
            { data: "phone" },
            { data: "receivable_amount" },
            { data: "payable_amount" },
            { data: "is_active" },
            { data: "created_at" },
            { data: "action" }
        ],
        order: [[7,"desc"]],
        columnDefs: [
            { orderable: false, targets: [0, 8] } // Disable sorting on logo and action columns
        ]
    });

    // Handle Delete Merchant
    $(document).on('click','.deleteMerchant', function(){
        var merchantCode = $(this).data('id');
        if(confirm("Are you sure you want to delete this merchant?")){
            $.ajax({
                url: '<?= site_url("merchant/delete") ?>/'+merchantCode,
                type: 'POST',
                success: function(response){
                    alert(response.message);
                    merchantTable.ajax.reload(null,false);
                },
                error: function(){
                    alert('Error deleting merchant.');
                }
            });
        }
    });
});
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'product' && (trim(strtolower(current_method())) == 'add' || trim(strtolower(current_method())) == 'edit')): ?>
<script>
if (typeof window.jQuery !== 'undefined') {
    (function($){
        $(document).ready(function(){
            var isEdit = '<?= trim(strtolower(current_method())) ?>' === 'edit';
            var formSelector = isEdit ? '#editProduct' : '#addProduct';
            var submitText = isEdit ? 'Update' : 'Submit';
            var loadingText = isEdit ? 'Updating...' : 'Submitting...';

            if (window.AppFormValidation) {
                window.AppFormValidation.initCustomMethods();
                window.AppFormValidation.bindAjaxSubmit(formSelector, {
                    submitButtonSelector: '#submitBtn',
                    submitText: submitText,
                    loadingText: loadingText,
                    rules: {
                        product_name: {
                            required: true
                        },
                        current_stock: {
                            required: true,
                            number: true,
                            min: 0
                        },
                        stock_unit: {
                            required: true
                        },
                        reorder_level: {
                            required: true,
                            number: true,
                            min: 0
                        },
                        product_image: {
                            imageExtension: true
                        }
                    },
                    messages: {
                        product_name: 'Please enter product name',
                        current_stock: {
                            required: 'Please enter current stock',
                            number: 'Current stock must be a valid number',
                            min: 'Current stock cannot be negative'
                        },
                        stock_unit: 'Please select stock unit',
                        reorder_level: {
                            required: 'Please enter reorder level',
                            number: 'Reorder level must be a valid number',
                            min: 'Reorder level cannot be negative'
                        },
                        product_image: 'Only JPG, JPEG, PNG, or WEBP files are allowed'
                    }
                });
            }
        });
    })(window.jQuery);
} else {
    console.error('jQuery is not loaded. Check vendor.bundle.base.js path.');
}
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'product' && trim(strtolower(current_method())) == 'index'): ?>
<script>
var productTable;
$(document).ready(function(){
    productTable = $('#productTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('product/getProductListJson') ?>",
            type: 'POST'
        },
        columns: [
            { data: 'product_image' },
            { data: 'product_name' },
            { data: 'category' },
            { data: 'stock' },
            { data: 'is_active' },
            { data: 'created_at' },
            { data: 'action' }
        ],
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ]
    });

    $(document).on('click', '.deleteProduct', function(){
        var productCode = $(this).data('id');
        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: '<?= site_url("product/delete") ?>/' + productCode,
                type: 'POST',
                success: function(response){
                    alert(response.message);
                    productTable.ajax.reload(null, false);
                },
                error: function(){
                    alert('Error deleting product.');
                }
            });
        }
    });
});
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'product' && trim(strtolower(current_method())) == 'stockhistory'): ?>
<script>
var stockHistoryTable;
$(document).ready(function(){
    stockHistoryTable = $('#stockHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('product/getStockHistoryListJson') ?>",
            type: 'POST',
            data: function(d) {
                d.filter_product_id = $('#filter_product_id').val();
                d.filter_movement_type = $('#filter_movement_type').val();
                d.filter_from_date = $('#filter_from_date').val();
                d.filter_to_date = $('#filter_to_date').val();
            }
        },
        columns: [
            { data: 'history_id' },
            { data: 'created_at' },
            { data: 'product_name' },
            { data: 'movement_type' },
            { data: 'quantity' },
            { data: 'stock_before' },
            { data: 'stock_after' },
            { data: 'txn_ref' },
            { data: 'notes' }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { targets: [3], render: function(data) { return data; } }
        ]
    });

    $(document).on('click', '#applyStockHistoryFilter', function(){
        stockHistoryTable.ajax.reload();
    });

    $(document).on('click', '#resetStockHistoryFilter', function(){
        $('#filter_product_id').val('');
        $('#filter_movement_type').val('');
        $('#filter_from_date').val('');
        $('#filter_to_date').val('');
        stockHistoryTable.ajax.reload();
    });

    // Add Stock — load product info on product change
    $(document).on('change', '#sh_product_id', function() {
        var productId = $(this).val();
        var $hint = $('#sh_unit_hint');
        var $existing = $('#sh_existing_stock');
        var $unit = $('#sh_unit');

        if (!productId) {
            $existing.text('--');
            $hint.text('');
            return;
        }

        $.getJSON('<?= site_url('product/getProductInfo') ?>/' + productId, function(res) {
            if (res && res.status) {
                $existing.text(res.current_stock_formatted);
                $hint.text('Product stock unit: ' + res.stock_unit_label);
                $unit.val(res.stock_unit);
            }
        });
    });

    var stockHistoryNotesManuallyEdited = false;

    function buildStockHistoryNote() {
        var quantity = parseFloat($('#sh_quantity').val() || 0);
        var unitText = ($('#sh_unit option:selected').text() || '').trim();

        if (isNaN(quantity) || quantity === 0 || !unitText) {
            return '';
        }

        var actionText = quantity > 0 ? 'add' : 'reduce';
        var quantityText = Math.abs(quantity).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 });
        return 'Manual stock ' + actionText + ': ' + quantityText + ' ' + unitText;
    }

    function syncStockHistoryNote(forceUpdate) {
        var $notes = $('#sh_notes');
        if (!$notes.length) {
            return;
        }

        if (!forceUpdate && stockHistoryNotesManuallyEdited) {
            return;
        }

        $notes.val(buildStockHistoryNote());
    }

    $(document).on('input change', '#sh_quantity, #sh_unit', function() {
        syncStockHistoryNote(false);
    });

    $(document).on('input', '#sh_notes', function() {
        var currentValue = ($(this).val() || '').trim();
        if (currentValue === '') {
            stockHistoryNotesManuallyEdited = false;
            syncStockHistoryNote(true);
            return;
        }

        stockHistoryNotesManuallyEdited = true;
    });

    // Add Stock — form submit
    $(document).on('submit', '#stockHistoryAddStockForm', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $('#shAddStockMessage');
        var productId = $('#sh_product_id').val();

        if (!productId) {
            $msg.removeClass('alert-success').addClass('alert alert-danger').text('Please select a product.').show();
            return;
        }

        $btn.prop('disabled', true);
        $msg.hide().removeClass('alert alert-success alert-danger').html('');

        $.ajax({
            url: '<?= site_url('product/adjustStock') ?>/' + productId,
            type: 'POST',
            data: {
                adjustment_quantity: $('#sh_quantity').val(),
                adjustment_unit: $('#sh_unit').val(),
                adjustment_notes: $('#sh_notes').val()
            },
            dataType: 'json'
        }).done(function(res) {
            if (res && res.status) {
                $msg.addClass('alert alert-success').html(res.message || 'Stock added successfully.').show();
                $form[0].reset();
                $('#sh_existing_stock').text('--');
                $('#sh_unit_hint').text('');
                stockHistoryTable.ajax.reload();
                return;
            }
            $msg.addClass('alert alert-danger').html((res && res.message) ? res.message : 'Failed to add stock.').show();
        }).fail(function(xhr) {
            var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to add stock.';
            $msg.addClass('alert alert-danger').html(err).show();
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });

    syncStockHistoryNote(true);
});
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'product' && trim(strtolower(current_method())) == 'view'): ?>
<script src="<?= base_url() ?>assets/vendors/chart.js/chart.umd.js"></script>
<script>
$(document).ready(function(){
    if ($('#productViewStockHistoryTable').length) {
        $('#productViewStockHistoryTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [7] }
            ]
        });
    }

    if (typeof Chart !== 'undefined' && document.getElementById('productStockHistoryChart') && window.ProductStockViewChartData) {
        var chartData = window.ProductStockViewChartData;
        var ctx = document.getElementById('productStockHistoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Stock (' + (chartData.unit || '') + ')',
                    data: chartData.values || [],
                    borderColor: 'rgba(63,81,181,1)',
                    backgroundColor: 'rgba(63,81,181,0.65)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return Number(value).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' ' + (chartData.unit || '');
                            }
                        }
                    }
                }
            }
        });
    }

    var productViewNotesManuallyEdited = false;

    function buildProductViewAdjustmentNote() {
        var quantity = parseFloat($('#adjustment_quantity').val() || 0);
        var unitText = ($('#adjustment_unit option:selected').text() || '').trim();

        if (isNaN(quantity) || quantity === 0 || !unitText) {
            return '';
        }

        var actionText = quantity > 0 ? 'add' : 'reduce';
        var quantityText = Math.abs(quantity).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 });
        return 'Manual stock ' + actionText + ': ' + quantityText + ' ' + unitText;
    }

    function syncProductViewAdjustmentNote(forceUpdate) {
        var $notes = $('#adjustment_notes');
        if (!$notes.length) {
            return;
        }

        if (!forceUpdate && productViewNotesManuallyEdited) {
            return;
        }

        $notes.val(buildProductViewAdjustmentNote());
    }

    $(document).on('input change', '#adjustment_quantity, #adjustment_unit', function() {
        syncProductViewAdjustmentNote(false);
    });

    $(document).on('input', '#adjustment_notes', function() {
        var currentValue = ($(this).val() || '').trim();
        if (currentValue === '') {
            productViewNotesManuallyEdited = false;
            syncProductViewAdjustmentNote(true);
            return;
        }

        productViewNotesManuallyEdited = true;
    });

    syncProductViewAdjustmentNote(true);

    $(document).on('submit', '#adjustProductStockForm', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var $message = $('#adjustStockMessage');

        $submitBtn.prop('disabled', true);
        $message.hide().removeClass('alert alert-success alert-danger').html('');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function(response) {
            if (response && response.status) {
                $message.addClass('alert alert-success').html(response.message || 'Stock added successfully.').show();
                setTimeout(function() {
                    window.location.reload();
                }, 700);
                return;
            }

            $message.addClass('alert alert-danger').html((response && response.message) ? response.message : 'Failed to add stock.').show();
        }).fail(function(xhr) {
            var errorMessage = 'Failed to add stock.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $message.addClass('alert alert-danger').html(errorMessage).show();
        }).always(function() {
            $submitBtn.prop('disabled', false);
        });
    });
});
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'shop' && trim(strtolower(current_method())) == 'index'): ?>
<script>
if (typeof window.jQuery !== 'undefined') {
    (function($){
        $(document).ready(function(){
            if (window.AppFormValidation) {
                window.AppFormValidation.initCustomMethods();
                window.AppFormValidation.bindAjaxSubmit('#editShop', {
                    submitButtonSelector: '#submitBtn',
                    submitText: 'Update',
                    loadingText: 'Updating...',
                    rules: {
                        shop_name: {
                            required: true
                        },
                        email: {
                            email: true
                        },
                        gstin: {
                            indianGstin: true
                        },
                        logo: {
                            imageExtension: true
                        },
                        banner: {
                            imageExtension: true
                        }
                    },
                    messages: {
                        shop_name: 'Please enter shop name',
                        email: 'Please enter a valid email address',
                        gstin: {
                            indianGstin: 'Please enter a valid GSTIN'
                        },
                        logo: {
                            imageExtension: 'Only JPG, JPEG, PNG, or WEBP files are allowed'
                        },
                        banner: {
                            imageExtension: 'Only JPG, JPEG, PNG, or WEBP files are allowed'
                        }
                    }
                });
            }
        });
    })(window.jQuery);
}
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'salepurchase' && trim(strtolower(current_method())) == 'add'): ?>
<script>
if (typeof window.jQuery !== 'undefined') {
    (function($){
        var salePurchaseDescriptionManuallyEdited = false;

        function formatSalePurchaseAmount(amountValue) {
            var amount = parseFloat(amountValue || 0);
            if (isNaN(amount) || amount <= 0) {
                return '';
            }

            return '₹' + amount.toFixed(2);
        }

        function buildSalePurchaseDescription() {
            var type = $('#entry_type').val();
            var merchantName = ($('#merchant_id option:selected').text() || '').trim();
            var openingBalanceType = $('#opening_balance_type').val();
            var productName = ($('#product_id option:selected').text() || '').trim();
            var weight = ($('#weight').val() || '').trim();
            var weightUnit = ($('#weight_unit option:selected').text() || '').trim();
            var purity = ($('#purity').val() || '').trim();
            var amountText = formatSalePurchaseAmount($('#amount').val());

            if (merchantName === '' || merchantName === 'Select Merchant') {
                merchantName = 'merchant';
            }

            if (productName === '' || productName === 'Select Product') {
                productName = 'product';
            }

            var weightText = '';
            if (weight !== '') {
                weightText = weight + (weightUnit && weightUnit !== 'Select Unit' ? ' ' + weightUnit : '');
            }

            var purityText = purity !== '' ? ' (Purity ' + purity + ')' : '';
            var amountSuffix = amountText !== '' ? ' - ' + amountText : '';

            if (type === 'opening') {
                var openingLabel = openingBalanceType === 'payable' ? 'Payable opening balance' : 'Receivable opening balance';
                return openingLabel + ' for ' + merchantName + amountSuffix;
            }

            if (type === 'sale') {
                var saleBase = 'Sale';
                if (weightText !== '' && productName !== 'product') {
                    saleBase += ' ' + weightText + ' ' + productName + purityText;
                } else if (productName !== 'product') {
                    saleBase += ' ' + productName + purityText;
                }
                return saleBase + ' to ' + merchantName + amountSuffix;
            }

            if (type === 'purchase') {
                var purchaseBase = 'Purchase';
                if (weightText !== '' && productName !== 'product') {
                    purchaseBase += ' ' + weightText + ' ' + productName + purityText;
                } else if (productName !== 'product') {
                    purchaseBase += ' ' + productName + purityText;
                }
                return purchaseBase + ' from ' + merchantName + amountSuffix;
            }

            if (type === 'payment_received') {
                return 'Payment received from ' + merchantName + amountSuffix;
            }

            if (type === 'payment_paid') {
                return 'Payment paid to ' + merchantName + amountSuffix;
            }

            return '';
        }

        function updateSalePurchaseDescription(force) {
            if (salePurchaseDescriptionManuallyEdited && !force) {
                return;
            }

            var description = buildSalePurchaseDescription();
            var descriptionField = $('#description');
            descriptionField.data('isAutoWriting', '1');
            descriptionField.val(description);
            descriptionField.data('autoGenerated', '1');
            descriptionField.data('isAutoWriting', '0');
        }

        function toggleTradeFieldsByEntryType() {
            var type = $('#entry_type').val();
            var isTradeType = type === 'sale' || type === 'purchase';
            var isOpeningType = type === 'opening';

            $('#productGroup, #weightGroup, #weightUnitGroup, #purityGroup').toggle(isTradeType);
            $('#weightRequiredMark, #weightUnitRequiredMark').toggle(isTradeType);
            $('#openingBalanceTypeGroup').toggle(isOpeningType);
            $('#openingBalanceRequiredMark').toggle(isOpeningType);

            if (isOpeningType) {
                $('#opening_balance_type').prop('required', true);
            } else {
                $('#opening_balance_type').prop('required', false).val('');
            }

            if (isTradeType) {
                $('#weight').prop('required', true);
                $('#weight_unit').prop('required', true);
                return;
            }

            $('#weight').prop('required', false).val('');
            $('#weight_unit').prop('required', false).val('');
            $('#product_id').val('');
            $('#purity').val('');
            $('#productStockInfo').text('Available stock: -');
            $('#productStockWarning').addClass('d-none').text('Insufficient stock for this sale.');

            updateSalePurchaseDescription(false);
        }

        function getSelectedProductStockMeta() {
            var option = $('#product_id option:selected');
            if (!option.length || !option.val()) {
                return null;
            }

            return {
                stock: parseFloat(option.attr('data-stock') || '0') || 0,
                unit: (option.attr('data-stock-unit') || '').trim(),
                reorderLevel: parseFloat(option.attr('data-reorder-level') || '0') || 0
            };
        }

        function convertWeightUnit(value, fromUnit, toUnit) {
            var from = (fromUnit || '').toLowerCase().trim();
            var to = (toUnit || '').toLowerCase().trim();

            if (!from || !to) {
                return null;
            }

            if (from === to) {
                return value;
            }

            var massUnitToGram = {
                milligram: 0.001,
                gram: 1,
                kilogram: 1000,
                tola: 11.6638038,
                ounce: 28.349523125
            };

            if (typeof massUnitToGram[from] === 'undefined' || typeof massUnitToGram[to] === 'undefined') {
                return null;
            }

            var valueInGram = value * massUnitToGram[from];
            return valueInGram / massUnitToGram[to];
        }

        function shortUnitLabel(unit) {
            var normalized = (unit || '').toLowerCase().trim();
            var map = {
                kilogram: 'kg',
                gram: 'gm',
                milligram: 'mg',
                ounce: 'oz',
                piece: 'pc',
                liter: 'ltr',
                tola: 'tola'
            };

            return map[normalized] || unit;
        }

        function updateProductStockInfo() {
            var meta = getSelectedProductStockMeta();
            if (!meta) {
                $('#productStockInfo').text('Available stock: -');
                $('#productStockWarning').addClass('d-none').text('Insufficient stock for this sale.');
                return;
            }

            var stockText = 'Available stock: ' + meta.stock.toFixed(3) + ' ' + shortUnitLabel(meta.unit);
            if (meta.stock <= meta.reorderLevel) {
                stockText += ' (LOW)';
            }
            $('#productStockInfo').text(stockText);

            var selectedWeightUnit = ($('#weight_unit').val() || '').trim();
            if (selectedWeightUnit !== '') {
                var conversionCheck = convertWeightUnit(1, selectedWeightUnit, meta.unit);
                if (conversionCheck === null) {
                    $('#productStockWarning').removeClass('d-none').text('Cannot convert selected weight unit to product stock unit (' + shortUnitLabel(meta.unit) + ').');
                    return;
                }
            }

            $('#productStockWarning').addClass('d-none').text('Insufficient stock for this sale.');
        }

        function validateStockBeforeSubmit() {
            var type = $('#entry_type').val();
            if (type !== 'sale') {
                return true;
            }

            var meta = getSelectedProductStockMeta();
            if (!meta) {
                return true;
            }

            var enteredWeight = parseFloat($('#weight').val() || '0') || 0;
            var selectedWeightUnit = ($('#weight_unit').val() || '').trim();

            var convertedWeight = enteredWeight;
            if (selectedWeightUnit !== '') {
                convertedWeight = convertWeightUnit(enteredWeight, selectedWeightUnit, meta.unit);
            }

            if (convertedWeight === null) {
                $('#productStockWarning').removeClass('d-none').text('Cannot convert selected weight unit to product stock unit (' + shortUnitLabel(meta.unit) + ').');
                return false;
            }

            if (enteredWeight > 0 && convertedWeight > meta.stock) {
                $('#productStockWarning').removeClass('d-none').text('Insufficient stock. Available: ' + meta.stock.toFixed(3) + ' ' + shortUnitLabel(meta.unit) + '.');
                return false;
            }

            $('#productStockWarning').addClass('d-none').text('Insufficient stock for this sale.');
            return true;
        }

        $(document).ready(function(){
            if (window.AppFormValidation) {
                window.AppFormValidation.bindAjaxSubmit('#addSalePurchase', {
                    submitButtonSelector: '#submitBtn',
                    submitText: 'Submit',
                    loadingText: 'Submitting...',
                    rules: {
                        entry_date: { required: true },
                        entry_type: { required: true },
                        merchant_id: { required: true },
                        opening_balance_type: {
                            required: {
                                depends: function() {
                                    return $('#entry_type').val() === 'opening';
                                }
                            }
                        },
                        weight: {
                            required: {
                                depends: function() {
                                    var type = $('#entry_type').val();
                                    return type === 'sale' || type === 'purchase';
                                }
                            },
                            number: true,
                            min: 0.001
                        },
                        weight_unit: {
                            required: {
                                depends: function() {
                                    var type = $('#entry_type').val();
                                    return type === 'sale' || type === 'purchase';
                                }
                            }
                        },
                        amount: { required: true, number: true, min: 0.01 }
                    },
                    messages: {
                        entry_date: 'Please select entry date',
                        entry_type: 'Please select entry type',
                        merchant_id: 'Please select merchant',
                        opening_balance_type: 'Please select opening balance type',
                        weight: 'Please enter valid weight',
                        weight_unit: 'Please select weight unit',
                        amount: 'Please enter valid amount'
                    }
                });
            }

            $(document).on('change', '#entry_type', function(){
                toggleTradeFieldsByEntryType();
                updateProductStockInfo();
                updateSalePurchaseDescription(false);
            });

            $(document).on('change input', '#merchant_id, #opening_balance_type, #product_id, #weight, #weight_unit, #purity, #amount', function(){
                if ($(this).attr('id') === 'product_id' || $(this).attr('id') === 'weight' || $(this).attr('id') === 'weight_unit') {
                    updateProductStockInfo();
                    validateStockBeforeSubmit();
                }
                updateSalePurchaseDescription(false);
            });

            $(document).on('submit', '#addSalePurchase', function(e){
                if (!validateStockBeforeSubmit()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
                return true;
            });

            $(document).on('input', '#description', function(){
                var field = $(this);
                if (field.data('isAutoWriting') === '1') {
                    return;
                }

                if ($.trim(field.val()) === '') {
                    salePurchaseDescriptionManuallyEdited = false;
                    updateSalePurchaseDescription(true);
                    return;
                }

                salePurchaseDescriptionManuallyEdited = true;
                field.data('autoGenerated', '0');
            });

            toggleTradeFieldsByEntryType();
            updateProductStockInfo();
            updateSalePurchaseDescription(true);
        });
    })(window.jQuery);
}
</script>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'salepurchase' && trim(strtolower(current_method())) == 'index'): ?>
<script>
var salePurchaseTable;
$(document).ready(function(){
    function getSalePurchaseExportQuery() {
        var params = new URLSearchParams();
        var entryType = $('#filter_entry_type').val();
        var merchantId = $('#filter_merchant_id').val();
        var fromDate = $('#filter_from_date').val();
        var toDate = $('#filter_to_date').val();

        if (entryType) {
            params.set('filter_entry_type', entryType);
        }
        if (merchantId) {
            params.set('filter_merchant_id', merchantId);
        }
        if (fromDate) {
            params.set('filter_from_date', fromDate);
        }
        if (toDate) {
            params.set('filter_to_date', toDate);
        }

        return params.toString();
    }

    salePurchaseTable = $('#salePurchaseTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('salepurchase/getSalePurchaseListJson') ?>",
            type: 'POST',
            data: function(d) {
                d.filter_entry_type = $('#filter_entry_type').val();
                d.filter_merchant_id = $('#filter_merchant_id').val();
                d.filter_from_date = $('#filter_from_date').val();
                d.filter_to_date = $('#filter_to_date').val();
            }
        },
        columns: [
            { data: 's_no' },
            { data: 'entry_date' },
            { data: 'entry_type' },
            { data: 'merchant_name' },
            { data: 'product_name' },
            { data: 'weight' },
            { data: 'purity' },
            { data: 'amount' },
            { data: 'receivable_delta' },
            { data: 'current_receivable_balance' },
            { data: 'txn_ref' },
            { data: 'description' },
            { data: 'action' }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [11, 12] },
            { targets: [2], render: function(data) { return data; } }
        ]
    });

    $(document).on('click', '#applySalePurchaseFilter', function(){
        salePurchaseTable.ajax.reload();
    });

    $(document).on('click', '#resetSalePurchaseFilter', function(){
        $('#filter_entry_type').val('');
        $('#filter_merchant_id').val('');
        $('#filter_from_date').val('');
        $('#filter_to_date').val('');
        salePurchaseTable.ajax.reload();
    });

    $(document).on('click', '#exportSalePurchaseCsvFiltered', function(){
        var query = getSalePurchaseExportQuery();
        var url = "<?= site_url('salepurchase/export/csv') ?>";
        if (query) {
            url += '?' + query;
        }
        window.open(url, '_blank');
    });

    $(document).on('click', '#exportSalePurchaseExcelFiltered', function(){
        var query = getSalePurchaseExportQuery();
        var url = "<?= site_url('salepurchase/export/excel') ?>";
        if (query) {
            url += '?' + query;
        }
        window.open(url, '_blank');
    });

    $(document).on('click', '#exportSalePurchaseCsvAll', function(){
        window.open("<?= site_url('salepurchase/export/csv') ?>", '_blank');
    });

    $(document).on('click', '#exportSalePurchaseExcelAll', function(){
        window.open("<?= site_url('salepurchase/export/excel') ?>", '_blank');
    });

    $(document).on('click', '#importSalePurchaseCsv', function(){
        $('#importSalePurchaseCsvFile').val('');
        $('#importSalePurchaseCsvFile').trigger('click');
    });

    $(document).on('change', '#importSalePurchaseCsvFile', function(){
        if (!this.files || !this.files.length) {
            return;
        }
        $('#importSalePurchaseCsvForm').trigger('submit');
    });
});
</script>
<?php endif; ?>

<!-- Employee Module JS -->
<?php if(trim(strtolower(current_controller())) == 'employee' && (trim(strtolower(current_method())) == 'add' || trim(strtolower(current_method())) == 'edit')): ?>
<script>
if (typeof window.jQuery !== 'undefined') {
    (function($){
        $(document).ready(function(){
            var isEdit = '<?= trim(strtolower(current_method())) ?>' === 'edit';
            var formSelector = isEdit ? '#editEmployee' : '#addEmployee';
            var submitText = isEdit ? 'Update' : 'Submit';
            var loadingText = isEdit ? 'Updating...' : 'Submitting...';

            if (window.AppFormValidation) {
                window.AppFormValidation.initCustomMethods();
                window.AppFormValidation.bindAjaxSubmit(formSelector, {
                    submitButtonSelector: '#submitBtn',
                    submitText: submitText,
                    loadingText: loadingText,
                    rules: {
                        name: {
                            required: true
                        },
                        email: {
                            required: true,
                            email: true
                        },
                        mobileno: {
                            required: true,
                            indianPhone: true
                        },
                        user_type: {
                            required: true
                        },
                        profile_image: {
                            imageExtension: true
                        },
                        id_proof_front_image: {
                            imageExtension: true
                        },
                        id_proof_back_image: {
                            imageExtension: true
                        },
                        password: {
                            required: !isEdit,
                            minlength: 6
                        },
                        confirm_password: {
                            required: {
                                depends: function() {
                                    return !isEdit || $('#password').val().length > 0;
                                }
                            },
                            minlength: 6,
                            equalTo: '#password'
                        }
                    },
                    messages: {
                        name: 'Please enter employee name',
                        email: {
                            required: 'Please enter email address',
                            email: 'Please enter a valid email address'
                        },
                        mobileno: {
                            required: 'Please enter mobile number',
                            indianPhone: 'Please enter a valid Indian mobile or landline number'
                        },
                        user_type: 'Please select employee role',
                        profile_image: 'Only JPG, JPEG, PNG, or WEBP files are allowed',
                        id_proof_front_image: 'Only JPG, JPEG, PNG, or WEBP files are allowed',
                        id_proof_back_image: 'Only JPG, JPEG, PNG, or WEBP files are allowed',
                        password: {
                            required: 'Please enter password',
                            minlength: 'Password must be at least 6 characters'
                        },
                        confirm_password: {
                            required: 'Please confirm password',
                            minlength: 'Confirm password must be at least 6 characters',
                            equalTo: 'Confirm password must match password'
                        }
                    }
                });
            }
        });
    })(window.jQuery);
} else {
    console.error('jQuery is not loaded. Check vendor.bundle.base.js path.');
}
</script>
<?php endif; ?>

<?php if (trim(strtolower(current_controller())) == 'auth' && trim(strtolower(current_method())) == 'dashboard'): ?>
<script>
(function() {
    var controlsRoot = document.getElementById('dashboardRealtimeControls');
    if (!controlsRoot) {
        return;
    }

    var updatedEl = document.getElementById('dashboardLastUpdated');
    var autoRefreshEl = document.getElementById('dashboardAutoRefresh');
    var refreshNowEl = document.getElementById('dashboardRefreshNow');
    var autoRefreshTimer = null;

    var generatedAtRaw = controlsRoot.getAttribute('data-generated-at') || '';
    var generatedAt = generatedAtRaw ? new Date(generatedAtRaw.replace(' ', 'T')) : new Date();
    if (Number.isNaN(generatedAt.getTime())) {
        generatedAt = new Date();
    }

    function formatClock(dateObj) {
        return dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function updateLastUpdatedLabel() {
        if (!updatedEl) {
            return;
        }

        var nowMs = Date.now();
        var diffSeconds = Math.max(0, Math.floor((nowMs - generatedAt.getTime()) / 1000));
        var prefix = controlsRoot.getAttribute('data-last-updated-label') || 'Last updated:';
        updatedEl.textContent = prefix + ' ' + formatClock(generatedAt) + ' (' + diffSeconds + 's ago)';
    }

    function resetAutoRefreshTimer() {
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }

        if (!autoRefreshEl) {
            return;
        }

        var intervalSeconds = parseInt(autoRefreshEl.value, 10) || 0;
        if (intervalSeconds > 0) {
            autoRefreshTimer = setInterval(function() {
                window.location.reload();
            }, intervalSeconds * 1000);
        }
    }

    if (autoRefreshEl) {
        autoRefreshEl.addEventListener('change', resetAutoRefreshTimer);
    }

    if (refreshNowEl) {
        refreshNowEl.addEventListener('click', function() {
            window.location.reload();
        });
    }

    updateLastUpdatedLabel();
    setInterval(updateLastUpdatedLabel, 1000);
    resetAutoRefreshTimer();
}());
</script>

<?php if (!empty($monthlyTrendLabels) || !empty($categoryChartLabels)): ?>
<script src="<?= base_url() ?>assets/vendors/chart.js/chart.umd.js"></script>
<script>
(function(){
    if (typeof Chart === 'undefined') {
        return;
    }

    <?php if (!empty($monthlyTrendLabels)): ?>
    var monthlyTrendEl = document.getElementById('monthlyTrendChart');
    if (monthlyTrendEl) {
        var monthlyTrendCtx = monthlyTrendEl.getContext('2d');
        new Chart(monthlyTrendCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthlyTrendLabels) ?>,
                datasets: [
                    {
                        label: 'Sales (\u20B9)',
                        data: <?= json_encode($monthlyTrendSales) ?>,
                        backgroundColor: 'rgba(63,81,181,0.75)',
                        borderColor: 'rgba(63,81,181,1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Purchases (\u20B9)',
                        data: <?= json_encode($monthlyTrendPurchases) ?>,
                        backgroundColor: 'rgba(0,137,123,0.75)',
                        borderColor: 'rgba(0,137,123,1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v){
                                return '\u20B9' + v.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx){
                                return ctx.dataset.label + ': \u20B9' + parseFloat(ctx.parsed.y).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($categoryChartLabels)): ?>
    var categorySplitEl = document.getElementById('categorySplitChart');
    if (categorySplitEl) {
        var categorySplitCtx = categorySplitEl.getContext('2d');
        new Chart(categorySplitCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($categoryChartLabels) ?>,
                datasets: [
                    {
                        data: <?= json_encode($categoryChartData) ?>,
                        backgroundColor: ['#FFD700', '#C0C0C0', '#cd7f32', '#3f51b5', '#00897b', '#e53935', '#8e24aa']
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx){
                                var v = ctx.parsed;
                                return ctx.label + ': \u20B9' + parseFloat(v).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
}());
</script>
<?php endif; ?>
<?php endif; ?>

<?php if(trim(strtolower(current_controller())) == 'employee' && trim(strtolower(current_method())) == 'index'): ?>
<script>
var employeeTable;
$(document).ready(function(){
    employeeTable = $('#employeeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('employee/getEmployeeListJson') ?>",
            type: 'POST'
        },
        columns: [
            { data: 'profile_image' },
            { data: 'name' },
            { data: 'email' },
            { data: 'mobileno' },
            { data: 'user_type' },
            { data: 'is_active' },
            { data: 'created_at' },
            { data: 'action' }
        ],
        order: [[6, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 7] }
        ]
    });

    $(document).on('click', '.deleteEmployee', function(){
        var employeeCode = $(this).data('id');
        if (confirm('Are you sure you want to delete this employee?')) {
            $.ajax({
                url: '<?= site_url("employee/delete") ?>/' + employeeCode,
                type: 'POST',
                success: function(response){
                    alert(response.message);
                    employeeTable.ajax.reload(null, false);
                },
                error: function(xhr){
                    var resp = xhr.responseJSON;
                    alert(resp?.message || 'Error deleting employee.');
                }
            });
        }
    });
});
</script>
<?php endif; ?>
